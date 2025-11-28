import http, { getPaginationSet, PaginatedResult } from '@/api/http';
import { ExternalPlugin, QueryParams } from '@/api/server/plugins-sl/Plugins';

const getPlugins = ({ id, ...params }: QueryParams & { id: string }): Promise<PaginatedResult<ExternalPlugin>> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/${id}/plugin-sl/installable`, {
            params: {
                ...params,
            },
        })
            .then(({ data }) => {
                resolve({
                    items: data?.data || [],
                    pagination: getPaginationSet(data?.meta || {}),
                });
            })
            .catch(reject);
    });
};

export default getPlugins;