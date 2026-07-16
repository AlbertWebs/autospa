const api = window.autoSpaDesktop;
const view = document.getElementById('view');
const headerSection = document.getElementById('header-section');
const toastEl = document.getElementById('toast');
const syncBtn = document.getElementById('btn-sync');

const titles = {
    pos: 'Sales',
    live: 'Operations',
    'job-create': 'Operations',
    'check-in': 'Vehicles',
    finance: 'Finance',
    receipt: 'Sales',
};

let route = 'pos';
let financeTab = 'overview';
let cart = [];
let catalogSearch = '';
let financeRange = { from: '', to: '' };
let posCustomerId = '';
let posVehicleUuid = '';
let posJobCardUuid = '';
let posJobVehicleLabel = '';
let posPaymentMethodId = '';
let posCheckoutGuide = localStorage.getItem('posCheckoutGuideDismissed') !== 'true';
let posPendingReceipt = null;
let posLastReceipt = null;
let posShowCustomerModal = false;
let posShowCashModal = false;
let posCheckoutSubmitting = false;
let posCustomerForm = { full_name: '', phone: '', email: '', registration_number: '' };
let jobForm = {
    customerUuid: '',
    vehicleUuid: '',
    serviceIds: [],
    notes: '',
    showCustomerModal: false,
    submitting: false,
    customerForm: { full_name: '', phone: '', email: '', registration_number: '' },
};

function money(value) {
    return Number(value || 0).toLocaleString('en-KE', { maximumFractionDigits: 0 });
}

function kes(value) {
    return `KES ${money(value)}`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

function showToast(message, isError = false) {
    toastEl.classList.remove('hidden');
    toastEl.textContent = message;
    toastEl.className = isError
        ? 'rounded-xl border border-rose-400/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200'
        : 'rounded-xl border border-brand-primary/30 bg-brand-surface-high px-3 py-2 text-sm text-slate-200';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toastEl.classList.add('hidden'), 3500);
}

function setActiveNav() {
    const activeRoute = route === 'receipt' ? 'pos' : route;
    document.querySelectorAll('.nav-link').forEach((btn) => {
        btn.classList.toggle('is-active', btn.dataset.route === activeRoute);
    });
}

async function refreshSyncPill() {
    if (!syncBtn) {
        return;
    }

    try {
        const status = await api.syncStatus();
        const pending = status.pending || 0;

        if (pending > 0) {
            syncBtn.textContent = `Sync required (${pending}) · Sync now`;
            syncBtn.disabled = false;
            syncBtn.classList.remove('asp-btn-secondary');
            syncBtn.classList.add('asp-btn-primary');
            syncBtn.title = 'Click to sync queued changes';
        } else {
            syncBtn.textContent = 'Up to date';
            syncBtn.disabled = true;
            syncBtn.classList.add('asp-btn-secondary');
            syncBtn.classList.remove('asp-btn-primary');
            syncBtn.title = 'No pending offline changes';
        }
    } catch {
        syncBtn.textContent = 'Sync now';
        syncBtn.disabled = false;
        syncBtn.classList.add('asp-btn-secondary');
        syncBtn.classList.remove('asp-btn-primary');
    }
}

async function runSync() {
    showToast('Syncing…');
    try {
        const result = await api.syncNow();
        if (result.reason === 'needs_sign_in') {
            showToast('Sign in online to upload changes', true);
        } else if (result.reason === 'offline' || result.reason === 'unreachable') {
            showToast('Still offline — changes stay queued');
        } else if ((result.failed || 0) > 0) {
            const detail = Array.isArray(result.errors) && result.errors[0]
                ? ` — ${result.errors[0]}`
                : '';
            showToast(`${result.failed} change(s) could not be synced${detail}`, true);
        } else {
            showToast(`Synced ${result.synced || 0} change(s)`);
        }
        await refreshSyncPill();
        await render();
    } catch (error) {
        showToast(error.message || 'Sync failed', true);
    }
}

document.querySelectorAll('.nav-link').forEach((btn) => {
    btn.addEventListener('click', async () => {
        route = btn.dataset.route;
        if (route === 'finance') {
            financeTab = 'overview';
        }
        setActiveNav();
        await render();
    });
});

document.getElementById('btn-sync').addEventListener('click', runSync);
document.getElementById('btn-online').addEventListener('click', async () => {
    showToast('Reconnecting…');
    try {
        await api.goOnline();
    } catch (error) {
        showToast(error.message || 'Could not go online', true);
    }
});

function sectionHeader(eyebrow, trailingHtml = '') {
    return `
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <p class="asp-page-eyebrow !mb-0">${escapeHtml(eyebrow)}</p>
            ${trailingHtml}
        </div>
    `;
}

function customerOptionLabel(customer, vehicles = []) {
    const name = (customer.full_name || '').trim() || null;
    const regs = vehicles
        .filter((v) => v.customer_uuid === customer.uuid)
        .map((v) => v.registration_number);
    const primary = regs[0] || null;
    const extra = Math.max(regs.length - 1, 0);
    const vehicleSummary = primary
        ? `${primary}${extra > 0 ? ` +${extra} more` : ''}`
        : null;

    return name
        ? `${name}${vehicleSummary ? ` · ${vehicleSummary}` : ''}`
        : (vehicleSummary || 'Unnamed customer');
}

function vehicleLabel(vehicle) {
    const parts = [vehicle.registration_number, vehicle.make, vehicle.model].filter(Boolean);
    return parts.join(' · ') || 'Vehicle';
}

function jobStatusLabel(status) {
    if (status === 'open') return 'Queued';
    if (status === 'in_progress') return 'Washing';
    if (status === 'completed') return 'Ready';
    return status;
}

function jobProgress(status) {
    if (status === 'open') return 25;
    if (status === 'in_progress') return 70;
    if (status === 'completed') return 100;
    return 0;
}

function jobStatusButtonClass(status) {
    if (status === 'in_progress') return 'asp-btn asp-btn-secondary !px-3 !py-1.5 text-xs';
    if (status === 'completed') return 'asp-btn asp-btn-primary !px-3 !py-1.5 text-xs';
    return 'asp-btn asp-btn-ghost !px-3 !py-1.5 text-xs';
}

async function openPosFromCompletedJob(job, services = []) {
    const serviceIds = Array.isArray(job.service_ids) ? job.service_ids : [];

    cart = serviceIds.map((id) => {
        const service = services.find((entry) => Number(entry.id) === Number(id));
        if (!service) {
            return null;
        }

        return {
            id: service.id,
            name: service.name,
            price: Number(service.price),
            qty: 1,
            itemType: 'service',
            type: 'service',
        };
    }).filter(Boolean);

    posCustomerId = job.customer_uuid || '';
    posVehicleUuid = job.vehicle_uuid || '';
    posJobCardUuid = job.uuid || '';
    posJobVehicleLabel = job.registration_number || '';
    posCheckoutGuide = false;
    route = 'pos';
    setActiveNav();
    showToast('Job ready — complete checkout in POS');
    await render();
}

function selectedPaymentMethod(methods) {
    return methods.find((m) => String(m.id) === String(posPaymentMethodId)) || null;
}

