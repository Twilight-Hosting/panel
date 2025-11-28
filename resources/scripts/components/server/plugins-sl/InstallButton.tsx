import React, { useState, useEffect } from 'react';
import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import { ExternalPlugin, InstalledPlugin, ExternalRelease } from '@/api/server/plugins-sl/Plugins';
import installPlugin from '@/api/server/plugins-sl/installPlugin';
import { ServerContext } from '@/state/server';
import { Button } from '@/components/elements/button/index';
import { Actions, useStoreActions } from 'easy-peasy';
import { numify } from "numify";
import Spinner from '@/components/elements/Spinner';
import { differenceInCalendarMonths, format, formatDistanceToNow } from 'date-fns';
import { ExternalLinkIcon, DownloadIcon, CalendarIcon, ViewGridAddIcon, ChevronUpIcon } from '@heroicons/react/outline';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import { ApplicationStore } from '@/state';
import { useTranslation } from 'react-i18next';
import * as locales from 'date-fns/locale';
import { isEmptyArray } from 'formik';
import { bytesToString } from '@/lib/formatters';
import tw from 'twin.macro';
import styled, { css } from 'styled-components';

const getLocale = (localeKey: keyof typeof locales) => {
    if (locales[localeKey]) {
        return locales[localeKey];
    } else {
        const keyString = String(localeKey);
        console.warn(`Locale '${keyString}' not found. Falling back to '${locales.enUS}'`);
        return locales.enUS;
    }
};

interface Props {
    hideDropdownArrow?: boolean;
}

const Select = styled.select<Props>`
    ${tw`shadow-none block p-3 pr-8 rounded-component w-full text-sm transition-colors duration-150 ease-linear`};
    border: var(--borderInput);

    &,
    &:hover:not(:disabled),
    &:focus {
        ${tw`outline-none`};
    }

    -webkit-appearance: none;
    -moz-appearance: none;
    background-size: 1rem;
    background-repeat: no-repeat;
    background-position-x: calc(100% - 0.75rem);
    background-position-y: center;

    &::-ms-expand {
        display: none;
    }

    ${(props) =>
        !props.hideDropdownArrow &&
        css`
            ${tw`bg-neutral-700 border-neutral-800 text-neutral-200`};
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='%23C3D1DF' d='M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z'/%3e%3c/svg%3e ");

            &:hover:not(:disabled),
            &:focus {
                ${tw`!border-neutral-400`};
            }
        `};
`;

export interface CheckboxProps {
    id: string;
    name?: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    disabled?: boolean;
    className?: string;
    children?: React.ReactNode;
}

const CheckboxContainer = styled.label`
    ${tw`flex items-center border border-transparent rounded md:p-2 transition-colors duration-75`};
    text-transform: none;

    &:not(.disabled) {
        ${tw`cursor-pointer`};

        ${tw`border-neutral-800 bg-neutral-700`};
        &:hover {
            ${tw`border-neutral-500 bg-neutral-800`};
        }
    }

    &:not(:first-of-type) {
        ${tw`mt-4 sm:mt-2`};
    }

    &.disabled {
        ${tw`opacity-50`};

        & input[type='checkbox']:not(:checked) {
            ${tw`border-0`};
        }
    }
`;

const Checkbox: React.FC<CheckboxProps> = ({
    id,
    name,
    checked,
    onChange,
    disabled = false,
    className = '',
    children
}) => {
    return (
        <CheckboxContainer className={`flex items-start gap-x-3 p-3 rounded-lg border transition-colors cursor-pointer
         ${disabled ? 'opacity-50 cursor-not-allowed' : ''} ${className}`} htmlFor={id}>
            <div className="flex items-center h-5 relative">

                <div className={`w-4 h-4 border-2 rounded flex items-center justify-center transition-all ${
                    checked 
                        ? 'bg-blue-500 border-blue-500 shadow-lg shadow-blue-500/25' 
                        : 'bg-gray-800 border-gray-400 shadow-lg'
                } ${disabled ? 'opacity-50' : ''}`}>
                    {checked && (
                        <svg className="w-3 h-3 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                        </svg>
                    )}
                </div>

                <input
                    type="checkbox"
                    id={id}
                    name={name || id}
                    checked={checked}
                    onChange={(e) => onChange(e.target.checked)}
                    disabled={disabled}
                    className="absolute opacity-0 w-4 h-4 cursor-pointer"
                />
            </div>
            <label
                htmlFor={id}
                className={`flex-1 cursor-pointer ${disabled ? 'text-gray-500 cursor-not-allowed' : 'text-gray-100'}`}
            >
                {children}
            </label>
        </CheckboxContainer>
    );
};

