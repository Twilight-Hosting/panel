const getDirectory = (port: string, framework: string, dependencies: boolean, portSpecific: boolean): string => {

    if (framework === 'labapi')
    {
        if (dependencies)
        {
            return '/.config/SCP Secret Laboratory/LabAPI/dependencies/' + (portSpecific ? port : 'global')
        }
        else
        {
            return '/.config/SCP Secret Laboratory/LabAPI/plugins/' + (portSpecific ? port : 'global')
        }
    }
    else if (framework === 'exiled')
    {
        if (dependencies)
        {
            return '/.config/EXILED/Plugins/dependencies'
        }
        else
        {
            return '/.config/EXILED/Plugins' + (portSpecific ? ('/' + port) : '')
        }
    }

    console.error(`Uhhhhhh how? Provided framework: ${framework === `` ? `Empty` : framework}`);
    return '';
}

export default getDirectory;