function ensureCashDefault(methods) {
    if (posPaymentMethodId) {
        const stillValid = methods.some((m) => String(m.id) === String(posPaymentMethodId) && m.slug !== 'mpesa');
        if (stillValid) {
            return;
        }
    }

    const cash = methods.find((m) => m.slug === 'cash');
    if (cash) {
        posPaymentMethodId = String(cash.id);
    }
}

async function completePosSale(methods) {
    if (posCheckoutSubmitting) {
        return;
    }

    const method = selectedPaymentMethod(methods);

    if (!posCustomerId || !cart.length || !method) {
        showToast('Select customer, items, and payment method', true);
        return;
    }

    if (method.slug === 'mpesa') {
        showToast('M-Pesa is unavailable while offline.', true);
        return;
    }

    posCheckoutSubmitting = true;

    const customers = await api.listCustomers('');
    const customer = customers.find((c) => c.uuid === posCustomerId);
    const receiptItems = cart.map((item) => ({
        description: item.name,
        quantity: item.qty || 1,
        unit_price: item.price,
        total: Number(item.price) * Number(item.qty || 1),
    }));
    const amount = receiptItems.reduce((sum, item) => sum + Number(item.total), 0);
    const itemCount = cart.reduce((sum, i) => sum + Number(i.qty || 1), 0);

    try {
        await api.checkout({
            customer_uuid: posCustomerId,
            vehicle_uuid: posVehicleUuid || null,
            job_card_uuid: posJobCardUuid || null,
            payment_method_id: method.id,
            method: method.slug,
            items: cart.map((item) => ({
                id: item.id,
                type: item.itemType || item.type || 'product',
                name: item.name,
                price: item.price,
                qty: item.qty || 1,
            })),
        });

        posLastReceipt = {
            receipt_number: `OFF-${Date.now().toString().slice(-8)}`,
            amount,
            method_name: method.name,
            customer_name: customer?.full_name || 'Walk-in customer',
            customer_phone: customer?.phone || null,
            vehicle_label: null,
            items: receiptItems,
            issued_at: new Date().toISOString(),
            itemCount,
        };

        posPendingReceipt = {
            total: amount,
            itemCount,
            methodName: method.name,
        };

        cart = [];
        posCustomerId = '';
        posVehicleUuid = '';
        posJobCardUuid = '';
        posJobVehicleLabel = '';
        posShowCashModal = false;
        ensureCashDefault(methods);
        showToast('Sale queued for sync. Opening receipt…');
        await refreshSyncPill();
        api.syncNow().catch(() => {});
        route = 'receipt';
        setActiveNav();
        await render();
        window.setTimeout(() => window.print(), 400);
    } catch (error) {
        showToast(error.message || 'Checkout failed', true);
        await renderPos();
    } finally {
        posCheckoutSubmitting = false;
    }
}

