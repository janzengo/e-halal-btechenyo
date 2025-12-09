import { Head, useForm } from '@inertiajs/react';
import React from 'react';

interface TwoFactorChallengeForm {
    code: string;
}

export default function VoterTwoFactorChallenge() {
    const { data, setData, post, processing, errors, reset } = useForm<TwoFactorChallengeForm>({
        code: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('auth.two-factor.verify'), {
            onFinish: () => reset('code'),
        });
    };

    return (
        <>
            <Head title="Two-Factor Authentication" />
            <div className="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
                <h1 className="text-4xl font-bold text-green-700 mb-4">Two-Factor Authentication</h1>
                <p className="text-gray-600 mb-6">Please enter the code from your authenticator app.</p>
                <form onSubmit={submit} className="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
                    <div className="mb-4">
                        <label htmlFor="code" className="block text-gray-700 text-sm font-bold mb-2">
                            Authentication Code
                        </label>
                        <input
                            id="code"
                            type="text"
                            name="code"
                            value={data.code}
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            onChange={(e) => setData('code', e.target.value)}
                            required
                            maxLength={6}
                        />
                        {errors.code && <div className="text-red-500 text-xs italic mt-2">{errors.code}</div>}
                    </div>
                    <div className="flex items-center justify-between">
                        <button
                            type="submit"
                            className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            disabled={processing}
                        >
                            Verify Code
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}
