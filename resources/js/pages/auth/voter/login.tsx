import AuthenticatedSessionController from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';
import { Form, Head, Link } from '@inertiajs/react';
import { LoaderCircle, ArrowLeft, Fingerprint, ArrowRight } from 'lucide-react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    return (
        <AuthLayout
            title="E-Halal BTECHenyo"
            description="A WEB-BASED ELECTION SYSTEM FOR DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG"
        >
            <Head title="Log in" />

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
                        <span className="px-3 text-xs text-gray-500 uppercase">LOGIN WITH YOUR STUDENT NUMBER</span>
                        <div className="flex-1 h-px bg-gray-300"></div>
                </div>
                <Form
                    {...AuthenticatedSessionController.store.form()}
                    resetOnSuccess={['student_number']}
                >
                    {({ processing, errors }) => (
                        <div className="space-y-4">
                            <div className="relative group">
                                <Input
                                    id="student_number"
                                    type="text"
                                    name="student_number"
                                    placeholder="ENTER YOUR STUDENT NUMBER"
                                    autoComplete="username"
                                    className="pr-10 py-6 pl-3 transition-all duration-200 placeholder:text-xs"
                                    required
                                    autoFocus
                                    tabIndex={5}
                                    pattern="[0-9]+"
                                    inputMode="numeric"
                                    onKeyPress={(e) => {
                                        if (!/[0-9]/.test(e.key)) {
                                            e.preventDefault();
                                        }
                                    }}
                                />
                                <Fingerprint className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground group-focus-within:text-green-500 transition-colors" />
                            </div>
                            <InputError message={errors.student_number} />

                            <Button
                                type="submit"
                                variant="outlinePrimary"
                                className="w-full"
                                tabIndex={1}
                                disabled={processing}
                                data-test="login-button"
                            >
                                LOGIN
                                {processing ? (
                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                ) : (
                                    <ArrowRight className="h-4 w-4" />
                                )}
                            </Button>
                        </div>
                    )}
                </Form>
                <div className="text-xs text-center text-gray-500">
                    Already voted?{' '}
                    <Link
                        href="/voters/receipt"
                        className="text-green-600 hover:text-green-800 font-medium transition-colors duration-200"
                    >
                        See your receipt here
                    </Link>
                </div>
            </div>
                    
            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </AuthLayout>
    );
}
