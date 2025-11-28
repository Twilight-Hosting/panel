import React, { useState, useEffect } from 'react';
import getPlugins from '@/api/server/plugins-sl/getPlugins';
import getInstalledPlugins from '@/api/server/plugins-sl/getInstalledPlugins';
import { ExternalPlugin, QueryParams } from '@/api/server/plugins-sl/Plugins';
import PluginRow from '@/components/server/plugins-sl/PluginRow';
import PluginInstalledRow from '@/components/server/plugins-sl/PluginInstalledRow';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import { DocumentDownloadIcon, ChevronDownIcon } from '@heroicons/react/outline';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import PaginationFooter from '@/components/elements/table/PaginationFooter';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { Button } from "@/components/elements/button/index"
import Input from '@/components/elements/Input';
import { useDeepMemoize } from '@/plugins/useDeepMemoize';
import FlashMessageRender from '@/components/FlashMessageRender';
import { PaginatedResult } from '@/api/http';
import { useTranslation } from 'react-i18next';

type RadioProps = {
    id: string;
    target: keyof QueryParams;
    active?: string;
    updateFilter: (target: keyof QueryParams, value: string | number) => void;
};

const RadioButton: React.FC<RadioProps> = ({ id, target, active, updateFilter }) => {
    return (
        <div className="flex items-center gap-x-1 cursor-pointer">
            <Input
                id={`${id}-${target}`}
                name={target}
                type="radio"
                checked={active === id}
                onClick={() => updateFilter(target, id)}
            />
            <label htmlFor={`${id}-${target}`} className={'capitalize'}>
                {id.toLowerCase()}
            </label>
        </div>
    );
};

const TagCheckbox: React.FC<{
    id: string;
    active: string[];
    updateFilter: (categories: string[]) => void
}> = ({ id, active, updateFilter }) => {
    return (
        <div className='flex items-center gap-x-2 cursor-pointer'>
            <Input
                id={`category-${id}`}
                type="checkbox"
                checked={active.includes(id)}
                onChange={(e) => {
                    const newCategories = e.target.checked
                        ? [...active, id]
                        : active.filter(tag => tag !== id);
                        updateFilter(newCategories);
                }}
            />
            <label htmlFor={`category-${id}`} className={'capitalize text-sm'}>
                {id.replace(/(A-Z)/g, ' $1').trim()}
            </label>
        </div>
    );
}

