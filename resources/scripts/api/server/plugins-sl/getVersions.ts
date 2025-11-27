import http from '@/api/http';
import { Versions } from '@/api/server/plugins-sl/Plugins';

const getVersions = ({ id, service, pluginId }: { id: string, service: string, pluginId: string  }): Promise<Versions> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/${id}/plugin/installable/${pluginId}`)
            .then(({ data }) =>
                resolve({
                    versions: data.versions
                })
            )
            .catch(reject);
    });
};

export default getVersions;