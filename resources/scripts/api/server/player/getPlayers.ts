export interface Player {
    id: number;
    authid: string;
    nickname: string;
    ping: number;
    admin: boolean;
    role: string;
    muted: boolean;
}

export interface PlayersData {
    message: string;
    players: Player[];
    online_players: number;
    max_players: number;
}

export interface ApiResponse<T> {
    success: boolean;
    data: T;
}

import http from '@/api/http';

export default async (uuid: string): Promise<ApiResponse<PlayersData>> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/player-manager?action=GetPlayers`)
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