async function renderPos() {
    const [customers, vehicles, products, methods] = await Promise.all([
        api.listCustomers(''),
        api.listVehicles(),
        api.listProducts(),
        api.listPaymentMethods(),
    ]);

    ensureCashDefault(methods);

    const catalog = products.map((p) => ({
        id: p.id,
        name: p.name,
        price: Number(p.price),
        itemType: 'product',
        sku: null,
    }));

    const filtered = catalog.filter((item) => {
        if (!catalogSearch.trim()) {
            return true;
        }

        return item.name.toLowerCase().includes(catalogSearch.trim().toLowerCase());
    });

    const subtotal = cart.reduce((sum, i) => sum + Number(i.price) * Number(i.qty || 1), 0);
    const total = subtotal;
    const itemCount = cart.reduce((sum, i) => sum + Number(i.qty || 1), 0);
    const method = selectedPaymentMethod(methods);
    const isMpesa = method?.slug === 'mpesa';
    const isCash = method?.slug === 'cash';
    const canCheckout = Boolean(posCustomerId && cart.length && posPaymentMethodId && !isMpesa);
    const selectedCustomer = customers.find((c) => c.uuid === posCustomerId);
    const selectedPhone = selectedCustomer?.phone || '';

    view.innerHTML = `
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <p class="asp-page-eyebrow !mb-0">Sales</p>
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Cart</p>
                <p class="font-mono text-sm font-bold text-slate-900 dark:text-white">${itemCount} items · KES ${money(total)}</p>
            </div>
        </div>

        <div class="asp-pos-layout">
            <div class="asp-pos-catalog">
                <div class="asp-panel overflow-hidden">
                    <div class="asp-panel-header">
                        <div>
                            <h2 class="asp-panel-title">Products</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Sell retail and consumable products. Add wash services on a job card first.</p>
                        </div>
                        <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">storefront</span>
                    </div>
                    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-brand-border/60">
                        <div class="asp-pos-toolbar">
                            <div class="asp-pos-search-wrap asp-pos-search-wrap--full">
                                <span class="material-symbols-outlined asp-pos-search-icon">search</span>
                                <input type="search" id="pos-search" value="${escapeHtml(catalogSearch)}" placeholder="Search products…" class="asp-pos-search" />
                            </div>
                        </div>
                    </div>
                    <div class="asp-pos-grid">
                        ${filtered.map((item) => `
                            <button type="button" class="group asp-pos-tile" data-add='${escapeHtml(JSON.stringify(item))}'>
                                <span class="asp-pos-tile-add material-symbols-outlined text-lg">add</span>
                                <span class="asp-pos-tile-type asp-pos-tile-type--product">product</span>
                                <span class="asp-pos-tile-name">${escapeHtml(item.name)}</span>
                                <span class="asp-pos-tile-price">KES ${money(item.price)}</span>
                            </button>
                        `).join('')}
                    </div>
                    ${filtered.length === 0 ? `
                        <div class="p-6">
                            <div class="asp-pos-empty">
                                <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">inventory_2</span>
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No items found</p>
                                <p class="mt-1 text-xs text-slate-500">Try a different search term.</p>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>

            <div class="asp-pos-checkout">
                <div class="asp-pos-cart">
                    <div class="asp-pos-cart-header">
                        <div>
                            <h2 class="asp-panel-title">Checkout</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">${itemCount} item(s) in cart</p>
                        </div>
                        <button type="button" id="clear-cart" class="asp-btn asp-btn-ghost !px-2 !py-1.5 text-xs" ${cart.length ? '' : 'hidden'}>Clear</button>
                    </div>

                    <form id="pos-form" class="asp-pos-cart-body asp-form !space-y-4">
                        ${posCheckoutGuide ? `
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-brand-border/60 dark:bg-brand-surface-high/60">
                                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Checkout Steps</p>
                                <ol class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    <li>1. Select or create a customer.</li>
                                    <li>2. Add products to the cart.</li>
                                    <li>3. Choose a payment method.</li>
                                    <li>4. Complete the sale to issue a receipt.</li>
                                </ol>
                                <div class="mt-3 flex justify-end">
                                    <button type="button" id="dismiss-guide" class="asp-btn asp-btn-primary !px-4 !py-2 text-sm">Understood</button>
                                </div>
                            </div>
                        ` : ''}

                        ${posJobCardUuid ? `
                            <div class="rounded-2xl border border-brand-primary/30 bg-brand-primary/5 px-4 py-3 dark:bg-brand-primary/10">
                                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">From Live Wash Board</p>
                                <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white">
                                    Job services loaded for checkout${posJobVehicleLabel ? ` · ${escapeHtml(posJobVehicleLabel)}` : ''}.
                                </p>
                            </div>
                        ` : ''}

                        <div class="asp-form-field">
                            <label class="asp-label" for="pos_customer">Customer</label>
                            <div class="asp-field-addon">
                                <select id="pos_customer" class="asp-select" required>
                                    <option value="">Select customer…</option>
                                    ${customers.map((c) => `
                                        <option value="${c.uuid}" ${posCustomerId === c.uuid ? 'selected' : ''}>${escapeHtml(customerOptionLabel(c, vehicles))}</option>
                                    `).join('')}
                                </select>
                                <button type="button" id="open-customer-modal" class="asp-btn asp-btn-secondary shrink-0 !px-3" title="Create new customer">
                                    <span class="material-symbols-outlined text-lg">person_add</span>
                                </button>
                            </div>
                            <p class="asp-field-hint">Required for invoicing.</p>
                            ${selectedPhone ? `<p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Phone: ${escapeHtml(selectedPhone)}</p>` : ''}
                        </div>

                        <div class="asp-pos-cart-lines">
                            ${cart.length === 0 ? `
                                <div class="asp-pos-empty !py-8">
                                    <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">shopping_cart</span>
                                    <p class="text-sm text-slate-500">Cart is empty</p>
                                </div>
                            ` : cart.map((item, index) => `
                                <div class="asp-pos-line">
                                    <div class="asp-pos-line-info">
                                        <p class="asp-pos-line-name">${escapeHtml(item.name)}</p>
                                        <p class="asp-pos-line-meta">KES ${money(item.price)} each</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="asp-pos-qty">
                                            <button type="button" class="asp-pos-qty-btn rounded-l-lg" data-dec="${index}">
                                                <span class="material-symbols-outlined text-base">remove</span>
                                            </button>
                                            <span class="asp-pos-qty-value">${item.qty || 1}</span>
                                            <button type="button" class="asp-pos-qty-btn rounded-r-lg" data-inc="${index}">
                                                <span class="material-symbols-outlined text-base">add</span>
                                            </button>
                                        </div>
                                        <button type="button" class="text-xs text-rose-500" data-remove="${index}">Remove</button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>

                        <div class="asp-pos-totals">
                            <div class="asp-pos-total-row">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-mono">KES ${money(subtotal)}</span>
                            </div>
                            <div class="asp-pos-total-row asp-pos-total-row--grand">
                                <span>Total</span>
                                <span class="font-mono text-brand-primary-dim dark:text-brand-primary">KES ${money(total)}</span>
                            </div>
                        </div>

                        <div class="asp-form-field">
                            <label class="asp-label" for="payment_method">Payment Method</label>
                            <select id="payment_method" class="asp-select" required>
                                <option value="">Select method…</option>
                                ${(methods.length ? methods : [
                                    { id: 'cash', slug: 'cash', name: 'Cash' },
                                    { id: 'card', slug: 'card', name: 'Card' },
                                    { id: 'bank', slug: 'bank', name: 'Bank' },
                                ]).map((m) => `
                                    <option value="${m.id}" ${String(posPaymentMethodId) === String(m.id) ? 'selected' : ''} ${m.slug === 'mpesa' ? 'disabled' : ''}>
                                        ${escapeHtml(m.name)}${m.slug === 'mpesa' ? ' (offline unavailable)' : ''}
                                    </option>
                                `).join('')}
                            </select>
                            ${isMpesa ? `<p class="mt-1 text-xs text-amber-600 dark:text-amber-400">M-Pesa STK push requires an internet connection.</p>` : ''}
                        </div>

                        ${posPendingReceipt ? `
                            <div class="rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-amber-700 dark:text-amber-300">Pending Sync</p>
                                <p class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Sale recorded locally — receipt will be issued when synced.</p>
                                <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    <p>${posPendingReceipt.itemCount} item(s) · KES ${money(posPendingReceipt.total)}</p>
                                    <p>Payment: ${escapeHtml(posPendingReceipt.methodName)}</p>
                                </div>
                                <button type="button" id="dismiss-pending" class="asp-btn asp-btn-ghost mt-3 !px-3 !py-1.5 text-xs">Dismiss</button>
                            </div>
                        ` : ''}

                        <button type="submit" class="asp-btn asp-btn-primary w-full justify-center !py-3" ${canCheckout && !posCheckoutSubmitting ? '' : 'disabled'}>
                            <span class="material-symbols-outlined text-lg">payments</span>
                            <span>${isMpesa ? 'M-Pesa unavailable offline' : 'Complete Sale (sync later)'}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        ${posShowCustomerModal ? `
            <div class="asp-modal-backdrop" id="customer-modal-backdrop">
                <div class="asp-modal">
                    <div class="asp-modal-header">
                        <div>
                            <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">New Customer</p>
                            <h3 class="asp-modal-title">Quick Add Customer</h3>
                        </div>
                        <button type="button" id="close-customer-modal" class="rounded-lg p-1 text-slate-400">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <form id="quick-customer-form" class="asp-form !space-y-5">
                        <div class="asp-modal-body space-y-5">
                            <div class="asp-form-field">
                                <label class="asp-label asp-label-required">Full Name</label>
                                <input name="full_name" class="asp-input" placeholder="Jane Mwangi" value="${escapeHtml(posCustomerForm.full_name)}" required />
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Phone</label>
                                <input name="phone" type="tel" class="asp-input" placeholder="+254 7XX XXX XXX" value="${escapeHtml(posCustomerForm.phone)}" />
                                <p class="asp-field-hint">Optional</p>
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Email</label>
                                <input name="email" type="email" class="asp-input" placeholder="customer@example.com" value="${escapeHtml(posCustomerForm.email)}" />
                                <p class="asp-field-hint">Optional</p>
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Car Registration</label>
                                <input name="registration_number" class="asp-input font-mono uppercase" placeholder="KDA 123A" value="${escapeHtml(posCustomerForm.registration_number)}" />
                                <p class="asp-field-hint">Optional</p>
                            </div>
                        </div>
                        <div class="asp-modal-footer">
                            <button type="button" id="cancel-customer-modal" class="asp-btn asp-btn-ghost">Cancel</button>
                            <button type="submit" class="asp-btn asp-btn-primary">Save Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        ` : ''}

        ${posShowCashModal ? `
            <div class="asp-modal-backdrop" id="cash-modal-backdrop">
                <div class="asp-modal">
                    <div class="asp-modal-header">
                        <div>
                            <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Cash Payment</p>
                            <h3 class="asp-modal-title">Confirm Cash Received</h3>
                        </div>
                        <button type="button" id="close-cash-modal" class="rounded-lg p-1 text-slate-400">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="asp-modal-body space-y-5">
                        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Have you received the cash payment?</p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                            <div class="flex justify-between"><span class="text-slate-500">Amount</span><span class="font-mono font-semibold">KES ${money(total)}</span></div>
                        </div>
                    </div>
                    <div class="asp-modal-footer">
                        <button type="button" id="cancel-cash-modal" class="asp-btn asp-btn-ghost">Not yet</button>
                        <button type="button" id="confirm-cash" class="asp-btn asp-btn-primary" ${posCheckoutSubmitting ? 'disabled' : ''}>Yes, cash received</button>
                    </div>
                </div>
            </div>
        ` : ''}
    `;

    document.getElementById('pos-search')?.addEventListener('input', async (e) => {
        catalogSearch = e.target.value;
        await renderPos();
    });

    document.getElementById('clear-cart')?.addEventListener('click', async () => {
        cart = [];
        await renderPos();
    });

    document.getElementById('dismiss-guide')?.addEventListener('click', async () => {
        posCheckoutGuide = false;
        localStorage.setItem('posCheckoutGuideDismissed', 'true');
        await renderPos();
    });

    document.getElementById('dismiss-pending')?.addEventListener('click', async () => {
        posPendingReceipt = null;
        await renderPos();
    });

    document.getElementById('pos_customer')?.addEventListener('change', async (e) => {
        posCustomerId = e.target.value;
        await renderPos();
    });

    document.getElementById('payment_method')?.addEventListener('change', async (e) => {
        posPaymentMethodId = e.target.value;
        await renderPos();
    });

    document.getElementById('open-customer-modal')?.addEventListener('click', async () => {
        posShowCustomerModal = true;
        posCustomerForm = { full_name: '', phone: '', email: '', registration_number: '' };
        await renderPos();
    });

    const closeCustomerModal = async () => {
        posShowCustomerModal = false;
        await renderPos();
    };

    document.getElementById('close-customer-modal')?.addEventListener('click', closeCustomerModal);
    document.getElementById('cancel-customer-modal')?.addEventListener('click', closeCustomerModal);

    document.getElementById('quick-customer-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        try {
            const created = await api.createCustomer({
                full_name: form.full_name.value,
                phone: form.phone.value,
                email: form.email.value,
                registration_number: form.registration_number.value,
            });
            posCustomerId = created.uuid;
            posShowCustomerModal = false;
            showToast('Customer created.');
            await refreshSyncPill();
            api.syncNow().catch(() => {});
            await renderPos();
        } catch (error) {
            showToast(error.message || 'Could not create customer.', true);
        }
    });

    view.querySelectorAll('[data-add]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const item = JSON.parse(btn.getAttribute('data-add'));
            const existing = cart.find((c) => c.id === item.id && c.itemType === item.itemType);
            if (existing) {
                existing.qty = (existing.qty || 1) + 1;
            } else {
                cart.push({ ...item, qty: 1, type: item.itemType });
            }
            await renderPos();
        });
    });

    view.querySelectorAll('[data-inc]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            cart[Number(btn.dataset.inc)].qty += 1;
            await renderPos();
        });
    });

    view.querySelectorAll('[data-dec]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const i = Number(btn.dataset.dec);
            if (cart[i].qty <= 1) {
                cart.splice(i, 1);
            } else {
                cart[i].qty -= 1;
            }
            await renderPos();
        });
    });

    view.querySelectorAll('[data-remove]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            cart.splice(Number(btn.dataset.remove), 1);
            await renderPos();
        });
    });

    document.getElementById('pos-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!canCheckout) {
            return;
        }

        if (isCash) {
            posShowCashModal = true;
            await renderPos();
            return;
        }

        await completePosSale(methods);
    });

    const closeCashModal = async () => {
        if (posCheckoutSubmitting) {
            return;
        }

        posShowCashModal = false;
        await renderPos();
    };

    document.getElementById('close-cash-modal')?.addEventListener('click', closeCashModal);
    document.getElementById('cancel-cash-modal')?.addEventListener('click', closeCashModal);
    document.getElementById('confirm-cash')?.addEventListener('click', async () => {
        await completePosSale(methods);
    });
}

