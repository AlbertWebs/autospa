import Database from 'better-sqlite3';
import fs from 'node:fs';
import path from 'node:path';
import { app } from 'electron';
import { v4 as uuidv4 } from 'uuid';

/** @type {import('better-sqlite3').Database | null} */
let db = null;

export function getDb() {
    if (!db) {
        throw new Error('Database is not initialized.');
    }

    return db;
}

export function initDatabase() {
    const dir = path.join(app.getPath('userData'), 'data');
    fs.mkdirSync(dir, { recursive: true });

    const dbPath = path.join(dir, 'autospa-desktop.sqlite');
    db = new Database(dbPath);
    db.pragma('journal_mode = WAL');
    db.pragma('foreign_keys = ON');

    db.exec(`
        CREATE TABLE IF NOT EXISTS meta (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS customers (
            uuid TEXT PRIMARY KEY,
            server_id INTEGER,
            full_name TEXT NOT NULL,
            phone TEXT,
            email TEXT,
            notes TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            synced_at TEXT
        );

        CREATE TABLE IF NOT EXISTS vehicles (
            uuid TEXT PRIMARY KEY,
            server_id INTEGER,
            customer_uuid TEXT NOT NULL,
            registration_number TEXT NOT NULL,
            make TEXT,
            model TEXT,
            color TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            synced_at TEXT,
            FOREIGN KEY (customer_uuid) REFERENCES customers(uuid)
        );

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY,
            uuid TEXT,
            name TEXT NOT NULL,
            price REAL NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            category_name TEXT
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY,
            uuid TEXT,
            name TEXT NOT NULL,
            price REAL NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS payment_methods (
            id INTEGER PRIMARY KEY,
            slug TEXT NOT NULL,
            name TEXT NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS employees (
            id INTEGER PRIMARY KEY,
            uuid TEXT,
            full_name TEXT NOT NULL,
            position TEXT
        );

        CREATE TABLE IF NOT EXISTS job_cards (
            uuid TEXT PRIMARY KEY,
            server_id INTEGER,
            customer_uuid TEXT NOT NULL,
            vehicle_uuid TEXT,
            status TEXT NOT NULL DEFAULT 'open',
            notes TEXT,
            assigned_to INTEGER,
            service_ids TEXT NOT NULL DEFAULT '[]',
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            synced_at TEXT
        );

        CREATE TABLE IF NOT EXISTS sales (
            uuid TEXT PRIMARY KEY,
            customer_uuid TEXT NOT NULL,
            vehicle_uuid TEXT,
            method TEXT NOT NULL,
            amount REAL NOT NULL,
            items_json TEXT NOT NULL,
            receipt_number TEXT,
            server_receipt_id INTEGER,
            created_at TEXT NOT NULL,
            synced_at TEXT
        );

        CREATE TABLE IF NOT EXISTS expenses (
            uuid TEXT PRIMARY KEY,
            category TEXT NOT NULL,
            description TEXT NOT NULL,
            amount REAL NOT NULL,
            spent_on TEXT NOT NULL,
            created_at TEXT NOT NULL,
            synced_at TEXT
        );

        CREATE TABLE IF NOT EXISTS outbox (
            id TEXT PRIMARY KEY,
            type TEXT NOT NULL,
            client_entity_uuid TEXT,
            payload TEXT NOT NULL,
            created_at TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS id_map (
            client_ref TEXT PRIMARY KEY,
            server_id INTEGER NOT NULL,
            entity_type TEXT,
            updated_at TEXT NOT NULL
        );
    `);

    return dbPath;
}

export function newUuid() {
    return uuidv4();
}

export function nowIso() {
    return new Date().toISOString();
}

export function getMeta(key, fallback = null) {
    const row = getDb().prepare('SELECT value FROM meta WHERE key = ?').get(key);

    return row ? row.value : fallback;
}

export function setMeta(key, value) {
    getDb().prepare(`
        INSERT INTO meta (key, value) VALUES (?, ?)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value
    `).run(key, String(value));
}

export function enqueueMutation(type, payload, clientEntityUuid = null) {
    const id = newUuid();
    const entityUuid = clientEntityUuid ?? payload.uuid ?? newUuid();

    getDb().prepare(`
        INSERT INTO outbox (id, type, client_entity_uuid, payload, created_at)
        VALUES (?, ?, ?, ?, ?)
    `).run(id, type, entityUuid, JSON.stringify(payload), nowIso());

    return id;
}

