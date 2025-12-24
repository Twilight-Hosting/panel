import React, { useEffect, useState } from 'react';

//! Components
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import PlayersRow from './PlayersRow';
import Fade from '@/components/elements/Fade';
import Button from '@/components/elements/Button';
import MessageBox from '@/components/MessageBox';

//! Plugins
import useFlash from '@/plugins/useFlash';

//! API
import { PlayersData, Player, ApiResponse } from '@/api/server/player/getPlayers';
import getPlayers from '@/api/server/player/getPlayers';

//! State Management
import { ServerContext } from '@/state/server';

//! Vendors
import tw from 'twin.macro';
import useSWR from 'swr';
import Can from '@/components/elements/Can';
import UnbanPlayerModal from '@/components/server/player/UnbanPlayerModal';
import TitledGreyBox from '@/components/elements/TitledGreyBox';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { data, error, mutate } = useSWR<ApiResponse<PlayersData>>(`${uuid}`, (key) => getPlayers(key), {
        revalidateOnFocus: true,
        errorRetryCount: 7,
        refreshInterval: 60000,
    });

    const [unbanvisible, setUnbanVisible] = useState(false);

    useEffect(() => {
        if (!error) {
            clearFlashes('players');
        } else {
            clearAndAddHttpError({ key: 'players', error });
        }
    }, [error]);

    return (
        <ServerContentBlock title={'Players'} showFlashKey={'players'}>
            {!data ? (
                <Spinner size={'large'} centered />
            ) : (
                <Fade timeout={150}>
                    {data.success ? (
                        <>
                            <Button onClick={() => mutate()} size='small' css={tw`w-full mt-4 sm:w-auto sm:mt-0`}>
                                Refresh
                            </Button>

                            <Can action='players.unban'>
                                <UnbanPlayerModal
                                    visible={unbanvisible}
                                    onModalDismissed={() => setUnbanVisible(false)}
                                    uuid={uuid}
                                    onSuccess={mutate}
                                />
                                <Button
                                    onClick={() => setUnbanVisible(true)}
                                    size='small'
                                    css={tw`w-full mt-4 sm:w-auto sm:mt-0`}
                                >
                                    Unban
                                </Button>
                            </Can>
                            <p css={tw`text-sm text-neutral-400 mt-2 mb-4`}>
                                There are {data.data.online_players} of {data.data.max_players} online players.
                            </p>

                            <div css={tw`md:flex`}>
                                <div css={tw`flex-1`}>
                                    <p css={tw`text-sm text-neutral-400 mt-2 mb-4`}>Players List</p>
                                    {data.data.players.length > 0 ? (
                                        data.data.players.map((player, index) => {
                                            return (
                                                <PlayersRow
                                                    key={index}
                                                    uuid={uuid}
                                                    admin={player.admin}
                                                    player={player}
                                                    className={index > 0 ? 'mt-1' : undefined}
                                                    refresh={mutate}
                                                />
                                            );
                                        })
                                    ) : (
                                        <p css={tw`text-center text-sm text-neutral-400`}>
                                            It looks like you don't have players on the server.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </>
                    ) : data.data.message.toLowerCase().includes('plugin not installed') ? (
                        <TitledGreyBox title={'What is this?'} css={tw`mb-6 md:mb-10`}>
                            This section requires a server-side plugin that integrates with the panel to enable
                            real-time game actions. Once installed, you'll be able to view active players, execute
                            commands, manage permissions, and perform administrative actions directly from the panel
                            without directly interfacing with the console or any other external tool.

                            The plugin will auto install when clicking the button and the server will need a restart to apply the changes
                            No additional actions need to be done, once restarted this section will work automatically
                        </TitledGreyBox>
                    ) : (
                        // TODO: ADD BUTTON TO install using
                        //         import pullFile from '@/api/server/files/pullFile';
                        //         await pullFile(uuid, `https://arix.gg/arix-api/v1${download}`, '/', 'server.jar');

                        <MessageBox type={'error'} title={'ERROR:'}>
                            {data.data.message}
                        </MessageBox>
                    )}
                </Fade>
            )}
        </ServerContentBlock>
    );
};
