import { action, Action } from 'easy-peasy';
import { InstalledPlugin } from '@/api/server/plugins-sl/Plugins';

export interface SLServerPluginsStore {
    data: InstalledPlugin[]; 
    setPlugins: Action<SLServerPluginsStore, InstalledPlugin[]>; 
    appendPlugin: Action<SLServerPluginsStore, InstalledPlugin>; 
    removePlugin: Action<SLServerPluginsStore, number>;
}

const slPlugins: SLServerPluginsStore = {
    data: [],

    setPlugins: action((state, payload) => {
        state.data = payload;
    }),

    appendPlugin: action((state, payload) => {
        const exists = state.data.find(plugin => plugin.id === payload.id);
        if (!exists) {
            state.data.push(payload); 
        }
    }),

    removePlugin: action((state, payload) => {
        state.data = state.data.filter(plugin => plugin.id !== payload);
    }),
};

export default slPlugins;