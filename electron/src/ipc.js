import { ipcMain } from 'electron';
import {
    checkoutPosLocal,
    createCustomerLocal,
    createExpenseLocal,
    createJobCardLocal,
    createVehicleLocal,
    dashboardStats,
    financeOverview,
    listCustomers,
    listExpenses,
    listJobCards,
    listPaymentMethods,
    listProducts,
    listSales,
    listServices,
    listVehicles,
    pendingCount,
    updateJobCardStatusLocal,
} from './db.js';
import {
    checkRemoteSession,
    cloudStatusSnapshot,
    isRemoteReachable,
    pullBootstrap,
    syncNow,
} from './sync.js';

export function registerIpcHandlers({ openCloudLogin, goOnline, goOffline }) {
    ipcMain.handle('desktop:dashboard', () => dashboardStats());
    ipcMain.handle('desktop:customers:list', (_e, search) => listCustomers(search ?? ''));
    ipcMain.handle('desktop:customers:create', (_e, input) => createCustomerLocal(input));
    ipcMain.handle('desktop:vehicles:list', (_e, customerUuid) => listVehicles(customerUuid ?? null));
    ipcMain.handle('desktop:vehicles:create', (_e, input) => createVehicleLocal(input));
    ipcMain.handle('desktop:jobs:list', () => listJobCards());
    ipcMain.handle('desktop:jobs:create', (_e, input) => createJobCardLocal(input));
    ipcMain.handle('desktop:jobs:update-status', (_e, input) => updateJobCardStatusLocal(input));
    ipcMain.handle('desktop:catalog:services', () => listServices());
    ipcMain.handle('desktop:catalog:products', () => listProducts());
    ipcMain.handle('desktop:catalog:payment-methods', () => listPaymentMethods());
    ipcMain.handle('desktop:sales:list', () => listSales());
    ipcMain.handle('desktop:pos:checkout', (_e, input) => checkoutPosLocal(input));
    ipcMain.handle('desktop:finance:overview', (_e, range) => financeOverview(range?.from ?? null, range?.to ?? null));
    ipcMain.handle('desktop:finance:expenses:list', (_e, range) => listExpenses(range?.from ?? null, range?.to ?? null));
    ipcMain.handle('desktop:finance:expenses:create', (_e, input) => createExpenseLocal(input));
    ipcMain.handle('desktop:sync:status', () => cloudStatusSnapshot());
    ipcMain.handle('desktop:sync:now', () => syncNow());
    ipcMain.handle('desktop:sync:bootstrap', () => pullBootstrap());
    ipcMain.handle('desktop:sync:check', () => checkRemoteSession());
    ipcMain.handle('desktop:sync:reachable', () => isRemoteReachable());
    ipcMain.handle('desktop:sync:pending', () => pendingCount());
    ipcMain.handle('desktop:cloud:login', async () => {
        await openCloudLogin();

        return checkRemoteSession();
    });
    ipcMain.handle('desktop:ui:go-online', async () => {
        if (typeof goOnline === 'function') {
            await goOnline();
        }

        return { ok: true };
    });
    ipcMain.handle('desktop:ui:go-offline', async () => {
        if (typeof goOffline === 'function') {
            await goOffline();
        }

        return { ok: true };
    });
}
