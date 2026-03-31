import getDirectory from "./getDirectory";

const getInstallationLocations = (port: string, includeExiled: boolean, isLabApi: boolean): { name: string, location: string }[] => {
    const values: { name: string, location: string }[] = [];

    if (isLabApi) {
        if (includeExiled)
        {
            values.push({name: 'LabAPI -> Plugins -> Global', location: getDirectory(port, 'labapi', false, false)});
            values.push({name: 'LabAPI -> Plugins -> Port', location: getDirectory(port, 'labapi', false, true)});
            values.push({name: 'LabAPI -> Dependencies -> Global', location: getDirectory(port, 'labapi', true, false)});
            values.push({name: 'LabAPI -> Dependencies -> Port', location: getDirectory(port, 'labapi', true, true)});
        }
        else
        {
            values.push({name: 'Plugins -> Global', location: getDirectory(port, 'labapi', false, false)});
            values.push({name: 'Plugins -> Port', location: getDirectory(port, 'labapi', false, true)});
            values.push({name: 'Dependencies -> Global', location: getDirectory(port, 'labapi', true, false)});
            values.push({name: 'Dependencies -> Port', location: getDirectory(port, 'labapi', true, true)});
        }
    }

    if (includeExiled) {
        values.push({name: 'EXILED -> Plugins -> Global', location: getDirectory(port, 'exiled', false, false)});
        values.push({name: 'EXILED -> Plugins -> Port', location: getDirectory(port, 'exiled', false, true)});
        values.push({name: 'EXILED -> Dependencies', location: getDirectory(port, 'exiled', true, false)});
    }

    return values;
}

export default getInstallationLocations;