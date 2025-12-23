import React, { useContext } from 'react';
import { Form, Formik } from 'formik';
import Field from '@/components/elements/Field';
import Button from '@/components/elements/Button';
import ModalContext from '@/context/ModalContext';
import asModal from '@/hoc/asModal';
import sendCommand from '@/api/server/player/sendAction';
import FormikSwitch from '@/components/elements/FormikSwitch';
import tw from 'twin.macro';

interface Props {
    uuid: string;
    playerId: number;
    playerName: string;
    onSuccess?: () => void;
}

interface Values {
    reason: string;
}

const BanPlayerModal = ({ uuid, playerId, playerName, onSuccess }: Props) => {
    const { dismiss } = useContext(ModalContext);

    return (
        <Formik<Values>
            initialValues={{
                reason: 'No Reason',
            }}
            onSubmit={async (values, { setSubmitting, setErrors }) => {
                try {
                    await sendCommand(
                        uuid,
                        'Kick',
                        `id=${playerId}&reason=${encodeURIComponent(values.reason)}`
                    );

                    onSuccess?.();
                    dismiss();
                } catch (err) {
                    setErrors({
                        reason: 'No reason',
                    });
                } finally {
                    setSubmitting(false);
                }
            }}
        >
            {({ isSubmitting, values }) => (
                <Form>
                    <h3 css={tw`text-xl mb-4`}>Kick {playerName}</h3>

                    <Field name='reason' label='Reason' placeholder='Breaking server rules' />

                    <div css={tw`mt-6 text-right`}>
                        <Button type='button' isSecondary onClick={dismiss} css={tw`mr-2`}>
                            Cancel
                        </Button>
                        <Button type='submit' color='grey' disabled={isSubmitting}>
                            Kick
                        </Button>
                    </div>
                </Form>
            )}
        </Formik>
    );
};

export default asModal<Props>()(BanPlayerModal);
