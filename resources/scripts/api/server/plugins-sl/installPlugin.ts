import http from '@/api/http';
import pullFile from '@/api/server/files/pullFile';
import { ExternalAsset, ExternalRelease, InstalledPlugin } from '@/api/server/plugins-sl/Plugins';
import getDirectory from '@/api/server/plugins-sl/getDirectory';

export interface InstallProps {
    id: string, 
    uuid: string, 
    framework: string, 
    plugin_id: string, 
    plugin_name: string, 
    plugin_icon: string, 
    release: ExternalRelease, 
    assets: ExternalAsset[]
}

const installPlugin = async ({ 
    id, 
    uuid, 
    framework, 
    plugin_id, 
    plugin_name, 
    plugin_icon, 
    release,
    assets
}: InstallProps ): Promise<InstalledPlugin> => {

    const FormatFile = (plugin_id: string, asset: string): string =>
    {
        return `${plugin_id}-${asset}`;
    };

    try {
        var fileNames: string[] = [];
        for (const asset of assets)
        {
            var downloadUrl = asset.downloadUrl;
            const formattedFile = FormatFile(plugin_id, asset.name);
            fileNames = fileNames.concat(formattedFile);

            const dir = getDirectory(framework);
            await pullFile(uuid, downloadUrl, dir, formattedFile);
        }
    } catch (error) {
        throw new Error(`Plugin installation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }

    return new Promise((resolve, reject) => {
        http.post(`/api/client/${id}/plugin-sl/install`, {
            plugin_icon,
            plugin_name,
            plugin_framework: framework,
            plugin_version: release.name,
            plugin_id: plugin_id,
            file_names: fileNames
        })
        .then(({ data }) => resolve(data))
        .catch(reject);
    });
};

export default installPlugin;