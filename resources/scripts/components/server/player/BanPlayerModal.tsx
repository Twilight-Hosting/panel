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
    until: string; // datetime-local
    permanent: boolean;
}

const BanPlayerModal = ({ uuid, playerId, playerName, onSuccess }: Props) => {
    const { dismiss } = useContext(ModalContext);

    const toSeconds = (until: string, permanent: boolean): number => {
        if (permanent) return 99999999;

        const target = new Date(until).getTime();
        const now = Date.now();

        if (isNaN(target) || target <= now) {
            throw new Error('Invalid ban date');
        }

        return Math.floor((target - now) / 1000);
    };

    return (
        <Formik<Values>
            initialValues={{
                reason: 'No Reason',
                until: '',
                permanent: false,
            }}
            onSubmit={async (values, { setSubmitting, setErrors }) => {
                try {
                    const seconds = toSeconds(values.until, values.permanent);

                    await sendCommand(
                        uuid,
                        'Ban',
                        `id=${playerId}&reason=${encodeURIComponent(values.reason)}&seconds=${seconds}`
                    );

                    onSuccess?.();
                    dismiss();
                } catch (err) {
                    setErrors({
                        until: 'Please select a valid future date',
                    });
                } finally {
                    setSubmitting(false);
                }
            }}
        >
            {({ isSubmitting, values }) => (
                <Form>
                    <h3 css={tw`text-xl mb-4`}>Ban {playerName}</h3>

                    <Field name='reason' label='Reason' placeholder='Breaking server rules' />

                    {!values.permanent && <Field name='until' type='datetime-local' label='Ban until' />}

                    <div css={tw`mt-4`}>
                        <FormikSwitch name='permanent' label='Permanent ban' description='Ban indefinitely' />
                    </div>

                    <div css={tw`mt-6 text-right`}>
                        <Button type='button' isSecondary onClick={dismiss} css={tw`mr-2`}>
                            Cancel
                        </Button>
                        <Button type='submit' color='red' disabled={isSubmitting}>
                            Ban
                        </Button>
                    </div>
                </Form>
            )}
        </Formik>
    );
};

export default asModal<Props>()(BanPlayerModal);
