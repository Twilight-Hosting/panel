import http from '@/api/http';

export default (uuid: string, action: string, command: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/player-manager?action=${action}&${command}`)
            .then(() => resolve())
            .catch(reject);
    });
};
