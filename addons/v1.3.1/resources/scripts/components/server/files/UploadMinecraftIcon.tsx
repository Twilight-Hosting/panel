import axios from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import React, { useRef } from 'react';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { ServerContext } from '@/state/server';
import { WithClassname } from '@/components/types';
import { useTranslation } from 'react-i18next';

export default ({ className }: WithClassname) => {
    const { t } = useTranslation('arix/server/addons/files');
    const fileUploadInput = useRef<HTMLInputElement>(null);
    const { mutate } = useFileManagerSwr();
    const { addError, clearAndAddHttpError } = useFlashKey('files');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFileUploads, pushFileUpload, removeFileUpload, setUploadProgress } = ServerContext.useStoreActions(
        (actions) => actions.files
    );

    const onUploadProgress = (data: ProgressEvent, name: string) => {
        setUploadProgress({ name, loaded: data.loaded });
    };

    const processAndUploadImage = async (file: File) => {
        if (!file.type.startsWith('image/')) {
            return addError('Only image files are allowed.', 'Error');
        }

        const imageBitmap = await createImageBitmap(file);
        const canvas = document.createElement('canvas');
        canvas.width = 64;
        canvas.height = 64;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.drawImage(imageBitmap, 0, 0, 64, 64);

        canvas.toBlob(async (blob) => {
            if (!blob) return;
            const renamedFile = new File([blob], 'server-icon.png', { type: 'image/png' });
            
            const controller = new AbortController();
            pushFileUpload({
                name: renamedFile.name,
                data: { abort: controller, loaded: 0, total: renamedFile.size },
            });

            try {
                const url = await getFileUploadUrl(uuid);
                await axios.post(
                    url,
                    { files: renamedFile },
                    {
                        signal: controller.signal,
                        headers: { 'Content-Type': 'multipart/form-data' },
                        params: { directory: '/' },
                        onUploadProgress: (data) => onUploadProgress(data, renamedFile.name),
                    }
                );
                removeFileUpload(renamedFile.name);
                mutate();
            } catch (error) {
                clearFileUploads();
                clearAndAddHttpError(error instanceof Error ? error.message : String(error));
            }
        }, 'image/png');
    };

    return (
        <>
            <input
                type={'file'}
                accept="image/*"
                ref={fileUploadInput}
                css={tw`hidden`}
                onChange={(e) => {
                    if (!e.currentTarget.files) return;
                    processAndUploadImage(e.currentTarget.files[0]);
                    if (fileUploadInput.current) {
                        fileUploadInput.current.value = '';
                    }
                }}
            />
            <Button.Text className={className} onClick={() => fileUploadInput.current && fileUploadInput.current.click()}>
                {t('upload-minecraft-icon')}
            </Button.Text>
        </>
    );
};