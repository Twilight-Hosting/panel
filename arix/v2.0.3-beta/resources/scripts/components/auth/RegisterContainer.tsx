import React, { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import register from '@/api/auth/register';
import RegisterFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { UserCircleIcon, AtSymbolIcon } from '@heroicons/react/outline';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';
import Turnstile, { useTurnstile } from "react-turnstile";

interface Values {
    email: string;
    username: string;
    firstname: string;
    lastname: string;
}

const RegisterContainer = () => {
    const ref = useRef<Reaptcha>(null);
    const turnstile = useTurnstile();
    const [token, setToken] = useState('');

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const { recaptcha: recaptchaSettings, turnstile: turnstileSettings } = useStoreState((state) => state.settings.data!);

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        if (recaptchaSettings.enabled && recaptchaSettings.method && !token) {
            if (recaptchaSettings.method === 'recaptcha') {
            ref.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
            } else if (recaptchaSettings.method === 'turnstile') {
                turnstile.execute().catch((error: unknown) => {
                    console.error(error);

                    setSubmitting(false);
                    clearAndAddHttpError({ error: error as Error });
                });
            }

            return;
        }

        register({ ...values, recaptchaData: token })
            .then((response) => {
                if (response.complete) {
                    addFlash({
                        type: 'success',
                        title: 'Success',
                        message: 'You have successfully registered, check your email',
                    });

                    setSubmitting(false);
                }
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (recaptchaSettings.enabled && recaptchaSettings.method) {
                    if (recaptchaSettings.method === 'recaptcha') {
                        ref.current!.reset();
                    } else if (recaptchaSettings.method === 'turnstile') {
                        turnstile.reset();
                    }
                }

                const data = JSON.parse(error.config.data);

                if (!/^[a-zA-Z0-9][a-zA-Z0-9_.-]*[a-zA-Z0-9]$/.test(data.username))
                    error =
                        'The username must start and end with alpha-numeric characters and contain only letters, numbers, dashes, underscores, and periods.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) error = 'The email must be a valid email address.';

                setSubmitting(false);
                if (typeof error === 'string') {
                    addFlash({
                        type: 'error',
                        title: 'Error',
                        message: error || '',
                    });
                } else {
                    clearAndAddHttpError({ error });
                }
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ email: '', username: '', firstname: '', lastname: '' }}
            validationSchema={object().shape({
                email: string().required('An email must be provided.'),
                username: string().required('A username must be provided.'),
                firstname: string().required('A first name must be provided.'),
                lastname: string().required('A last name must be provided.'),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <RegisterFormContainer title={'Create an Account'} css={tw`w-full flex`}>
                    <div className="grid lg:grid-cols-2 gap-4 w-full">
                        <Field
                            type={'text'}
                            label={'First Name'}
                            name={'firstname'}
                            placeholder={'First Name'}
                            disabled={isSubmitting}
                            icon={UserCircleIcon}
                        />
                        <Field
                            type={'text'}
                            label={'Last Name'}
                            name={'lastname'}
                            placeholder={'Last Name'}
                            disabled={isSubmitting}
                            icon={UserCircleIcon}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            type={'text'}
                            label={'Username'}
                            name={'username'}
                            placeholder={'Username'}
                            disabled={isSubmitting}
                            icon={UserCircleIcon}
                        />
                    </div>
                    <div css={tw`mt-6 mb-3`}>
                        <Field
                            type={'email'}
                            label={'Email'}
                            name={'email'}
                            placeholder={'example@gmail.com'}
                            disabled={isSubmitting}
                            icon={AtSymbolIcon}
                        />
                    </div>
                    <div className={'z-50 relative'}>
                        {recaptchaSettings.enabled && recaptchaSettings.method && (
                            recaptchaSettings.method === 'recaptcha' ? (
                            <Reaptcha
                                ref={ref}
                                size={'invisible'}
                                sitekey={recaptchaSettings.siteKey || '_invalid_key'}
                                onVerify={(response) => {
                                    setToken(response);
                                    submitForm();
                                }}
                                onExpire={() => {
                                    setSubmitting(false);
                                    setToken('');
                                }}
                            />
                            ) : recaptchaSettings.method === 'turnstile' && (
                                <Turnstile
                                    sitekey={turnstileSettings.siteKey || '_invalid_key'}
                                    execution="render"
                                    appearance="always"
                                    onVerify={(response) => {
                                        setToken(response);
                                    }}
                                    onExpire={() => {
                                        setSubmitting(false);
                                        setToken('');
                                    }}
                                />
                            )
                        )}
                    </div>
                    <div css={tw`mt-3`}>
                        <Button type={'submit'} className={'w-full !py-3'} disabled={isSubmitting}>
                            Register
                        </Button>
                    </div>
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-300 tracking-wide uppercase no-underline hover:text-neutral-200`}
                        >
                            Already an account? Log in.
                        </Link>
                    </div>
                </RegisterFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;