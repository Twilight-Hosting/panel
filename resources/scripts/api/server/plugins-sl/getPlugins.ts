import http, { getPaginationSet, PaginatedResult } from '@/api/http';
import { Plugin, QueryParams } from '@/api/server/plugins-sl/Plugins';

const getPlugins = ({ id, ...params }: QueryParams & { id: string }): Promise<PaginatedResult<Plugin>> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/${id}/plugin-sl/installable`, {
            params: {
                ...params,
            },
        })
            .then(({ data }) =>
                resolve({
                    items: data.data,
                    pagination: getPaginationSet(data.meta.pagination),
                })
            )
            .catch(reject);
    });
};

export default getPlugins;