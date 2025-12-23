import React, { useState } from 'react';

//! Components
import CopyOnClick from '@/components/elements/CopyOnClick';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Button from '@/components/elements/Button';
import Can from '@/components/elements/Can';
import BanPlayerModal from './BanPlayerModal';

//! Vendors
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faUser, faGavel, faExclamationCircle, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';

//! States
import { Player } from '@/api/server/player/getPlayers';
import KickPlayerModal from '@/components/server/player/KickPlayerModal';

interface Props {
    uuid: string;
    admin: boolean;
    player: Player;
    className?: string;
    refresh?: () => void;
}

const PlayersRow = ({ uuid, admin, player, className, refresh }: Props) => {
    const [banvisible, setBanVisible] = useState(false);
    const [kickvisible, setKickVisible] = useState(false);

    return (
        <GreyRowBox $hoverable={false} className={className} css={tw`mb-2`}>
            <div css={tw`hidden md:block`}>
                <FontAwesomeIcon icon={faUser} fixedWidth />
            </div>

            <div css={tw`flex-1 ml-4`}>
                <p css={tw`text-lg`}>
                    {player?.authid != null ? (
                        <a href={`https://steamcommunity.com/profiles/${player.authid.split('@')[0]}`} target='_blank'>
                            {player.nickname || 'Noname'} {admin ? <span css={tw`text-neutral-400`}>(ADMIN)</span> : ''}
                        </a>
                    ) : (
                        <>
                            {player.nickname || 'Noname'} {admin ? <span css={tw`text-neutral-400`}>(ADMIN)</span> : ''}
                        </>
                    )}
                </p>
            </div>

            {player?.authid != null && (
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <CopyOnClick text={`${player.authid}`}>
                        <p css={tw`text-sm`}>{player.authid}</p>
                    </CopyOnClick>
                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>Auth Id</p>
                </div>
            )}

            {player?.ping != null && (
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <p css={tw`text-sm`}>{player.ping}</p>
                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>Ping</p>
                </div>
            )}

            <div css={tw`ml-8`}>
                <Can action={'players.kick'}>
                    <KickPlayerModal
                        visible={kickvisible}
                        onModalDismissed={() => setKickVisible(false)}
                        uuid={uuid}
                        playerId={player.id}
                        playerName={player.nickname ?? 'Noname'}
                        onSuccess={refresh}
                    />
                    <Button color={'grey'} isSecondary css={tw`mr-2`} onClick={() => setKickVisible(true)}>
                        <FontAwesomeIcon icon={faExclamationCircle} fixedWidth />
                    </Button>
                </Can>

                <Can action='players.ban'>
                    <BanPlayerModal
                        visible={banvisible}
                        onModalDismissed={() => setBanVisible(false)}
                        uuid={uuid}
                        playerId={player.id}
                        playerName={player.nickname ?? 'Noname'}
                        onSuccess={refresh}
                    />
                    <Button color='red' isSecondary onClick={() => setBanVisible(true)}>
                        <FontAwesomeIcon icon={faGavel} fixedWidth />
                    </Button>
                </Can>
            </div>
        </GreyRowBox>
    );
};

export default PlayersRow;