export function listPendingMutations() {
    return getDb().prepare('SELECT * FROM outbox ORDER BY created_at ASC').all().map((row) => ({
        id: row.id,
        type: row.type,
        client_entity_uuid: row.client_entity_uuid,
        payload: JSON.parse(row.payload),
        created_at: row.created_at,
    }));
}

export function removeMutations(ids) {
    if (!ids.length) {
        return;
    }

    const stmt = getDb().prepare('DELETE FROM outbox WHERE id = ?');
    const tx = getDb().transaction((list) => {
        for (const id of list) {
            stmt.run(id);
        }
    });
    tx(ids);
}

export function pendingCount() {
    return getDb().prepare('SELECT COUNT(*) AS c FROM outbox').get().c;
}

export function saveIdMap(clientRef, serverId, entityType = null) {
    getDb().prepare(`
        INSERT INTO id_map (client_ref, server_id, entity_type, updated_at)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(client_ref) DO UPDATE SET
            server_id = excluded.server_id,
            entity_type = excluded.entity_type,
            updated_at = excluded.updated_at
    `).run(clientRef, serverId, entityType, nowIso());
}

export function getServerId(clientRef) {
    const row = getDb().prepare('SELECT server_id FROM id_map WHERE client_ref = ?').get(clientRef);

    return row?.server_id ?? null;
}

export function applyBootstrap(data) {
    const database = getDb();
    const tx = database.transaction(() => {
        database.prepare('DELETE FROM services').run();
        database.prepare('DELETE FROM products').run();
        database.prepare('DELETE FROM payment_methods').run();
        database.prepare('DELETE FROM employees').run();

        const insertService = database.prepare(`
            INSERT INTO services (id, uuid, name, price, is_active, category_name)
            VALUES (?, ?, ?, ?, ?, ?)
        `);
        for (const service of data.services ?? []) {
            insertService.run(
                service.id,
                service.uuid ?? null,
                service.name,
                Number(service.price ?? 0),
                service.is_active ? 1 : 0,
                service.category?.name ?? null,
            );
        }

        const insertProduct = database.prepare(`
            INSERT INTO products (id, uuid, name, price, is_active)
            VALUES (?, ?, ?, ?, ?)
        `);
        for (const product of data.products ?? []) {
            insertProduct.run(
                product.id,
                product.uuid ?? null,
                product.name,
                Number(product.selling_price ?? product.price ?? 0),
                product.is_active !== false ? 1 : 0,
            );
        }

        const insertPm = database.prepare(`
            INSERT INTO payment_methods (id, slug, name, is_active)
            VALUES (?, ?, ?, ?)
        `);
        for (const method of data.payment_methods ?? []) {
            insertPm.run(method.id, method.slug, method.name, method.is_active ? 1 : 0);
        }

        const insertEmp = database.prepare(`
            INSERT INTO employees (id, uuid, full_name, position)
            VALUES (?, ?, ?, ?)
        `);
        for (const employee of data.employees ?? []) {
            insertEmp.run(employee.id, employee.uuid ?? null, employee.full_name, employee.position ?? null);
        }

        const upsertCustomer = database.prepare(`
            INSERT INTO customers (uuid, server_id, full_name, phone, email, notes, created_at, updated_at, synced_at)
            VALUES (@uuid, @server_id, @full_name, @phone, @email, @notes, @created_at, @updated_at, @synced_at)
            ON CONFLICT(uuid) DO UPDATE SET
                server_id = excluded.server_id,
                full_name = excluded.full_name,
                phone = excluded.phone,
                email = excluded.email,
                notes = excluded.notes,
                updated_at = excluded.updated_at,
                synced_at = excluded.synced_at
        `);

        const stamp = data.synced_at ?? nowIso();

        for (const customer of data.customers ?? []) {
            const uuid = customer.uuid || `server-customer-${customer.id}`;
            upsertCustomer.run({
                uuid,
                server_id: customer.id,
                full_name: customer.full_name,
                phone: customer.phone ?? null,
                email: customer.email ?? null,
                notes: customer.notes ?? null,
                created_at: customer.created_at ?? stamp,
                updated_at: stamp,
                synced_at: stamp,
            });
            saveIdMap(`client:${uuid}`, customer.id, 'customer');
        }

        const upsertVehicle = database.prepare(`
            INSERT INTO vehicles (uuid, server_id, customer_uuid, registration_number, make, model, color, created_at, updated_at, synced_at)
            VALUES (@uuid, @server_id, @customer_uuid, @registration_number, @make, @model, @color, @created_at, @updated_at, @synced_at)
            ON CONFLICT(uuid) DO UPDATE SET
                server_id = excluded.server_id,
                customer_uuid = excluded.customer_uuid,
                registration_number = excluded.registration_number,
                make = excluded.make,
                model = excluded.model,
                color = excluded.color,
                updated_at = excluded.updated_at,
                synced_at = excluded.synced_at
        `);

        const customerByServerId = new Map(
            (data.customers ?? []).map((c) => [c.id, c.uuid || `server-customer-${c.id}`]),
        );

        for (const vehicle of data.vehicles ?? []) {
            const uuid = vehicle.uuid || `server-vehicle-${vehicle.id}`;
            const customerUuid = customerByServerId.get(vehicle.customer_id)
                || (data.customers ?? []).find((c) => c.id === vehicle.customer_id)?.uuid
                || null;

            if (!customerUuid) {
                continue;
            }

            upsertVehicle.run({
                uuid,
                server_id: vehicle.id,
                customer_uuid: customerUuid,
                registration_number: vehicle.registration_number,
                make: vehicle.make ?? null,
                model: vehicle.model ?? null,
                color: vehicle.color ?? null,
                created_at: vehicle.created_at ?? stamp,
                updated_at: stamp,
                synced_at: stamp,
            });
            saveIdMap(`client:${uuid}`, vehicle.id, 'vehicle');
        }

        if (data.branch_id != null) {
            setMeta('branch_id', data.branch_id);
        }

        setMeta('bootstrap_synced_at', stamp);
    });

    tx();
}

