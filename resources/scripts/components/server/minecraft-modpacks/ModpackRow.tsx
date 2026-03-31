import http from '@/api/http';
import { Modpack, ModpackProvider } from '@/api/swr/getMinecraftModpacks';
import { Dialog } from '@/components/elements/dialog';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import Switch from '@/components/elements/Switch';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';
import { faDownload, faExternalLinkAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import React, { useEffect, useState } from 'react';
import tw from 'twin.macro';

interface Props {
    provider: ModpackProvider;
    modpack: Modpack;
    className?: string;
}

interface ModpackVersion {
    id: string;
    name: string;
}

const ModpackCard = ({ provider, modpack, className }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const [installDialogVisible, setInstallDialogVisible] = useState(false);
    const [confirmDialogVisible, setConfirmDialogVisible] = useState(false);

    const [versions, setVersions] = useState<ModpackVersion[]>([]);
    const [selectedVersion, setSelectedVersion] = useState<string | null>(null);
    const [deleteServerFiles, setDeleteServerFiles] = useState(false);

    const { clearAndAddHttpError } = useFlash();

    // Actual install, now only triggered from the 2nd popup.
    const installModpack = () => {
        if (!selectedVersion) {
            return;
        }

        http.post(`/api/client/servers/${uuid}/minecraft-modpacks/install`, {
            provider,
            modpack_id: modpack.id,
            modpack_version_id: selectedVersion,
            delete_server_files: deleteServerFiles,
        })
            .then(() => {
                // Close the second dialog. The external watcher will handle reload.
                setConfirmDialogVisible(false);
            })
            .catch((error) => {
                setConfirmDialogVisible(false);
                clearAndAddHttpError({ error, key: 'modpacks' });
            });
    };

    // When first dialog opens, load versions once.
    useEffect(() => {
        if (installDialogVisible && !versions.length) {
            http.get(`/api/client/servers/${uuid}/minecraft-modpacks/versions`, {
                params: {
                    provider,
                    modpack_id: modpack.id,
                },
            })
                .then((response) => {
                    const data: ModpackVersion[] = response.data || [];
                    setVersions(data);
                    if (data[0]) {
                        setSelectedVersion(data[0].id);
                    }
                })
                .catch((error) => {
                    clearAndAddHttpError({ error, key: 'modpacks' });
                    setInstallDialogVisible(false);
                });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [installDialogVisible]);

    // When user confirms the first dialog: just move to the second.
    const handleInitialConfirm = () => {
        if (!selectedVersion) {
            return;
        }

        setInstallDialogVisible(false);
        setConfirmDialogVisible(true);
    };

    return (
        <>
            {/* Step 1: Version & options dialog */}
            <Dialog.Confirm
                title={'Install modpack'}
                confirm={'Continue'}
                open={installDialogVisible}
                onClose={() => setInstallDialogVisible(false)}
                onConfirmed={handleInitialConfirm}
            >
                <p>
                    You requested the installation of the modpack &quot;{modpack.name}&quot; from the {provider}{' '}
                    provider. Please select the desired modpack version below.
                </p>

                <Label className={'mt-3'} htmlFor='modpack_version_id'>
                    Modpack version
                </Label>

                <Select
                    name='modpack_version_id'
                    value={selectedVersion ?? ''}
                    onChange={(event) => setSelectedVersion(event.target.value)}
                >
                    {versions.map((version) => (
                        <option key={version.id} value={version.id}>
                            {version.name}
                        </option>
                    ))}
                </Select>

                <p css={tw`mt-3`}>
                    Please note that modpack updates can cause world corruption. You are strongly advised to make a
                    backup before updating a modpack.
                </p>

                <div css={tw`mt-6 bg-neutral-700 p-4 rounded`}>
                    <Switch
                        defaultChecked={deleteServerFiles}
                        onChange={() => setDeleteServerFiles((s) => !s)}
                        name='delete_files'
                        label='Delete files'
                        description='Delete all your server files before installing the modpack. This is irreversible!'
                    />
                </div>
            </Dialog.Confirm>

            {/* Step 2: Final "Got it" dialog that actually triggers install */}
            <Dialog.Confirm
                title={'Modpack installation'}
                confirm={'Got it'}
                open={confirmDialogVisible}
                onClose={() => setConfirmDialogVisible(false)}
                onConfirmed={installModpack}
            >
                <p>
                    Your selected options will now be applied:
                </p>
                <ul css={tw`list-disc list-inside mt-2 text-sm`}>
                    <li>Modpack: {modpack.name}</li>
                    {selectedVersion && (
                        <li>
                            Version:{' '}
                            {versions.find((v) => v.id === selectedVersion)?.name ?? selectedVersion}
                        </li>
                    )}
                    <li>
                        Delete existing server files:{' '}
                        {deleteServerFiles ? 'Yes, delete all files.' : 'No, keep existing files.'}
                    </li>
                </ul>
                <p css={tw`mt-3 text-sm text-neutral-300 text-center`}>
                    When you click <strong>Got it</strong>, the modpack installation process will start. Shortly after,
                    the panel may reload automatically once the server status updates.<br />

		    Be sure to go to the "Startup" tab on the panel, and select the correct Java version (in the "Docker Image" drop-down menu) 
		    for the version of Minecraft you have installed.<br /><br />

			As a general rule (may differ for some modpacks):<br />
			    Minecraft 1.0 - 1.11: Java 8<br />
			    Minecraft 1.12 - 1.16.5: Java 11<br />
			    Minecraft 1.17: Java 16<br />
			    Minecraft 1.18 - 1.20.4: Java 17<br />
			    Minecraft 1.20.5+: Java 21<br />
                </p>
            </Dialog.Confirm>

            {/* Modpack list row */}
            <GreyRowBox className={className} css={tw`flex items-center`}>
                <img
                    src={modpack.iconUrl ?? 'https://placehold.co/32'}
                    css={tw`rounded-md w-8 h-8 sm:w-12 sm:h-12 object-contain flex items-center justify-center`}
                    alt={modpack.name}
                />
                <div css={tw`flex flex-col ml-3 w-9/12`}>
                    {modpack.url ? (
                        <a
                            css={tw`hover:text-gray-400`}
                            href={modpack.url}
                            rel='noreferrer'
                            target='_blank'
                        >
                            {modpack.name}
                            <FontAwesomeIcon icon={faExternalLinkAlt} css={tw`ml-1 h-3 w-3`} />
                        </a>
                    ) : (
                        <p>{modpack.name}</p>
                    )}
                    <p css={tw`hidden lg:block text-neutral-300 truncate`}>{modpack.description}</p>
                </div>
                <button
                    title='Install'
                    css={tw`ml-auto text-sm text-neutral-400 hover:text-green-400 transition-colors duration-150`}
                    onClick={() => setInstallDialogVisible(true)}
                >
                    <FontAwesomeIcon icon={faDownload} />
                </button>
            </GreyRowBox>
        </>
    );
};

export default ModpackCard;

