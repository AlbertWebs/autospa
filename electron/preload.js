import { contextBridge, ipcRenderer } from 'electron';

contextBridge.exposeInMainWorld('autoSpaDesktop', {
    runtime: 'electron',
    get remoteSyncUrl() {
        return ipcRenderer.sendSync('desktop:get-remote-sync-url');
    },
    async checkRemoteReachable() {
        return ipcRenderer.invoke('desktop:check-remote-reachable');
    },
});
