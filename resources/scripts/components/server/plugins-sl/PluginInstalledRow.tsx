import React from 'react';
import { InstalledPlugin } from '@/api/server/plugins-sl/Plugins';
import GreyRowBox from '@/components/elements/GreyRowBox';
import DeletePlugin from '@/components/server/plugins-sl/DeletePlugin';
import { ViewGridAddIcon } from '@heroicons/react/outline';

export default function InstalledPluginRow({ ...props}: InstalledPlugin){

    return(
        <GreyRowBox key={props.id} className={'items-center gap-3'} $hoverable={false}>
            {props.plugin_icon === null ?
                    <ViewGridAddIcon
                        width={64}
                        height={64}
                        className={'shrink-0'}
                    />
                    :
                    <img 
                        src={`https://plugins.scpslgame.com/api/uploads/${props.plugin_icon}`}
                        width={64} 
                        height={64} 
                        className={'shrink-0'}
                    />}
            <div>
                <p className={'text-lg font-medium'}>
                    {props.plugin_name}
                </p>
                <p className={'text-sm text-gray-300'}>
                    {props.plugin_version}
                </p>
            </div>

            <DeletePlugin 
                plugin_id={props.id}
                plugin_name={props.plugin_name}
                framework={props.plugin_framework}
                file_names={props.files}
            />
        </GreyRowBox>
    )
}