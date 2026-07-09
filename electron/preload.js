import { contextBridge } from 'electron';

contextBridge.exposeInMainWorld('autoSpaDesktop', {
    runtime: 'electron',
});