const ModalContent = ({ plugin, visible, onDismissed, ...props }: RequiredModalProps & { plugin: ExternalPlugin; }) => {
    const { t } = useTranslation('arix/server/addons/plugins');
    const { i18n } = useTranslation();
    const currentLang = i18n.language;
    const localeKey = currentLang as keyof typeof locales;
    const [loading, setLoading] = useState(false);
    const [releases, setReleases] = useState<ExternalRelease[]>();
    const [selectedRelease, setSelectedRelease] = useState<ExternalRelease>();
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const appendPlugin = ServerContext.useStoreActions((actions) => actions.slPlugins.appendPlugin);
    const [selectedAssets, setSelectedAssets] = useState<string[]>([]);

    const Error = (message?: string) => {
        onDismissed();
        addFlash({
            type: 'error',
            key: 'plugins',
            message: message ?? t('install.download-not-available'),
        });
    }

    const SetSelectedRelease = (release: ExternalRelease | undefined) => {
        setSelectedRelease(release);
        setSelectedAssets([]);
    }

    const InstallPlugin = async () => {
        try {
            if (selectedRelease && selectedAssets && releases) {
                if (selectedRelease?.htmlUrl) {
                    setLoading(true);
                    installPlugin({
                        id, uuid,
                        framework: plugin.framework,
                        plugin_id: plugin.id,
                        plugin_name: plugin.name,
                        plugin_icon: plugin.icon || '',
                        release: selectedRelease,
                        assets: selectedRelease.assets.filter(asset => selectedAssets.includes(asset.name))
                    })
                    .then((rep: InstalledPlugin) => {
                        appendPlugin(rep);
                        addFlash({
                            type: 'success',
                            key: 'plugins',
                            message: t('install.installed-successfully'),
                        });
                        onDismissed();
                    })
                    .catch((error: string) => {
                        Error(error);
                    })
                    .finally(() => 
                        setLoading(false)
                    )
                } else {
                    Error();
                }
            }
        } catch (error) {
            console.error('InstallPlugin error:', error);
            Error(String(error));
        }
    };

    useEffect(() => {
        if(visible) {
            clearFlashes('plugins');
            setReleases(plugin.releases);
            SetSelectedRelease(plugin.releases[0]);
        }

    }, [id, plugin, plugin.framework, visible]);

    return (
        <Modal visible={visible} onDismissed={onDismissed} {...props}>
            <div className={'flex flex-col items-start gap-3'}>
                <div className={'flex items-center gap-x-5'}>
                    <div className={'p-1 bg-gray-600 rounded-lg overflow-hidden'}>
                    {plugin.icon === null ?
                    <ViewGridAddIcon
                        width={64}
                        height={64}
                        className={'shrink-0'}
                    />
                    :
                    <img 
                        src={`https://plugins.scpslgame.com/api/uploads/${plugin.icon}`}
                        width={64} 
                        height={64} 
                        alt={`${plugin.name.slice(0, 5)} Icon`}
                        className={'shrink-0'}
                    />}
                    </div>
                    <div>
                        <p className={'text-xl font-medium text-gray-50 flex items-center gap-x-2'}>
                            {plugin.name}
                            <a href={`https://github.com/${plugin.repository}`} target={'_blank'}>
                                <ExternalLinkIcon className={'w-6'}/>
                            </a>
                        </p>
                        <div className={'flex gap-5 text-gray-400 text-sm'}>
                            <p>
                                {t('by')}&nbsp;
                                <a href={`https://github.com/${plugin.author.username}`} target={'_blank'} className={'underline'}>
                                    {plugin.author.displayName}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <div className={'flex gap-3'}>
                    <Tooltip content={`${t('downloads')}`} placement={'top'}>
                        <p className={'flex items-center gap-x-1'}>
                            <DownloadIcon className={'w-4 text-arix'} />
                            {numify(plugin.downloads)}
                        </p>
                    </Tooltip>
                    <Tooltip content={`${t('likes')}`} placement={'top'}>
                        <p className={'flex items-center gap-x-1'}>
                            <ChevronUpIcon className={'w-4 text-arix'} />
                            {numify(plugin.upvotes)}
                        </p>
                    </Tooltip>
                    <Tooltip content={`${t('last-updated')}`} placement={'top'}>
                        <p className={'flex items-center gap-x-1'}>
                            <CalendarIcon className={'w-4 text-arix'} />
                            {plugin.repoUpdatedAt
                                ? (() => {
                                    const lastUpdated = new Date(plugin.repoUpdatedAt);
                                    const monthsDifference = Math.abs(differenceInCalendarMonths(lastUpdated, new Date()));
                                    return monthsDifference > 12
                                        ? format(lastUpdated, 'MMM do, yyyy', { locale: getLocale(localeKey) })
                                        : formatDistanceToNow(lastUpdated, { addSuffix: true, locale: getLocale(localeKey) });
                                    })()
                                : 'Unknown'
                            }
                        </p>
                    </Tooltip>
                </div>
                <p>
                    {plugin.description}
                </p>
            </div>
            <div>
                <div className="space-y-2">
                    <p className={'mb-1 mt-4'}>{t('install.select-a-version')}:</p>
                        <Select onChange={e => SetSelectedRelease(releases?.find((release) => release.name === e.target.value))}>
                            {releases?.map((release) => (
                                <option value={release.name} key={release.name}>
                                    {release.name}
                                </option>
                            ))}
                        </Select>
                    {
                        selectedRelease
                        ?
                            <div className={'space-y-1'}>
                                {selectedRelease.assets.map((asset) => (
                                        <Checkbox
                                            key={asset.name}
                                            id={asset.name}
                                            checked={selectedAssets.includes(asset.name)}
                                            onChange={(checked) => {
                                                const newAssets = checked
                                                    ? [...selectedAssets, asset.name]
                                                    : selectedAssets.filter(name => name !== asset.name);
                                                setSelectedAssets(newAssets);
                                            }}
                                        >
                                            <div className="flex flex-col">
                                                <p className="font-medium text-gray-100 mb-1">
                                                    {asset.name}
                                                </p>
                                                <div className="flex items-center gap-x-4 text-sm text-gray-400">
                                                    <div className="flex items-center gap-x-1">
                                                        <DownloadIcon className="w-4 h-4" />
                                                        <span>{numify(asset.downloadCount)}</span>
                                                    </div>
                                                    <span>{bytesToString(asset.size)}</span>
                                                </div>
                                            </div>
                                        </Checkbox>
                                ))}
                            </div>
                        :
                        null
                    }
                </div>
                <div className={'flex justify-end mt-3'}>
                    <Button disabled={!selectedRelease || loading || !selectedAssets || isEmptyArray(selectedAssets)} onClick={InstallPlugin} className={'flex items-center gap-x-2'}>
                        {loading && <Spinner size={'small'} />}
                        {t('install.install-plugin')}
                    </Button>
                </div>
            </div>
        </Modal>
    )
}

export default function InstallButton({ plugin }: { plugin: ExternalPlugin; }) {
    const { t } = useTranslation('arix/server/addons/plugins');
    const [visible, setVisible] = useState(false);

    return (
        <div>
            <ModalContent 
                plugin={plugin}
                appear 
                visible={visible} 
                onDismissed={() => setVisible(false)}
            />

            <Button onClick={() => setVisible(true)}>
                {t('install.select-version')}
            </Button>
        </div>
    )
}