import React, { useState } from 'react';
import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import { ExternalPlugin } from '@/api/server/plugins-sl/Plugins';
import { Button } from '@/components/elements/button/index';
import { numify } from "numify";
import { differenceInCalendarMonths, format, formatDistanceToNow } from 'date-fns';
import { ExternalLinkIcon, DownloadIcon, CalendarIcon, ViewGridAddIcon, ChevronUpIcon } from '@heroicons/react/outline';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import { useTranslation } from 'react-i18next';
import * as locales from 'date-fns/locale';
import { MarkdownRenderer } from '@/components/server/plugins-sl/MarkdownRenderer';

const getLocale = (localeKey: keyof typeof locales) => {
    if (locales[localeKey]) {
        return locales[localeKey];
    } else {
        const keyString = String(localeKey);
        console.warn(`Locale '${keyString}' not found. Falling back to '${locales.enUS}'`);
        return locales.enUS;
    }
};

const ModalContent = ({ plugin, visible, onDismissed, ...props }: RequiredModalProps & { plugin: ExternalPlugin; }) => {
    const { t } = useTranslation('arix/server/addons/plugins');
    const { i18n } = useTranslation();
    const currentLang = i18n.language;
    const localeKey = currentLang as keyof typeof locales;

    return (
        <Modal visible={visible} onDismissed={onDismissed} {...props}>
            <div className={'flex flex-col items-start gap-3 overflow-y-auto'}>
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
                <div className={'space-y-8'}>
                    <p>
                        {plugin.description}
                    </p>
                    {/* <div>
                        <p className={'text-lg'}>README:</p>
                        <MarkdownRenderer content={plugin.readme} />
                    </div> */}
                </div>
            </div>
        </Modal>
    )
}

export default function PluginDetails({ plugin } : { plugin: ExternalPlugin; }) {
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
                {t('install.details')}
            </Button>
        </div>
    )
}