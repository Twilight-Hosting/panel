export interface AssetParams {
    name: string,
    downloadUrl: string,

    downloadLocation: string,
    downloadAction: DownloadAction,
}

export enum DownloadAction {
    None = 0,
    Extract = 1,
}

const getPossibleActions = (name: string): DownloadAction[] => {
    const actions: DownloadAction[] = [DownloadAction.None];

    // TODO: make this better lol
    if (name.endsWith(".zip") || name.endsWith(".tar.gz")) {
        actions.push(DownloadAction.Extract)
    }

    return actions
}

export default getPossibleActions