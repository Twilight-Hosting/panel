import http from '@/api/http';

const getExiledInstalled = ({ id }: { id: string}): Promise<boolean> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/${id}/plugin-sl/has-exiled`)
            .then(({ data }) => {
                resolve(data.HasExiled);
            })
            .catch(reject)
    })
};

export default getExiledInstalled;