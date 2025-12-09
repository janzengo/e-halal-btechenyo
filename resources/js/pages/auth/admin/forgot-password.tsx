import { Head, Link } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout-single';
import { Form } from '@inertiajs/react';
import { LoaderCircle, Mail, ArrowLeft, Shield, Send, CheckCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import React, { useState } from 'react';

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const [email, setEmail] = useState('');
    const [isSubmitted, setIsSubmitted] = useState(false);

    const config = {
        icon: Shield,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-200'
    };

    const IconComponent = config.icon;

    return (
        <>
            <Head title="Forgot Password" />
            
            <AuthLayout title="E-Halal BTECHenyo" description="A WEB-BASED ELECTION SYSTEM FOR DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG">
                <div className="space-y-6">
                    {/* Back to Login Link */}
                    <div className="w-full">
                        <Link
                            href="/auth/admin-btech"
                            className="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-medium text-sm transition-colors duration-200 group"
                        >
                            <ArrowLeft className="h-4 w-4 group-hover:-translate-x-1 transition-transform duration-200" />
                            Back to Login
                        </Link>
                    </div>

                    {/* Divider Line */}
                    <div className="flex items-center mb-2">
                        <div className="flex-1 h-px bg-gray-300"></div>
                        <span className="px-3 text-xs text-gray-500 uppercase">RESET PASSWORD</span>
                        <div className="flex-1 h-px bg-gray-300"></div>
                    </div>

                    {!isSubmitted ? (
                        <>
                            <div className="text-center">
                                <p className="text-xs text-gray-600">
                                    Enter your email address to receive password reset instructions
                                </p>
                            </div>
                            <Form
                                method="post"
                                action="/auth/admin-btech/forgot-password"
                                onSuccess={() => setIsSubmitted(true)}
                            >
                                {({ processing, errors }) => (
                                    <div className="space-y-4">
                                        <div className="relative group">
                                            <Input
                                                type="email"
                                                name="email"
                                                placeholder="ENTER YOUR EMAIL ADDRESS"
                                                autoComplete="email"
                                                className="pr-10 pl-3 py-6 hover:border-green-300 focus:border-green-500 focus:ring-0 transition-all duration-200 placeholder:text-xs"
                                                required
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                            />
                                            <Mail className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground group-focus-within:text-green-500 transition-colors" />
                                        </div>
                                        <InputError message={errors.email} />

                                        <Button
                                            type="submit"
                                            variant="outlinePrimary"
                                            className="w-full"
                                            disabled={processing}
                                        >
                                            SEND RESET LINK
                                            {processing ? (
                                                <LoaderCircle className="h-4 w-4 animate-spin" />
                                            ) : (
                                                <Send className="h-4 w-4" />
                                            )}
                                        </Button>
                                    </div>
                                )}
                            </Form>
                        </>
                    ) : (
                        <div className="text-center space-y-4">
                            <div className="flex justify-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                    <CheckCircle className="h-8 w-8 text-green-600" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <h2 className="text-xl font-semibold text-gray-900">Check your email</h2>
                                <p className="text-sm text-gray-600">
                                    We've sent a password reset link to <span className="font-medium text-gray-900">{email}</span>
                                </p>
                                <p className="text-xs text-gray-500">
                                    Didn't receive the email? Check your spam folder or try again.
                                </p>
                            </div>
                            <div className="flex flex-col gap-2">
                                <Button
                                    onClick={() => setIsSubmitted(false)}
                                    variant="outline"
                                    className="w-full"
                                >
                                    Try another email
                                </Button>
                                <Link
                                    href="/auth/admin-btech"
                                    className="text-sm text-green-600 hover:text-green-800 font-medium transition-colors duration-200"
                                >
                                    Back to Login
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </AuthLayout>

            {status && (
                <div className="fixed top-4 right-4 bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg text-sm font-medium">
                    {status}
                </div>
            )}
        </>
    );
}