async function renderReceipt() {
    const receipt = posLastReceipt;

    if (!receipt) {
        view.innerHTML = `
            <div class="asp-receipt-page mx-auto max-w-3xl">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900/40">
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No offline receipt found</p>
                    <p class="mt-1 text-xs text-slate-500">Complete an offline sale to generate a printable receipt.</p>
                    <button type="button" id="back-to-pos" class="asp-btn asp-btn-primary mt-4 inline-flex">Back to POS</button>
                </div>
            </div>
        `;
        document.getElementById('back-to-pos')?.addEventListener('click', async () => {
            route = 'pos';
            await render();
        });
        return;
    }

    const issuedLabel = receipt.issued_at ? new Date(receipt.issued_at).toLocaleString() : 'N/A';
    const lines = (receipt.items || []).map((item) => `
        <tr>
            <td class="px-4 py-3 text-slate-900 dark:text-white">${escapeHtml(item.description)}</td>
            <td class="px-4 py-3 text-right font-mono">${money(item.quantity)}</td>
            <td class="px-4 py-3 text-right font-mono">${money(item.unit_price)}</td>
            <td class="px-4 py-3 text-right font-mono">${money(item.total)}</td>
        </tr>
    `).join('');

    view.innerHTML = `
        <div class="asp-receipt-page mx-auto max-w-3xl">
            <div class="asp-receipt-actions mb-6 flex flex-wrap items-center gap-2 print:hidden">
                <button type="button" id="back-to-pos" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    <span class="material-symbols-outlined text-base">point_of_sale</span>
                    New Sale
                </button>
                <button type="button" id="print-receipt" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined text-base">print</span>
                    Print Receipt
                </button>
            </div>

            <article class="asp-receipt-print rounded-2xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-brand-border/60 dark:bg-brand-surface sm:p-8">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                    <div>
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Pending Sync Receipt</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">${escapeHtml(receipt.receipt_number)}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sale saved offline — final receipt number issues when synced.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Total Received</p>
                        <p class="font-mono text-2xl font-bold text-brand-primary-dim dark:text-brand-primary">KES ${money(receipt.amount)}</p>
                    </div>
                </header>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Customer</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Name</dt><dd class="font-medium">${escapeHtml(receipt.customer_name || 'Walk-in customer')}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>${escapeHtml(receipt.customer_phone || 'N/A')}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>${escapeHtml(receipt.vehicle_label || 'N/A')}</dd></div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Transaction</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Issued</dt><dd>${escapeHtml(issuedLabel)}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Payment</dt><dd>${escapeHtml(receipt.method_name || 'Cash')}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200">Queued for sync</span></dd></div>
                        </dl>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Items</h3>
                    <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-slate-500">Description</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-500">Qty</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-500">Unit Price</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                ${lines}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">${escapeHtml(receipt.method_name || 'Cash')}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Paid offline</p>
                        </div>
                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">KES ${money(receipt.amount)}</p>
                    </div>
                </div>
            </article>
        </div>
    `;

    document.getElementById('back-to-pos')?.addEventListener('click', async () => {
        route = 'pos';
        await render();
    });
    document.getElementById('print-receipt')?.addEventListener('click', () => window.print());
}

