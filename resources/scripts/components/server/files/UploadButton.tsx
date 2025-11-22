import axios from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import React, { useEffect, useRef } from 'react';
import { ModalMask } from '@/components/elements/Modal';
import Fade from '@/components/elements/Fade';
import useEventListener from '@/plugins/useEventListener';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { ServerContext } from '@/state/server';
import { WithClassname } from '@/components/types';
import Portal from '@/components/elements/Portal';
import { CloudUploadIcon } from '@heroicons/react/outline';
import { useSignal } from '@preact/signals-react';
import createDirectory from '@/api/server/files/createDirectory';

function isFileOrDirectory(event: DragEvent): boolean {
    if (!event.dataTransfer?.types) {
        return false;
    }

    return event.dataTransfer.types.some((value) => value.toLowerCase() === 'files');
}

async function fallbackDirectoryPicker(): Promise<FileList | null> {
    const input = document.createElement('input');
    input.type = 'file';
    input.webkitdirectory = true;
    input.multiple = true;
    input.style.position = 'fixed';
    input.style.top = '-100000px';
    input.style.left = '-100000px';
    document.body.appendChild(input);
    return new Promise((resolve) => {
        input.addEventListener('change', () => {
            resolve(input.files);
            document.body.removeChild(input);
        });
        input.click();
    });
}

