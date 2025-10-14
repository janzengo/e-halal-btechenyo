import { Head, useForm } from '@inertiajs/react';
import React from 'react';

interface OtpVerifyForm {
    otp: string;
}

export default function VoterOTPVerify() {
    const { data, setData, post, processing, errors, reset } = useForm<OtpVerifyForm>({
        otp: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('auth.otp.verify.submit'), {
            onFinish: () => reset('otp'),
        });
    };

    return (
        <>
            <Head title="Verify OTP" />
            <div className="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
                <h1 className="text-4xl font-bold text-green-700 mb-4">OTP Verification</h1>
                <p className="text-gray-600 mb-6">An OTP has been sent to your registered email. Please enter it below.</p>
                <form onSubmit={submit} className="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
                    <div className="mb-4">
                        <label htmlFor="otp" className="block text-gray-700 text-sm font-bold mb-2">
                            OTP Code
                        </label>
                        <input
                            id="otp"
                            type="text"
                            name="otp"
                            value={data.otp}
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            onChange={(e) => setData('otp', e.target.value)}
                            required
                            maxLength={6}
                        />
                        {errors.otp && <div className="text-red-500 text-xs italic mt-2">{errors.otp}</div>}
                    </div>
                    <div className="flex items-center justify-between">
                        <button
                            type="submit"
                            className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            disabled={processing}
                        >
                            Verify OTP
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}
