import pullFile from '@/api/server/files/pullFile';
import loadDirectory from '@/api/server/files/loadDirectory';
import deleteFiles from '@/api/server/files/deleteFiles';
import http from '@/api/http';

interface VersionRequest {
    uuid: string;
    id: string;
    engine: string;
    version: string;
    download: string;
    uninstall: boolean;
}

const setVersion = async ({ uuid, id, engine, version, download, uninstall }: VersionRequest): Promise<any> => {
    try {
        console.log('[DEBUG] VersionRequest received:', { uuid, id, engine, version, download, uninstall });

        if (uninstall) {
            const files = await loadDirectory(uuid, '/');
            const fileNames = files
                .filter(file => file.name !== 'server.properties')
                .map(file => file.name);

            if (fileNames.length > 0) {
                console.log('[DEBUG] Deleting existing files:', fileNames);
                await deleteFiles(uuid, '/', fileNames);
            }
        }

        // Case-insensitive detection
        const isForge = engine?.toLowerCase() === 'forge';
        // Use a distinctive name for Forge installer jars
        const targetName = isForge ? 'forge-installer.jar' : 'server.jar';

        console.log(`[DEBUG] Engine detected: ${engine}`);
        console.log(`[DEBUG] Downloading from: https://arix.gg/arix-api/v1${download}`);
        console.log(`[DEBUG] Target filename: ${targetName}`);

        await pullFile(
            uuid,
            `https://arix.gg/arix-api/v1${download}`,
            '/',
            targetName,
        );

        console.log(`[DEBUG] File successfully pulled and saved as ${targetName}`);

        const response = await http.post(`/api/client/${id}/versions/${engine}/${version}`);
        console.log('[DEBUG] Version install response:', response.data);

        return response.data;
    } catch (error) {
        console.error('[ERROR] Version installation failed:', error);
        throw new Error(`Version installation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
};



export default setVersion;