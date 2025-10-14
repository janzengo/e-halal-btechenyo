import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthLayout from '@/layouts/auth-layout-single';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { LoaderCircle, Search, Barcode } from 'lucide-react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Link } from '@inertiajs/react';

interface ReceiptLookupProps {
    errors?: {
        vote_ref?: string;
    };
}

export default function ReceiptLookup({ errors }: ReceiptLookupProps) {
    const [voteRef, setVoteRef] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!voteRef.trim()) {
            toast.error('Please enter a vote reference number');
            return;
        }

        // Validate format: VOTE-YYMMDD-XXXX
        const voteRefPattern = /^VOTE-\d{6}-\d{4}$/;
        if (!voteRefPattern.test(voteRef.trim())) {
            toast.error('Invalid vote reference format. Please use: VOTE-YYMMDD-XXXX');
            return;
        }

        setIsLoading(true);
        
        // Redirect to the receipt page with the vote reference
        router.visit(`/voters/receipt/${voteRef.trim()}`, {
            onFinish: () => setIsLoading(false),
            onError: () => {
                setIsLoading(false);
                toast.error('Receipt not found. Please check your vote reference number.');
            }
        });
    };

    return (
        <>
        <Head title="View Receipt" />
        <AuthLayout
            title="E-Halal BTECHenyo"
            description="A WEB-BASED ELECTION SYSTEM FOR DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG"
        >
            

            <div className="space-y-6">
                {/* Divider Line */}
                <div className="flex items-center mb-6">
                        <div className="flex-1 h-px bg-gray-300"></div>
                        <span className="px-3 text-xs text-gray-500 uppercase">ENTER VOTE REFERENCE NUMBER</span>
                        <div className="flex-1 h-px bg-gray-300"></div>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="relative group">
                        <div className="relative">
                            <Input
                                id="vote_reference"
                                type="text"
                                name="vote_reference"
                                value={voteRef}
                                onChange={e => setVoteRef(e.target.value.toUpperCase())}
                                placeholder="VOTE-YYMMDD-XXXX"
                                autoComplete="off"
                                className="pr-10 py-6 transition-all duration-200 uppercase placeholder:text-xs"
                                required
                                autoFocus
                                tabIndex={5}
                                maxLength={17}
                                pattern="^VOTE-\d{6}-\d{4}$"
                                inputMode="text"
                                disabled={isLoading}
                            />
                        </div>
                        <Barcode className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground group-focus-within:text-green-500 transition-colors" />
                    </div>
                    <InputError message={errors?.vote_ref} />

                    <Button
                        type="submit"
                        variant="outlinePrimary"
                        className="w-full"
                        tabIndex={1}
                        disabled={isLoading}
                        data-test="login-button"
                    >
                        VIEW RECEIPT
                        {isLoading ? (
                            <LoaderCircle className="h-4 w-4 animate-spin" />
                        ) : (
                            <Search className="h-4 w-4" />
                        )}
                    </Button>
                </form>
                <div className="text-xs text-center text-gray-500">
                    Back to {' '}
                    <Link
                        href="/auth/login"
                        className="text-green-600 hover:text-green-800 font-medium transition-colors duration-200"
                    >
                        Login
                    </Link>
                </div>
                </div>
            </AuthLayout>
        </>
    );
}
