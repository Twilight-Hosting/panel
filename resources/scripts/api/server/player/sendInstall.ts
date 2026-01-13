import http from '@/api/http';

export default (uuid: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/player-manager/update?action=install`)
            .then(() => resolve())
            .catch(reject);
    });
};