async function renderLiveBoard() {
    const [jobs, services] = await Promise.all([
        api.listJobs(),
        api.listServices(),
    ]);
    const live = jobs.filter((j) => ['open', 'in_progress'].includes(j.status));
    const queued = live.filter((j) => j.status === 'open').length;
    const washing = live.filter((j) => j.status === 'in_progress').length;
    const serviceName = (id) => services.find((s) => Number(s.id) === Number(id))?.name;

    view.innerHTML = `
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="asp-page-eyebrow">Operations</p>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Live</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track wash bay activity in real time and move vehicles through the wash stages.</p>
            </div>
            <button type="button" class="asp-btn asp-btn-primary" data-goto="job-create">
                <span class="material-symbols-outlined text-lg">add</span>
                New Job Card
            </button>
        </div>

        <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Live Cars</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">${live.length}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Queued</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">${queued}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Washing</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">${washing}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">All jobs</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">${jobs.length}</p>
            </div>
        </div>

        ${live.length === 0 ? `
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900/40">
                <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">local_car_wash</span>
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No live wash jobs</p>
                <p class="mt-1 text-xs text-slate-500">Cars waiting for wash or currently being washed will appear here.</p>
                <button type="button" class="asp-btn asp-btn-primary mt-4" data-goto="job-create">New Job Card</button>
            </div>
        ` : `
            <div class="grid gap-5 xl:grid-cols-2">
                ${live.map((j) => {
                    const names = (j.service_ids || []).map(serviceName).filter(Boolean);
                    const progress = jobProgress(j.status);
                    return `
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-xs uppercase tracking-widest text-slate-400">Job</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">${escapeHtml(j.registration_number || 'No vehicle')}</h2>
                                <p class="mt-1 text-sm font-medium text-brand-primary-dim dark:text-brand-primary">${escapeHtml(names.join(', ') || 'No services')}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                j.status === 'open'
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'
                                    : 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200'
                            }">${escapeHtml(jobStatusLabel(j.status))}</span>
                        </div>

                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Customer</span>
                                <span class="font-medium text-slate-900 dark:text-white">${escapeHtml(j.customer_name)}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Wash Stage</span>
                                <span class="font-medium text-slate-900 dark:text-white">${progress}%</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: ${progress}%;"></div>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-slate-200/80 pt-4 dark:border-brand-border/60">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Change Washing Status</p>
                            <div class="flex flex-wrap gap-2">
                                ${['open', 'in_progress', 'completed'].map((status) => `
                                    <button
                                        type="button"
                                        class="${jobStatusButtonClass(status)}"
                                        data-status-for="${j.uuid}"
                                        data-status="${status}"
                                        ${j.status === status ? 'disabled' : ''}
                                    >${status === 'open' ? 'Queued' : (status === 'in_progress' ? 'Washing' : 'Ready')}</button>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    `;
                }).join('')}
            </div>
        `}
    `;

    view.querySelectorAll('[data-goto="job-create"]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            route = 'job-create';
            setActiveNav();
            await render();
        });
    });

    view.querySelectorAll('[data-status-for]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const uuid = btn.dataset.statusFor;
            const status = btn.dataset.status;
            const job = live.find((entry) => entry.uuid === uuid);

            if (!job || job.status === status) {
                return;
            }

            try {
                await api.updateJobStatus({ uuid, status });
                showToast(status === 'completed' ? 'Marked ready — opening POS…' : 'Status updated');
                await refreshSyncPill();
                api.syncNow().catch(() => {});

                if (status === 'completed') {
                    await openPosFromCompletedJob({ ...job, status }, services);
                    return;
                }

                await renderLiveBoard();
            } catch (error) {
                showToast(error.message || 'Update failed', true);
            }
        });
    });
}

async function renderJobCreate() {
    const [customers, vehicles, services] = await Promise.all([
        api.listCustomers(''),
        api.listVehicles(),
        api.listServices(),
    ]);

    const filteredVehicles = jobForm.customerUuid
        ? vehicles.filter((v) => v.customer_uuid === jobForm.customerUuid)
        : [];
    const selectedCustomer = customers.find((c) => c.uuid === jobForm.customerUuid);
    const selectedVehicle = vehicles.find((v) => v.uuid === jobForm.vehicleUuid);
    const selectedServices = services.filter((s) => jobForm.serviceIds.includes(Number(s.id)));
    const serviceTotal = selectedServices.reduce((sum, s) => sum + Number(s.price || 0), 0);

    const servicesByCategory = {};
    for (const service of services) {
        const key = service.category_name || 'General';
        if (!servicesByCategory[key]) {
            servicesByCategory[key] = [];
        }
        servicesByCategory[key].push(service);
    }

    view.innerHTML = `
        ${sectionHeader('Operations')}

        <div class="asp-panel">
            <div class="asp-panel-body">
                <form id="job-form" class="asp-form">
                    <div class="asp-job-card-form-layout">
                        <div class="asp-job-card-form-main">
                            <section class="asp-job-card-step">
                                <div class="asp-job-card-step-header">
                                    <span class="asp-job-card-step-badge">1</span>
                                    <div>
                                        <h2 class="asp-job-card-step-title">Vehicle &amp; Customer</h2>
                                        <p class="asp-job-card-step-desc">Who is checking in and which vehicle is being washed.</p>
                                    </div>
                                </div>

                                <div class="asp-form-grid">
                                    <div class="asp-form-field sm:col-span-2">
                                        <label class="asp-label asp-label-required" for="job_customer">Customer</label>
                                        <div class="asp-field-addon">
                                            <select id="job_customer" name="customer_uuid" class="asp-select" required>
                                                <option value="">Select customer…</option>
                                                ${customers.map((c) => `
                                                    <option value="${c.uuid}" ${jobForm.customerUuid === c.uuid ? 'selected' : ''}>${escapeHtml(customerOptionLabel(c, vehicles))}</option>
                                                `).join('')}
                                            </select>
                                            <button type="button" id="open-job-customer-modal" class="asp-btn asp-btn-secondary shrink-0 !px-3" title="Create new customer">
                                                <span class="material-symbols-outlined text-lg">person_add</span>
                                            </button>
                                        </div>
                                        <p class="asp-field-hint">
                                            <button type="button" id="open-job-customer-link" class="text-brand-primary-dim hover:underline dark:text-brand-primary">+ Create new customer</button>
                                        </p>
                                    </div>

                                    <div class="asp-form-field sm:col-span-2">
                                        <label class="asp-label" for="job_vehicle">Vehicle</label>
                                        <select id="job_vehicle" name="vehicle_uuid" class="asp-select" ${jobForm.customerUuid ? '' : 'disabled'}>
                                            <option value="">No vehicle (optional)</option>
                                            ${filteredVehicles.map((v) => `
                                                <option value="${v.uuid}" ${jobForm.vehicleUuid === v.uuid ? 'selected' : ''}>${escapeHtml(vehicleLabel(v))}</option>
                                            `).join('')}
                                        </select>
                                        <p class="asp-field-hint">${jobForm.customerUuid ? 'Optional — leave blank for carpet wash or non-vehicle jobs.' : 'Select a customer first to choose a vehicle.'}</p>
                                    </div>
                                </div>
                            </section>

                            <section class="asp-job-card-step">
                                <div class="asp-job-card-step-header">
                                    <span class="asp-job-card-step-badge">2</span>
                                    <div>
                                        <h2 class="asp-job-card-step-title">Services</h2>
                                        <p class="asp-job-card-step-desc">Select one or more wash or detailing services.</p>
                                    </div>
                                </div>

                                ${services.length === 0 ? `
                                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                        No active services found. Sync catalog while online first.
                                    </div>
                                ` : `
                                    <div class="space-y-5">
                                        ${Object.entries(servicesByCategory).map(([categoryName, categoryServices]) => `
                                            <div class="space-y-3">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">${escapeHtml(categoryName)}</p>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    ${categoryServices.map((s) => {
                                                        const checked = jobForm.serviceIds.includes(Number(s.id));
                                                        return `
                                                            <label class="asp-service-pick-card">
                                                                <input type="checkbox" name="service_ids" value="${s.id}" class="asp-checkbox" ${checked ? 'checked' : ''} />
                                                                <span class="asp-service-pick-content">
                                                                    <span class="asp-service-pick-icon">
                                                                        <span class="material-symbols-outlined text-[20px]">local_car_wash</span>
                                                                    </span>
                                                                    <span class="min-w-0 flex-1">
                                                                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(s.name)}</span>
                                                                        <span class="mt-0.5 block font-mono text-xs text-slate-500 dark:text-slate-400">KES ${money(s.price)}</span>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        `;
                                                    }).join('')}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                `}
                            </section>

                            <section class="asp-job-card-step">
                                <div class="asp-job-card-step-header">
                                    <span class="asp-job-card-step-badge">3</span>
                                    <div>
                                        <h2 class="asp-job-card-step-title">Notes</h2>
                                        <p class="asp-job-card-step-desc">Optional instructions for the wash bay team.</p>
                                    </div>
                                </div>
                                <div class="asp-form-field">
                                    <label class="asp-label" for="job_notes">Notes</label>
                                    <textarea id="job_notes" name="notes" class="asp-textarea" rows="4" placeholder="Damage notes, special requests, bay instructions…">${escapeHtml(jobForm.notes)}</textarea>
                                </div>
                            </section>
                        </div>

                        <aside class="asp-job-card-form-aside">
                            <div class="asp-job-card-summary">
                                <div class="asp-job-card-summary-header">
                                    <span class="material-symbols-outlined text-brand-primary">receipt_long</span>
                                    <h3 class="asp-job-card-summary-title">Job Summary</h3>
                                </div>
                                <dl class="asp-job-card-summary-meta">
                                    <div>
                                        <dt>Customer</dt>
                                        <dd>${escapeHtml(selectedCustomer?.full_name || 'Not selected')}</dd>
                                    </div>
                                    <div>
                                        <dt>Vehicle</dt>
                                        <dd>${escapeHtml(selectedVehicle ? vehicleLabel(selectedVehicle) : 'Not selected')}</dd>
                                    </div>
                                </dl>
                                <div class="asp-job-card-summary-divider"></div>
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Selected services</p>
                                    ${selectedServices.length === 0
                                        ? '<p class="text-sm text-slate-400">No services selected yet.</p>'
                                        : `<ul class="space-y-2">${selectedServices.map((s) => `
                                            <li class="flex items-center justify-between gap-3 text-sm">
                                                <span class="truncate text-slate-700 dark:text-slate-200">${escapeHtml(s.name)}</span>
                                                <span class="shrink-0 font-mono text-xs text-slate-500">KES ${money(s.price)}</span>
                                            </li>
                                        `).join('')}</ul>`}
                                </div>
                                <div class="asp-job-card-summary-total">
                                    <span>Estimated total</span>
                                    <span class="font-display text-xl font-bold text-slate-900 dark:text-white">KES ${money(serviceTotal)}</span>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <div class="asp-form-actions mt-8">
                        <button type="submit" class="asp-btn asp-btn-primary min-w-[10rem]" ${jobForm.submitting ? 'disabled' : ''}>
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            ${jobForm.submitting ? 'Creating…' : 'Create Job Card'}
                        </button>
                        <button type="button" id="cancel-job" class="asp-btn asp-btn-ghost">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        ${jobForm.showCustomerModal ? `
            <div class="asp-modal-backdrop" id="job-customer-modal-backdrop">
                <div class="asp-modal">
                    <div class="asp-modal-header">
                        <div>
                            <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">New Customer</p>
                            <h3 class="asp-modal-title">Quick Add Customer</h3>
                        </div>
                        <button type="button" id="close-job-customer-modal" class="rounded-lg p-1 text-slate-400">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <form id="job-quick-customer-form" class="asp-form !space-y-5">
                        <div class="asp-modal-body space-y-5">
                            <div class="asp-form-field">
                                <label class="asp-label asp-label-required">Full Name</label>
                                <input name="full_name" class="asp-input" required value="${escapeHtml(jobForm.customerForm.full_name)}" />
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Phone</label>
                                <input name="phone" class="asp-input" value="${escapeHtml(jobForm.customerForm.phone)}" />
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Email</label>
                                <input name="email" type="email" class="asp-input" value="${escapeHtml(jobForm.customerForm.email)}" />
                            </div>
                            <div class="asp-form-field">
                                <label class="asp-label">Registration number</label>
                                <input name="registration_number" class="asp-input font-mono uppercase" placeholder="Optional vehicle" value="${escapeHtml(jobForm.customerForm.registration_number)}" />
                            </div>
                        </div>
                        <div class="asp-modal-footer">
                            <button type="button" id="cancel-job-customer-modal" class="asp-btn asp-btn-ghost">Cancel</button>
                            <button type="submit" class="asp-btn asp-btn-primary">Save Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        ` : ''}
    `;

    const openCustomerModal = async () => {
        jobForm.showCustomerModal = true;
        await renderJobCreate();
    };

    document.getElementById('open-job-customer-modal')?.addEventListener('click', openCustomerModal);
    document.getElementById('open-job-customer-link')?.addEventListener('click', openCustomerModal);
    document.getElementById('cancel-job')?.addEventListener('click', async () => {
        route = 'live';
        await render();
    });

    document.getElementById('job_customer')?.addEventListener('change', async (e) => {
        jobForm.customerUuid = e.target.value;
        jobForm.vehicleUuid = '';
        await renderJobCreate();
    });

    document.getElementById('job_vehicle')?.addEventListener('change', async (e) => {
        jobForm.vehicleUuid = e.target.value;
        await renderJobCreate();
    });

    document.getElementById('job_notes')?.addEventListener('input', (e) => {
        jobForm.notes = e.target.value;
    });

    view.querySelectorAll('input[name="service_ids"]').forEach((input) => {
        input.addEventListener('change', async () => {
            const id = Number(input.value);
            if (input.checked) {
                if (!jobForm.serviceIds.includes(id)) {
                    jobForm.serviceIds = [...jobForm.serviceIds, id];
                }
            } else {
                jobForm.serviceIds = jobForm.serviceIds.filter((entry) => entry !== id);
            }
            await renderJobCreate();
        });
    });

    const closeCustomerModal = async () => {
        jobForm.showCustomerModal = false;
        await renderJobCreate();
    };

    document.getElementById('close-job-customer-modal')?.addEventListener('click', closeCustomerModal);
    document.getElementById('cancel-job-customer-modal')?.addEventListener('click', closeCustomerModal);
    document.getElementById('job-customer-modal-backdrop')?.addEventListener('click', async (e) => {
        if (e.target.id === 'job-customer-modal-backdrop') {
            await closeCustomerModal();
        }
    });

    document.getElementById('job-quick-customer-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        try {
            const created = await api.createCustomer({
                full_name: form.full_name.value,
                phone: form.phone.value || null,
                email: form.email.value || null,
                registration_number: form.registration_number.value || null,
            });
            jobForm.customerUuid = created.uuid;
            jobForm.vehicleUuid = created.vehicle?.uuid || '';
            jobForm.showCustomerModal = false;
            jobForm.customerForm = { full_name: '', phone: '', email: '', registration_number: '' };
            showToast('Customer created');
            await refreshSyncPill();
            await renderJobCreate();
        } catch (error) {
            showToast(error.message || 'Could not create customer', true);
        }
    });

    document.getElementById('job-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!jobForm.customerUuid) {
            showToast('Select a customer', true);
            return;
        }

        if (!jobForm.serviceIds.length) {
            showToast('Select at least one service', true);
            return;
        }

        if (jobForm.submitting) {
            return;
        }

        jobForm.submitting = true;
        await renderJobCreate();

        try {
            await api.createJob({
                customer_uuid: jobForm.customerUuid,
                vehicle_uuid: jobForm.vehicleUuid || null,
                notes: jobForm.notes,
                service_ids: jobForm.serviceIds,
            });

            jobForm = {
                customerUuid: '',
                vehicleUuid: '',
                serviceIds: [],
                notes: '',
                showCustomerModal: false,
                submitting: false,
                customerForm: { full_name: '', phone: '', email: '', registration_number: '' },
            };

            showToast('Job card created — opening Live Board');
            await refreshSyncPill();
            api.syncNow().catch(() => {});
            route = 'live';
            setActiveNav();
            await render();
        } catch (error) {
            jobForm.submitting = false;
            showToast(error.message || 'Could not create job', true);
            await renderJobCreate();
        }
    });
}

