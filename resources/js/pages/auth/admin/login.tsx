import { Head, Link } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout-single';
import { Form } from '@inertiajs/react';
import { LoaderCircle, Fingerprint, ArrowLeft, Shield, KeyRound, LogIn } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toast } from 'sonner';
import React, { useState, useEffect } from 'react';

interface AdminLoginProps {
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function AdminLogin({ flash }: AdminLoginProps) {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');

    // Show flash messages if provided
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const config = {
        icon: Shield,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-200'
    };

    const IconComponent = config.icon;

    return (
        <>
            <Head title="Admin Login" />
            
            <AuthLayout title="E-Halal BTECHenyo" description="A WEB-BASED ELECTION SYSTEM FOR DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG">
                <div className="space-y-6">
                    {/* Back to Main Site Link */}
                    <div className="w-full">
                        <Link
                            href="/"
                            className="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-medium text-sm transition-colors duration-200 group"
                        >
                            <ArrowLeft className="h-4 w-4 group-hover:-translate-x-1 transition-transform duration-200" />
                            Back to Main Site
                        </Link>
                    </div>

                    {/* Divider Line */}
                    <div className="flex items-center mb-2">
                            <div className="flex-1 h-px bg-gray-300"></div>
                            <span className="px-3 text-xs text-gray-500 uppercase">WELCOME BACK ADMIN</span>
                            <div className="flex-1 h-px bg-gray-300"></div>
                    </div>

                    <Form
                        method="post"
                        action="/auth/admin-btech"
                        onError={(errors) => {
                            if (errors.username) {
                                toast.error(errors.username);
                            } else if (errors.password) {
                                toast.error(errors.password);
                            } else {
                                toast.error('Login failed. Please check your credentials.');
                            }
                        }}
                    >
                        {({ processing, errors }) => (
                            <div className="space-y-4">
                                <div className="relative group">
                                    <Input
                                        type="text"
                                        name="username"
                                        placeholder="ENTER YOUR USERNAME"
                                        autoComplete="username"
                                        className="pr-10 pl-3 py-6 hover:border-green-300 focus:border-green-500 focus:ring-0 transition-all duration-200 placeholder:text-xs"
                                        required
                                        value={username}
                                        onChange={(e) => setUsername(e.target.value)}
                                    />
                                    <Fingerprint className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground group-focus-within:text-green-500 transition-colors" />
                                </div>

                                <div className="relative group">
                                    <Input
                                        type="password"
                                        name="password"
                                        placeholder="ENTER YOUR PASSWORD"
                                        autoComplete="current-password"
                                        className="pr-10 pl-3 py-6 hover:border-green-300 focus:border-green-500 focus:ring-0 transition-all duration-200 placeholder:text-xs"
                                        required
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                    />
                                    <KeyRound className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground group-focus-within:text-green-500 transition-colors" />
                                </div>

                                {/* General error message moved below all input fields */}
                                {(errors.username || errors.password) && (
                                    <InputError message={errors.username || errors.password} />
                                )}

                                <Button
                                    type="submit"
                                    variant="outlinePrimary"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    LOGIN
                                    {processing ? (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    ) : (
                                        <LogIn className="h-4 w-4" />
                                    )}
                                </Button>
                            </div>
                        )}
                    </Form>

                    <div className="text-center">
                        <p className="text-xs text-muted-foreground">
                            Forgot your password?{' '}
                            <Link href="/auth/admin/forgot-password" className="text-green-600 hover:text-primary/80 font-medium transition-colors duration-200">
                                Reset it here
                            </Link>
                        </p>
                    </div>
                </div>
            </AuthLayout>

        </>
    );
}