export function createCustomerLocal(input) {
    const uuid = newUuid();
    const stamp = nowIso();

    getDb().prepare(`
        INSERT INTO customers (uuid, full_name, phone, email, notes, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(
        uuid,
        input.full_name.trim(),
        input.phone?.trim() || null,
        input.email?.trim() || null,
        input.notes?.trim() || null,
        stamp,
        stamp,
    );

    let vehicle = null;
    const registration = input.registration_number?.trim().toUpperCase() || null;

    if (registration) {
        const vehicleUuid = newUuid();
        getDb().prepare(`
            INSERT INTO vehicles (uuid, customer_uuid, registration_number, make, model, color, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `).run(vehicleUuid, uuid, registration, null, null, null, stamp, stamp);

        vehicle = { uuid: vehicleUuid, registration_number: registration };
    }

    // Remote SyncService creates the optional vehicle from registration_number on customer.create.
    enqueueMutation('customer.create', {
        uuid,
        full_name: input.full_name.trim(),
        phone: input.phone?.trim() || null,
        email: input.email?.trim() || null,
        notes: input.notes?.trim() || null,
        registration_number: registration,
    }, uuid);

    return { uuid, vehicle };
}

export function createVehicleLocal(input) {
    const uuid = newUuid();
    const stamp = nowIso();
    const registration = input.registration_number.trim().toUpperCase();

    getDb().prepare(`
        INSERT INTO vehicles (uuid, customer_uuid, registration_number, make, model, color, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
        uuid,
        input.customer_uuid,
        registration,
        input.make?.trim() || null,
        input.model?.trim() || null,
        input.color?.trim() || null,
        stamp,
        stamp,
    );

    enqueueMutation('vehicle.create', {
        uuid,
        customer_id: `client:${input.customer_uuid}`,
        registration_number: registration,
        make: input.make?.trim() || null,
        model: input.model?.trim() || null,
        color: input.color?.trim() || null,
    }, uuid);

    return { uuid };
}

export function createJobCardLocal(input) {
    const uuid = newUuid();
    const stamp = nowIso();
    const serviceIds = input.service_ids ?? [];

    if (!serviceIds.length) {
        throw new Error('Select at least one service.');
    }

    getDb().prepare(`
        INSERT INTO job_cards (uuid, customer_uuid, vehicle_uuid, status, notes, assigned_to, service_ids, created_at, updated_at)
        VALUES (?, ?, ?, 'open', ?, ?, ?, ?, ?)
    `).run(
        uuid,
        input.customer_uuid,
        input.vehicle_uuid || null,
        input.notes?.trim() || null,
        input.assigned_to || null,
        JSON.stringify(serviceIds),
        stamp,
        stamp,
    );

    enqueueMutation('job_card.create', {
        uuid,
        customer_id: `client:${input.customer_uuid}`,
        vehicle_id: input.vehicle_uuid ? `client:${input.vehicle_uuid}` : null,
        assigned_to: input.assigned_to || null,
        notes: input.notes?.trim() || null,
        service_ids: serviceIds,
        status: 'open',
    }, uuid);

    return { uuid };
}

export function updateJobCardStatusLocal(input) {
    const stamp = nowIso();
    const row = getDb().prepare('SELECT * FROM job_cards WHERE uuid = ?').get(input.uuid);

    if (!row) {
        throw new Error('Job card not found.');
    }

    getDb().prepare(`
        UPDATE job_cards
        SET status = ?, notes = COALESCE(?, notes), assigned_to = COALESCE(?, assigned_to), updated_at = ?
        WHERE uuid = ?
    `).run(input.status, input.notes ?? null, input.assigned_to ?? null, stamp, input.uuid);

    enqueueMutation('job_card.update_status', {
        job_card_id: `client:${input.uuid}`,
        status: input.status,
        notes: input.notes ?? row.notes,
        assigned_to: input.assigned_to ?? row.assigned_to,
    });

    return { uuid: input.uuid };
}

export function checkoutPosLocal(input) {
    if (input.method === 'mpesa') {
        throw new Error('M-Pesa requires an online connection. Use cash, card, or bank offline.');
    }

    const uuid = newUuid();
    const stamp = nowIso();
    const items = (input.items ?? []).map((item) => {
        const qty = Number(item.qty || item.quantity || 1);
        const price = Number(item.price || item.unit_price || 0);

        return {
            item_type: item.type || item.item_type || 'service',
            item_id: item.id || item.item_id,
            description: item.name || item.description,
            quantity: qty,
            unit_price: price,
            total: price * qty,
        };
    });

    if (!items.length) {
        throw new Error('Add at least one item to the cart.');
    }

    const amount = items.reduce((sum, item) => sum + Number(item.total), 0);
    let paymentMethodId = input.payment_method_id || null;

    if (!paymentMethodId) {
        const method = getDb().prepare(`
            SELECT id FROM payment_methods WHERE slug = ? AND is_active = 1 LIMIT 1
        `).get(input.method);
        paymentMethodId = method?.id ?? null;
    }

    getDb().prepare(`
        INSERT INTO sales (uuid, customer_uuid, vehicle_uuid, method, amount, items_json, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(
        uuid,
        input.customer_uuid,
        input.vehicle_uuid || null,
        input.method,
        amount,
        JSON.stringify(items),
        stamp,
    );

    enqueueMutation('pos.checkout', {
        customer_id: `client:${input.customer_uuid}`,
        vehicle_id: input.vehicle_uuid ? `client:${input.vehicle_uuid}` : null,
        job_card_id: null,
        payment_method_id: paymentMethodId,
        method: input.method,
        subtotal: amount,
        discount_amount: 0,
        tax_amount: 0,
        total_amount: amount,
        items,
    }, uuid);

    return { uuid, amount };
}

export function dashboardStats() {
    const database = getDb();

    return {
        customers: database.prepare('SELECT COUNT(*) AS c FROM customers').get().c,
        vehicles: database.prepare('SELECT COUNT(*) AS c FROM vehicles').get().c,
        open_jobs: database.prepare("SELECT COUNT(*) AS c FROM job_cards WHERE status IN ('open', 'in_progress')").get().c,
        pending_sync: pendingCount(),
        sales_today: database.prepare(`
            SELECT COALESCE(SUM(amount), 0) AS total FROM sales
            WHERE date(created_at) = date('now')
        `).get().total,
        bootstrap_synced_at: getMeta('bootstrap_synced_at'),
    };
}

export function listCustomers(search = '') {
    const q = `%${search.trim()}%`;

    return getDb().prepare(`
        SELECT c.*,
            (SELECT COUNT(*) FROM vehicles v WHERE v.customer_uuid = c.uuid) AS vehicle_count
        FROM customers c
        WHERE (? = '%%')
           OR c.full_name LIKE ?
           OR IFNULL(c.phone, '') LIKE ?
           OR EXISTS (
                SELECT 1 FROM vehicles v
                WHERE v.customer_uuid = c.uuid AND v.registration_number LIKE ?
           )
        ORDER BY c.full_name COLLATE NOCASE
        LIMIT 200
    `).all(q, q, q, q);
}

export function listVehicles(customerUuid = null) {
    if (customerUuid) {
        return getDb().prepare(`
            SELECT * FROM vehicles WHERE customer_uuid = ? ORDER BY registration_number
        `).all(customerUuid);
    }

    return getDb().prepare(`
        SELECT v.*, c.full_name AS customer_name
        FROM vehicles v
        JOIN customers c ON c.uuid = v.customer_uuid
        ORDER BY v.created_at DESC
        LIMIT 200
    `).all();
}

export function listJobCards() {
    return getDb().prepare(`
        SELECT j.*, c.full_name AS customer_name, v.registration_number
        FROM job_cards j
        JOIN customers c ON c.uuid = j.customer_uuid
        LEFT JOIN vehicles v ON v.uuid = j.vehicle_uuid
        ORDER BY j.updated_at DESC
        LIMIT 200
    `).all().map((row) => ({
        ...row,
        service_ids: JSON.parse(row.service_ids || '[]'),
    }));
}

export function listServices() {
    return getDb().prepare(`
        SELECT * FROM services WHERE is_active = 1 ORDER BY name COLLATE NOCASE
    `).all();
}

export function listProducts() {
    return getDb().prepare(`
        SELECT * FROM products WHERE is_active = 1 ORDER BY name COLLATE NOCASE
    `).all();
}

export function listPaymentMethods() {
    return getDb().prepare(`
        SELECT * FROM payment_methods
        WHERE is_active = 1
        ORDER BY
            CASE slug
                WHEN 'cash' THEN 1
                WHEN 'card' THEN 2
                WHEN 'bank' THEN 3
                WHEN 'mpesa' THEN 4
                ELSE 5
            END,
            name
    `).all();
}

export function listSales(limit = 50) {
    return getDb().prepare(`
        SELECT s.*, c.full_name AS customer_name
        FROM sales s
        JOIN customers c ON c.uuid = s.customer_uuid
        ORDER BY s.created_at DESC
        LIMIT ?
    `).all(limit);
}

export function createExpenseLocal(input) {
    const uuid = newUuid();
    const stamp = nowIso();
    const amount = Number(input.amount);

    if (!Number.isFinite(amount) || amount <= 0) {
        throw new Error('Enter a valid expense amount.');
    }

    getDb().prepare(`
        INSERT INTO expenses (uuid, category, description, amount, spent_on, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    `).run(
        uuid,
        String(input.category || '').trim(),
        String(input.description || '').trim(),
        amount,
        input.spent_on || stamp.slice(0, 10),
        stamp,
    );

    return { uuid, amount };
}

export function listExpenses(from = null, to = null) {
    let sql = `
        SELECT * FROM expenses
        WHERE 1 = 1
    `;
    const params = [];

    if (from) {
        sql += ' AND date(spent_on) >= date(?)';
        params.push(from);
    }

    if (to) {
        sql += ' AND date(spent_on) <= date(?)';
        params.push(to);
    }

    sql += ' ORDER BY spent_on DESC, created_at DESC LIMIT 200';

    return getDb().prepare(sql).all(...params);
}

export function financeOverview(from = null, to = null) {
    const database = getDb();
    let salesSql = 'SELECT COALESCE(SUM(amount), 0) AS total FROM sales WHERE 1 = 1';
    let expenseSql = 'SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE 1 = 1';
    const salesParams = [];
    const expenseParams = [];

    if (from) {
        salesSql += ' AND date(created_at) >= date(?)';
        expenseSql += ' AND date(spent_on) >= date(?)';
        salesParams.push(from);
        expenseParams.push(from);
    }

    if (to) {
        salesSql += ' AND date(created_at) <= date(?)';
        expenseSql += ' AND date(spent_on) <= date(?)';
        salesParams.push(to);
        expenseParams.push(to);
    }

    const income = Number(database.prepare(salesSql).get(...salesParams).total || 0);
    const expenses = Number(database.prepare(expenseSql).get(...expenseParams).total || 0);
    const sales = listSales(100).filter((sale) => {
        const day = sale.created_at?.slice(0, 10);
        if (from && day < from) return false;
        if (to && day > to) return false;
        return true;
    });
    const expenseRows = listExpenses(from, to);
    const breakdownMap = new Map();

    for (const row of expenseRows) {
        const key = row.category || 'Other';
        breakdownMap.set(key, (breakdownMap.get(key) || 0) + Number(row.amount));
    }

    const breakdown = [...breakdownMap.entries()]
        .map(([label, total]) => ({ label, total }))
        .sort((a, b) => b.total - a.total);
    const maxExpenseRow = breakdown[0]?.total || 1;

    return {
        from: from || null,
        to: to || null,
        income_total: income,
        expense_total: expenses,
        net_profit: income - expenses,
        sales,
        expenses: expenseRows,
        breakdown,
        max_expense_row: maxExpenseRow,
        manual_expenses_total: expenses,
    };
}

export function closeDatabase() {
    if (db) {
        db.close();
        db = null;
    }
}