async function renderCheckIn() {
    const services = await api.listServices();

    view.innerHTML = `
        <div class="mb-6">
            <p class="asp-page-eyebrow">Vehicles</p>
            <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Vehicle Check-In</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Quick intake for walk-in vehicles. Creates customer, vehicle, and job card offline.</p>
        </div>

        <div class="asp-panel max-w-3xl">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Check-in form</h2>
                </div>
            </div>
            <div class="asp-panel-body">
                <form id="checkin-form" class="asp-form space-y-5">
                    <div class="asp-form-grid">
                        <div class="asp-form-field">
                            <label class="asp-label asp-label-required">Customer full name</label>
                            <input name="full_name" class="asp-input" required />
                        </div>
                        <div class="asp-form-field">
                            <label class="asp-label">Phone</label>
                            <input name="phone" class="asp-input" />
                        </div>
                        <div class="asp-form-field">
                            <label class="asp-label asp-label-required">Registration number</label>
                            <input name="registration_number" class="asp-input font-mono uppercase" required placeholder="KDA 123A" />
                        </div>
                        <div class="asp-form-field">
                            <label class="asp-label">Make</label>
                            <input name="make" class="asp-input" />
                        </div>
                        <div class="asp-form-field">
                            <label class="asp-label">Model</label>
                            <input name="model" class="asp-input" />
                        </div>
                    </div>
                    <div class="asp-form-field">
                        <label class="asp-label">Services for this visit</label>
                        <div class="mt-2 space-y-2 rounded-xl border border-slate-200 p-3 dark:border-brand-border/60">
                            ${services.length ? services.map((s) => `
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 hover:bg-brand-primary/5">
                                    <input type="checkbox" name="service_ids" value="${s.id}" class="asp-checkbox" />
                                    <span class="text-sm text-slate-700 dark:text-slate-200">${escapeHtml(s.name)}</span>
                                    <span class="ml-auto font-mono text-sm text-brand-primary">KES ${money(s.price)}</span>
                                </label>
                            `).join('') : `<p class="text-sm text-slate-500">Sync catalog online first to attach services.</p>`}
                        </div>
                    </div>
                    <div class="asp-form-field">
                        <label class="asp-label">Notes</label>
                        <textarea name="notes" class="asp-textarea" rows="2"></textarea>
                    </div>
                    <div class="asp-form-actions">
                        <button type="submit" class="asp-btn asp-btn-primary min-w-[10rem]">Check in vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.getElementById('checkin-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const serviceIds = [...form.querySelectorAll('input[name="service_ids"]:checked')].map((el) => Number(el.value));

        try {
            const customer = await api.createCustomer({
                full_name: form.full_name.value,
                phone: form.phone.value,
                registration_number: form.registration_number.value,
                notes: form.notes.value,
            });

            let vehicleUuid = customer.vehicle?.uuid || null;

            if (!vehicleUuid) {
                const vehicle = await api.createVehicle({
                    customer_uuid: customer.uuid,
                    registration_number: form.registration_number.value,
                    make: form.make.value,
                    model: form.model.value,
                });
                vehicleUuid = vehicle.uuid;
            }

            if (serviceIds.length) {
                await api.createJob({
                    customer_uuid: customer.uuid,
                    vehicle_uuid: vehicleUuid,
                    notes: form.notes.value,
                    service_ids: serviceIds,
                });
            }

            form.reset();
            showToast('Vehicle checked in');
            await refreshSyncPill();
            api.syncNow().catch(() => {});
        } catch (error) {
            showToast(error.message || 'Check-in failed', true);
        }
    });
}

function financeTabsHtml(active) {
    const tabs = [
        ['overview', 'Overview'],
        ['income', 'Income'],
        ['expenses', 'Expenses'],
        ['profit-loss', 'Profit & Loss'],
    ];

    return `
        <div class="mb-6 flex flex-wrap gap-2">
            ${tabs.map(([id, label]) => `
                <button type="button" data-finance-tab="${id}" class="asp-btn !py-2 ${active === id ? 'asp-btn-primary' : 'asp-btn-secondary'}">${label}</button>
            `).join('')}
        </div>
    `;
}

function financeFilterHtml() {
    return `
        <form id="finance-filter" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
            <div class="asp-form-field">
                <label class="asp-label" for="from">From</label>
                <input id="from" name="from" type="date" class="asp-input" value="${escapeHtml(financeRange.from || '')}" />
            </div>
            <div class="asp-form-field">
                <label class="asp-label" for="to">To</label>
                <input id="to" name="to" type="date" class="asp-input" value="${escapeHtml(financeRange.to || '')}" />
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update</button>
            </div>
        </form>
    `;
}

async function renderFinance() {
    const report = await api.financeOverview({
        from: financeRange.from || null,
        to: financeRange.to || null,
    });

    let body = '';

    if (financeTab === 'overview') {
        body = `
            <div class="mb-6">
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Finance Overview</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Local offline totals from sales and recorded expenses. Cloud finance updates when you reconnect.</p>
            </div>
            ${financeTabsHtml('overview')}
            ${financeFilterHtml()}
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="asp-stat asp-stat--revenue">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Income</p>
                            <p class="asp-stat-value">${kes(report.income_total)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">south_west</span></div>
                    </div>
                </div>
                <div class="asp-stat">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Expenses</p>
                            <p class="asp-stat-value">${kes(report.expense_total)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">north_east</span></div>
                    </div>
                </div>
                <div class="asp-stat ${report.net_profit >= 0 ? 'asp-stat--revenue' : 'asp-stat--payments'}">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Net Profit</p>
                            <p class="asp-stat-value">${kes(report.net_profit)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">trending_up</span></div>
                    </div>
                </div>
            </div>
        `;
    }

    if (financeTab === 'income') {
        body = `
            <div class="mb-6">
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Income</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Local POS sales recorded on this device.</p>
            </div>
            ${financeTabsHtml('income')}
            ${financeFilterHtml()}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Sales entries</h2>
                </div>
                <div class="asp-table-wrap">
                    <table class="asp-table">
                        <thead>
                            <tr>
                                <th class="asp-th">When</th>
                                <th class="asp-th">Customer</th>
                                <th class="asp-th">Method</th>
                                <th class="asp-th text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${report.sales.length ? report.sales.map((s) => `
                                <tr class="asp-table-row">
                                    <td class="asp-td">${escapeHtml(new Date(s.created_at).toLocaleString())}</td>
                                    <td class="asp-td">${escapeHtml(s.customer_name)}</td>
                                    <td class="asp-td">${escapeHtml(s.method)}</td>
                                    <td class="asp-td text-right font-mono">${kes(s.amount)}</td>
                                </tr>
                            `).join('') : `
                                <tr><td colspan="4" class="asp-td py-8 text-center text-slate-500">No income entries yet</td></tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (financeTab === 'expenses') {
        body = `
            <div class="mb-6">
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Expenses</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record operating costs like rent, utilities, transport, and other direct expenses.</p>
            </div>
            ${financeTabsHtml('expenses')}
            ${financeFilterHtml()}
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-lg font-semibold">Record Expense</h2>
                <form id="expense-form" class="grid gap-4 md:grid-cols-2">
                    <div class="asp-form-field">
                        <label class="asp-label asp-label-required">Category</label>
                        <input name="category" class="asp-input" placeholder="Rent, Utilities, Fuel..." required />
                    </div>
                    <div class="asp-form-field">
                        <label class="asp-label asp-label-required">Amount</label>
                        <input name="amount" type="number" step="0.01" min="0.01" class="asp-input" required />
                    </div>
                    <div class="asp-form-field">
                        <label class="asp-label asp-label-required">Description</label>
                        <input name="description" class="asp-input" placeholder="Monthly office rent" required />
                    </div>
                    <div class="asp-form-field">
                        <label class="asp-label asp-label-required">Expense Date</label>
                        <input name="spent_on" type="date" class="asp-input" value="${today()}" required />
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Save Expense</button>
                    </div>
                </form>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="asp-stat">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Total Expenses</p>
                            <p class="asp-stat-value">${kes(report.expense_total)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">north_east</span></div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Manual Expense Entries</h2>
                </div>
                <div class="asp-table-wrap">
                    <table class="asp-table">
                        <thead>
                            <tr>
                                <th class="asp-th">Date</th>
                                <th class="asp-th">Category</th>
                                <th class="asp-th">Description</th>
                                <th class="asp-th text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${report.expenses.length ? report.expenses.map((row) => `
                                <tr class="asp-table-row">
                                    <td class="asp-td">${escapeHtml(row.spent_on)}</td>
                                    <td class="asp-td">${escapeHtml(row.category)}</td>
                                    <td class="asp-td">${escapeHtml(row.description)}</td>
                                    <td class="asp-td text-right font-mono">${kes(row.amount)}</td>
                                </tr>
                            `).join('') : `
                                <tr><td colspan="4" class="asp-td py-8 text-center text-slate-500">No expenses yet</td></tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (financeTab === 'profit-loss') {
        body = `
            <div class="mb-6">
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Profit &amp; Loss</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Local income minus expenses for the selected period.</p>
            </div>
            ${financeTabsHtml('profit-loss')}
            ${financeFilterHtml()}
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="asp-stat asp-stat--revenue">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Income</p>
                            <p class="asp-stat-value">${kes(report.income_total)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">south_west</span></div>
                    </div>
                </div>
                <div class="asp-stat">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Expenses</p>
                            <p class="asp-stat-value">${kes(report.expense_total)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">north_east</span></div>
                    </div>
                </div>
                <div class="asp-stat ${report.net_profit >= 0 ? 'asp-stat--revenue' : 'asp-stat--payments'}">
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="asp-stat-label">Net Profit</p>
                            <p class="asp-stat-value">${kes(report.net_profit)}</p>
                        </div>
                        <div class="asp-stat-icon"><span class="material-symbols-outlined text-[22px]">trending_up</span></div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-lg font-semibold">Expense Breakdown</h2>
                <div class="space-y-3">
                    ${report.breakdown.length ? report.breakdown.map((row) => `
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-300">${escapeHtml(row.label)}</span>
                                <span class="font-mono font-medium">${kes(row.total)}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-rose-500" style="width:${Math.round((row.total / (report.max_expense_row || 1)) * 100)}%"></div>
                            </div>
                        </div>
                    `).join('') : `<p class="text-sm text-slate-500">No expense categories yet.</p>`}
                </div>
            </div>
        `;
    }

    view.innerHTML = `
        <p class="asp-page-eyebrow">Finance</p>
        ${body}
    `;

    view.querySelectorAll('[data-finance-tab]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            financeTab = btn.dataset.financeTab;
            await renderFinance();
        });
    });

    document.getElementById('finance-filter')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(e.target);
        financeRange = {
            from: data.get('from') || '',
            to: data.get('to') || '',
        };
        await renderFinance();
    });

    document.getElementById('expense-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        try {
            await api.createExpense({
                category: form.category.value,
                amount: form.amount.value,
                description: form.description.value,
                spent_on: form.spent_on.value,
            });
            form.reset();
            form.spent_on.value = today();
            showToast('Expense saved locally');
            await renderFinance();
        } catch (error) {
            showToast(error.message || 'Could not save expense', true);
        }
    });
}

async function render() {
    headerSection.textContent = titles[route] || 'AutoSpa';
    setActiveNav();

    if (route === 'pos') await renderPos();
    if (route === 'receipt') await renderReceipt();
    if (route === 'live') await renderLiveBoard();
    if (route === 'job-create') await renderJobCreate();
    if (route === 'check-in') await renderCheckIn();
    if (route === 'finance') await renderFinance();
}

async function boot() {
    if (!api) {
        view.innerHTML = `<div class="asp-panel"><p class="text-slate-400">Open this screen from the AutoSpa Pro desktop app.</p></div>`;
        return;
    }

    setActiveNav();
    await refreshSyncPill();
    await render();
    setInterval(refreshSyncPill, 5000);
}

boot();
