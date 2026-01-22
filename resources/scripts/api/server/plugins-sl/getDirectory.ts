const getDirectory = (framework: string): string => {
    // TODO: update
    if (framework === 'labapi')
        return '/.config/SCP Secret Laboratory/LabAPI/plugins/global'
    console.error(`Uhhhhhh how? Provided framework: ${framework === `` ? `Empty` : framework}`);
    return '';
}

export default getDirectory