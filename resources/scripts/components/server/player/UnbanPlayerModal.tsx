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
    onSuccess?: () => void;
}

interface Values {
    id: string;
}

const MutePlayerModal = ({ uuid, onSuccess }: Props) => {
    const { dismiss } = useContext(ModalContext);

    return (
        <Formik<Values>
            initialValues={{
                id: '',
            }}
            onSubmit={async (values, { setSubmitting, setErrors }) => {
                try {
                    await sendCommand(uuid, 'Unban', `id=${values.id}`);

                    onSuccess?.();
                    dismiss();
                } catch (err) {
                    setErrors({
                        id: 'Please select a valid id',
                    });
                } finally {
                    setSubmitting(false);
                }
            }}
        >
            {({ isSubmitting, values }) => (
                <Form>
                    <h3 css={tw`text-xl mb-4`}>Unban</h3>

                    <Field name='id' label='Id' placeholder='Id or IP of the user to unban' />

                    <div css={tw`mt-6 text-right`}>
                        <Button type='button' isSecondary onClick={dismiss} css={tw`mr-2`}>
                            Cancel
                        </Button>
                        <Button type='submit' color='green' disabled={isSubmitting}>
                            Unban
                        </Button>
                    </div>
                </Form>
            )}
        </Formik>
    );
};

export default asModal<Props>()(MutePlayerModal);
