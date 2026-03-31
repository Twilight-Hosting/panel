import http from '@/api/http';
import { InstalledPlugin } from '@/api/server/plugins-sl/Plugins';

const getInstalledPlugins = ({ id }: { id: string }): Promise<InstalledPlugin[]> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/${id}/plugin-sl/installed`)
            .then(({ data }) => {
                resolve(data);
            })
            .catch(reject);
    });
};

export default getInstalledPlugins;