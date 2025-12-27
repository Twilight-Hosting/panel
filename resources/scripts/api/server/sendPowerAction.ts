import http from '@/api/http';

export default (uuid: string, action: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/power?signal=${action}`)
            .then(() => resolve())
            .catch(reject);
    });
};
