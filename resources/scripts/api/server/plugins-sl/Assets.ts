export interface AssetParams {
    name: string,
    downloadUrl: string,

    downloadLocation: string,
    downloadAction: DownloadAction,
}

export const DownloadAction = {
    None: 'none',
    Extract: 'extract'
} as const

const getPossibleActions = (name: string): DownloadAction[] => {
    const actions: DownloadAction[] = [];

    // TODO: make this better lol
    if (name.endsWith(".zip") || name.endsWith(".tar.gz")) {
        actions.push(DownloadAction.Extract)
    }

    return actions
}

export default getPossibleActions

export type DownloadAction = typeof DownloadAction[keyof typeof DownloadAction]