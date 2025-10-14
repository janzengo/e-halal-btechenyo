import { Head, Link } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout-single';
import { Form } from '@inertiajs/react';
import { LoaderCircle, ArrowLeft, Shield, KeyRound, Mail } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSeparator,
  InputOTPSlot,
} from '@/components/ui/input-otp';
import { toast } from 'sonner';
import React, { useState, useEffect } from 'react';

interface AdminOtpVerificationProps {
    status?: string;
    email?: string;
    username?: string;
}

export default function AdminOtpVerification({ status, email, username }: AdminOtpVerificationProps) {
    const [otp, setOtp] = useState('');

    // Show status toast if provided
    useEffect(() => {
        if (status) {
            toast.success(status);
        }
    }, [status]);

    const config = {
        icon: Shield,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-200'
    };

    const IconComponent = config.icon;

    return (
        <>
            <Head title="OTP Verification - Admin Login" />
            
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
                        <span className="px-3 text-xs text-gray-500 uppercase">OTP VERIFICATION</span>
                        <div className="flex-1 h-px bg-gray-300"></div>
                    </div>

                    {/* Email Display */}
                    <div className="flex items-center justify-center mb-4">
                        <Mail className="h-5 w-5 text-green-600 mr-2" />
                        <span className="text-sm text-gray-600">
                            Code sent to: <span className="font-medium text-gray-900">{email || username}</span>
                        </span>
                    </div>

                    <Form
                        method="post"
                        action="/auth/admin-btech/otp-verify"
                        data={{ otp }}
                        onError={(errors) => {
                            if (errors.otp) {
                                toast.error(errors.otp);
                            } else {
                                toast.error('OTP verification failed. Please try again.');
                            }
                        }}
                    >
                        {({ processing, errors }) => (
                            <div className="space-y-4">
                                <div className="flex justify-center">
                                    <InputOTP 
                                        maxLength={6} 
                                        value={otp}
                                        onChange={(value) => setOtp(value)}
                                        className="gap-2"
                                    >
                                        <InputOTPGroup className="gap-2">
                                            <InputOTPSlot index={0} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                            <InputOTPSlot index={1} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                            <InputOTPSlot index={2} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                        </InputOTPGroup>
                                        <InputOTPSeparator className="text-gray-400" />
                                        <InputOTPGroup className="gap-2">
                                            <InputOTPSlot index={3} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                            <InputOTPSlot index={4} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                            <InputOTPSlot index={5} className="w-10 h-10 text-base font-semibold border-2 border-gray-300 hover:border-green-300 focus:border-green-500 transition-colors" />
                                        </InputOTPGroup>
                                    </InputOTP>
                                </div>
                                
                                <input type="hidden" name="otp" value={otp} />
                                <input type="hidden" name="email" value={email} />
                                <input type="hidden" name="username" value={username} />
                                
                                <InputError message={errors.otp} />

                                <Button
                                    type="submit"
                                    variant="outlinePrimary"
                                    className="w-full"
                                    disabled={processing || otp.length !== 6}
                                >
                                    VERIFY OTP
                                    {processing ? (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    ) : (
                                        <KeyRound className="h-4 w-4" />
                                    )}
                                </Button>
                            </div>
                        )}
                    </Form>

                    <div className="text-center">
                        <p className="text-xs text-muted-foreground">
                            Didn't receive the code?{' '}
                            <Link
                                href="/auth/admin-btech/otp-resend"
                                className="text-green-600 hover:text-primary/80 font-medium transition-colors duration-200"
                                onClick={() => {
                                    toast.info('Resending OTP...');
                                }}
                            >
                                Resend OTP
                            </Link>
                        </p>
                    </div>
                </div>
            </AuthLayout>
        </>
    );
}