const PluginsContainer = () => {
    const { t } = useTranslation('arix/server/addons/plugins');
    const [showInstalled, setShowInstalled] = useState<boolean>(false);
    const [searchTerm, setSearchTerm] = useState<string>('');
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const [loading, setLoading] = useState<boolean>(true);
    const { addError, clearFlashes } = useFlash();
    const [filters, setFilters] = useState<QueryParams>({ page: 1, framework: 'labapi', tags: [], search: '' });
    const [plugins, setPlugins] = useState<PaginatedResult<ExternalPlugin>>();
    const setInstalledPlugins = ServerContext.useStoreActions((state) => state.slPlugins.setPlugins);
    const installedPlugins = useDeepMemoize(ServerContext.useStoreState((state) => state.slPlugins.data));

    const resetFilters = () => {
        setSearchTerm('');
        setFilters({ page: 1, framework: 'labapi', tags: [], search: '' });
    }

    const updateFilter = (target: keyof QueryParams, value: string | number): void => {
        if(target==='framework') {
            setSearchTerm('');
            setFilters((prevFilters) => ({
                ...prevFilters,
                page: 1,
                search: '',
                [target]: String(value) 
            }));
        } else {
            if(filters[target] === value){
                setFilters((prevFilters) => ({
                ...prevFilters,
                page: 1,
                [target]: ''
              }));
            } else {
                setFilters((prevFilters) => ({
                    ...prevFilters,
                    page: 1,
                    [target]: value
                }));
            }
        }
    };

    const updateCheckbox = (value: string[]): void => {
        setFilters((prevFilters) => ({
            ...prevFilters,
            page: 1,
            search: '',
            tags: value,
        }));
    };

    useEffect(() => {
        if (searchTerm === filters.search) return; 
        const handler = setTimeout(() => {
            updateFilter('search', searchTerm);
        }, 1000);

        return () => clearTimeout(handler);
    }, [searchTerm, filters.search, updateFilter]);


    useEffect(() => {
        setLoading(true);
        clearFlashes('plugins');

        getPlugins({id, ...filters })
            .then((response: PaginatedResult<ExternalPlugin>) => {
                setPlugins(response);
            })
            .catch((error) => {
                addError({ key: 'plugins', message: error.message });
            })
            .finally(() => {
                setLoading(false); 
            });
    }, [id, filters]);

    useEffect(() => {
        clearFlashes('plugins');

        getInstalledPlugins({id})
            .then((response) => {
                setInstalledPlugins(response);
            })
            .catch((error) => {
                addError({ key: 'plugins', message: error.message });
            })
    }, [id]);

    return (
        <ServerContentBlock title={t('plugins')} icon={DocumentDownloadIcon}>
            <SpinnerOverlay visible={loading} fixed={true} size={'large'} />
            <FlashMessageRender byKey={'plugins'} />
            {!showInstalled ?
            <div className={'grid lg:grid-cols-4 gap-4'}>
                <div className={'lg:col-span-3'}>
                    <div className={'flex gap-x-2 mb-4'}>
                        <Input
                            name="search"
                            type="text"
                            placeholder={t('search-for-a-plugin')}
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                        <Button.Text onClick={() => setShowInstalled(!showInstalled)} className={'whitespace-nowrap'}>
                            {t('installed-plugins')}
                        </Button.Text>
                    </div>
                    <div className={'grid lg:grid-cols-2 gap-4'}>
                        {(plugins?.items?.length ?? 0) > 0 ? (
                            plugins?.items?.map((plugin: ExternalPlugin) => (
                                <PluginRow
                                    plugin={plugin}
                                    filters={filters}
                                />
                            ))
                        ) : (
                            <div className={'lg:col-span-2'}>
                                {t('no-plugins-found')}
                            </div>
                        )}
                    </div>
                    {plugins && (
                        <PaginationFooter
                            pagination={plugins.pagination}
                            onPageSelect={(page) => setFilters((value) => ({ ...value, page }))}
                        />
                    )}
                </div>
                <div>
                    <div className={'sticky top-4 bg-gray-700 rounded-box backdrop p-5 flex flex-col gap-5'}>
                        <div className={'flex flex-col gap-1'}>
                            <div className={'flex items-center justify-between'}>
                                <p className={'font-medium'}>{t('platform')}</p>
                                {JSON.stringify(filters) !== JSON.stringify({ 
                                    page: 1, 
                                    framework: 'labapi',
                                    tags: [],
                                    search: '',
                                }) && (
                                    <button onClick={() => resetFilters()} className={'text-sm text-gray-300 font-medium'}>
                                        ({t('reset-filters')})
                                    </button>
                                )}
                            </div>
                            <RadioButton id="all" active={filters.framework} target={'framework'} updateFilter={updateFilter} />
                            <RadioButton id="labapi" active={filters.framework} target={'framework'} updateFilter={updateFilter} />
                            <RadioButton id="exiled" active={filters.framework} target={'framework'} updateFilter={updateFilter} />
                        </div>
                        <div className={'flex flex-col gap-1'}>
                            <p className={'font-medium'}>Categories</p>
                            <div className={'flex flex-col gap-2 max-h-48 overflow-y-auto'}>
                                <TagCheckbox id="tools" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="framework" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="miscellaneous" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="gamemodes" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="items" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="roles" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="map" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="security" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="libraries" active={filters.tags || []} updateFilter={updateCheckbox}/>
                                <TagCheckbox id="administration" active={filters.tags || []} updateFilter={updateCheckbox}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            :
            <div className={'grid lg:grid-cols-3 gap-4'}>
                <div className={'lg:col-span-3 flex gap-x-2'}>
                    <Input
                        name="search"
                        type="text"
                        placeholder={t('search-for-a-plugin')}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                    <Button.Text onClick={() => setShowInstalled(!showInstalled)} className={'whitespace-nowrap'}>
                        {t('browse-plugins')}
                    </Button.Text>
                </div>
                {installedPlugins
                    .filter((plugin) => 
                        plugin.plugin_name.toLowerCase().replace(/-/g, ' ')
                            .includes(searchTerm.toLowerCase().replace(/-/g, ' '))
                    )
                    .map((plugin) => (
                    <PluginInstalledRow 
                        {...plugin}
                    />
                ))}
            </div>
            }
        </ServerContentBlock>
    )
}

export default PluginsContainer