export default ({ className }: WithClassname) => {
    const fileUploadInput = useRef<HTMLInputElement>(null);

    const visible = useSignal(false);
    const timeouts = useSignal<NodeJS.Timeout[]>([]);

    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError } = useFlashKey('files');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { clearFileUploads, removeFileUpload, pushFileUpload, setUploadProgress } = ServerContext.useStoreActions(
        (actions) => actions.files
    );

    useEventListener(
        'dragenter',
        (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (isFileOrDirectory(e)) {
                visible.value = true;
            }
        },
        { capture: true }
    );

    useEventListener('dragexit', () => (visible.value = false), { capture: true });

    useEventListener('keydown', () => (visible.value = false));

    useEffect(() => {
        return () => timeouts.value.forEach(clearTimeout);
    }, []);

    const onUploadProgress = (data: ProgressEvent, name: string) => {
        setUploadProgress({ name, loaded: data.loaded });
    };

    const onFileSubmission = (list: any[]) => {
        clearAndAddHttpError();

        const uploads = list.map((file) => {
            const controller = new AbortController();

            if (!file.file) {
                pushFileUpload({
                    name: `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name
                        .split('/')
                        .slice(0, -1)
                        .join('/')}/${file.name.split('/').pop()}`,
                    data: { abort: controller, loaded: 0, total: file.size },
                });
                return () =>
                    createDirectory(
                        uuid,
                        `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name
                            .split('/')
                            .slice(0, -1)
                            .join('/')}`,
                        file.name.split('/').pop()
                    ).then(() =>
                        timeouts.value.push(
                            setTimeout(
                                () =>
                                    removeFileUpload(
                                        `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name
                                            .split('/')
                                            .slice(0, -1)
                                            .join('/')}/${file.name.split('/').pop()}`
                                    ),
                                500
                            )
                        )
                    );
            }

            pushFileUpload({
                name: `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name ? `${file.name}/` : ""}${file.file.name}`,
                data: { abort: controller, loaded: 0, total: file.file.size },
            });

            var fileForm = new FormData;
            fileForm.append("files", file.file, file.file.name);

            return () =>
                getFileUploadUrl(uuid).then((url) =>
                    axios
                        .post(
                            url,
                            fileForm,
                            {
                                signal: controller.signal,
                                headers: { 'Content-Type': 'multipart/form-data' },
                                params: {
                                    directory: `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${
                                        file.name
                                    }`,
                                },
                                onUploadProgress: (data) =>
                                    onUploadProgress(
                                        data,
                                        `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name ? `${file.name}/` : ""}${
                                            file.file.name
                                        }`
                                    ),
                            }
                        )
                        .then(() =>
                            timeouts.value.push(
                                setTimeout(
                                    () =>
                                        removeFileUpload(
                                            `${directory.endsWith('/') ? directory.slice(0, -1) : directory}/${file.name ? `${file.name}/` : ""}${file.file.name}`
                                        ),
                                    500
                                )
                            )
                        )
                );
        });

        Promise.all(uploads.map((fn) => fn()))
            .then(() => mutate())
            .catch((error) => {
                clearFileUploads();
                clearAndAddHttpError(error);
            });
    };

    const handleEntry = async function (files: any[], prefix: string, entry: FileSystemHandle) {
        if (entry.kind === 'directory') {
            files.push({
                name: `${prefix}${entry.name}`,
            });
            // @ts-ignore
            for await (const entry2 of entry.values()) {
                await handleEntry(files, `${prefix}${entry.name}/`, entry2);
            }
        } else {
            files.push({
                name: prefix.slice(0, -1),
                // @ts-ignore
                file: await entry.getFile(),
            });
        }
    };

    const uploadDirectory = async () => {
        // @ts-ignore
        if (typeof window.showDirectoryPicker === 'function') {
            // @ts-ignore
            const result = await window.showDirectoryPicker();
            const files: any[] = [];

            await handleEntry(files, '', result);

            onFileSubmission(files);
        } else {
            // @ts-ignore
            const result = Array.from(await fallbackDirectoryPicker());
            const files: any[] = [];
            const exists: any = {};

            for (const file of result) {
                const parts = file.webkitRelativePath.split('/').slice(0, -1);
                const current = exists;
                let currentPath = '';
                while (parts.length) {
                    const part = parts.shift();
                    // @ts-ignore
                    if (!current[part]) {
                        // @ts-ignore
                        current[part] = {};
                        currentPath += `${part}/`;
                        files.push({
                            name: currentPath.slice(0, -1),
                        });
                    }
                }
                files.push({
                    name: file.webkitRelativePath.split('/').slice(0, -1).join('/'),
                    file,
                });
            }

            onFileSubmission(files);
        }
    };

    return (
        <>
            <Portal>
                <Fade appear in={visible.value} timeout={75} key={'upload_modal_mask'} unmountOnExit>
                    <ModalMask
                        onClick={() => (visible.value = false)}
                        onDragOver={(e) => e.preventDefault()}
                        onDrop={async (e) => {
                            e.preventDefault();
                            e.stopPropagation();

                            visible.value = false;
                            if (!e.dataTransfer?.files.length) return;

                            async function mapperFunc(
                                prefix: string,
                                item: FileSystemFileEntry | FileSystemDirectoryEntry
                            ): Promise<any> {
                                if (item.isFile) {
                                    return new Promise((res) =>
                                        // @ts-ignore
                                        item.file((result) =>
                                            res({
                                                name: prefix.slice(0, -1),
                                                file: result,
                                            })
                                        )
                                    );
                                } else {
                                    const entries: any[] = await new Promise((res) =>
                                        // @ts-ignore
                                        item.createReader().readEntries(res)
                                    );
                                    return [
                                        {
                                            name: `${prefix}${item.name}`,
                                        },
                                        ...(
                                            await Promise.all(
                                                entries.map((item2) => mapperFunc(`${prefix}${item.name}/`, item2))
                                            )
                                        // @ts-ignore
                                        ).flat(),
                                    ];
                                }
                            }

                            onFileSubmission(
                                (
                                    await Promise.all(
                                        Array.from(e.dataTransfer.items)
                                            .map((item) => item.webkitGetAsEntry())
                                            // @ts-ignore
                                            .map((item) => mapperFunc('', item))
                                    )
                                // @ts-ignore
                                ).flat()
                            );
                        }}
                    >
                        <div className={'w-full flex items-center justify-center pointer-events-none'}>
                            <div
                                className={
                                    'flex items-center space-x-4 bg-black w-full ring-4 ring-blue-200 ring-opacity-60 rounded p-6 mx-10 max-w-sm'
                                }
                            >
                                <CloudUploadIcon className={'w-10 h-10 flex-shrink-0'} />
                                <p className={'font-header flex-1 text-lg text-neutral-100 text-center'}>
                                    Drag and drop files to upload.
                                </p>
                            </div>
                        </div>
                    </ModalMask>
                </Fade>
            </Portal>
            <input
                type={'file'}
                ref={fileUploadInput}
                css={tw`hidden`}
                onChange={(e) => {
                    if (!e.currentTarget.files) return;

                    onFileSubmission(
                        Array.from(e.currentTarget.files).map((file) => ({
                            name: '',
                            file,
                        }))
                    );

                    if (fileUploadInput.current) {
                        fileUploadInput.current.files = null;
                    }
                }}
                multiple
            />
            <Button className={className} onClick={() => fileUploadInput.current && fileUploadInput.current.click()}>
                Upload
            </Button>
            <Button className={className} onClick={uploadDirectory}>
                Upload Directory
            </Button>
        </>
    );
};
