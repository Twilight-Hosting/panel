import React from 'react';
import { SettableModalProps } from '@/hoc/asModal';

export interface ModalContextValues {
    dismiss: () => void;
    setPropOverrides: (value: {
        props: { uuid: string; playerId: number; playerName: string; onSuccess: (() => void) | undefined };
    }) => void;
}

const ModalContext = React.createContext<ModalContextValues>({
    dismiss: () => null,
    setPropOverrides: () => null,
});

ModalContext.displayName = 'ModalContext';

export default ModalContext;
