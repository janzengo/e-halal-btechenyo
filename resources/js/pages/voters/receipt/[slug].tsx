import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Download, Printer } from 'lucide-react';

interface VoteItem {
    position: string;
    candidate: string;
    party: string;
}

interface ReceiptPageProps {
    vote_ref: string;
    election: {
        name: string;
        date: string;
    };
    voter: {
        student_number: string;
        name: string;
        course: string;
    };
    votes: VoteItem[];
    timestamp: string;
}

export default function ReceiptPage({ vote_ref, election, voter, votes, timestamp }: ReceiptPageProps) {
    const handleDownloadPDF = () => {
        window.location.href = `/voters/receipt/${vote_ref}/download`;
    };

    const handlePrint = () => {
        window.print();
    };

    const handleBack = () => {
        router.visit('/voters/dashboard');
    };

    return (
        <>
            <Head title={`Receipt - ${vote_ref}`} />
            
            <div className="min-h-screen bg-gray-50 py-8">
                <div className="container mx-auto px-4">
                    {/* Action Buttons */}
                    <div className="mb-6 flex flex-wrap gap-3 print:hidden">
                        <Button onClick={handleBack} variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back
                        </Button>
                        <Button onClick={handleDownloadPDF} className="bg-green-600 hover:bg-green-700">
                            <Download className="mr-2 h-4 w-4" />
                            Download PDF
                        </Button>
                        <Button onClick={handlePrint} variant="outline">
                            <Printer className="mr-2 h-4 w-4" />
                            Print
                        </Button>
                    </div>

                    {/* Receipt Card */}
                    <Card className="mx-auto max-w-3xl">
                        <CardHeader className="space-y-4 border-b bg-white">
                            <div className="flex items-center justify-center gap-6">
                                <img 
                                    src="/images/logos/logo.png" 
                                    alt="Logo" 
                                    className="h-12 w-12 object-contain" 
                                />
                                <div className="text-center">
                                    <h1 className="text-lg font-bold text-gray-900">E-Halal BTECHenyo</h1>
                                    <p className="text-xs text-gray-600">Dalubhasaang Politekniko ng Lungsod ng Baliwag</p>
                                </div>
                                <img 
                                    src="/images/logos/btech-logo.png" 
                                    alt="BTECH Logo" 
                                    className="h-16 w-16 object-contain" 
                                />
                            </div>
                            <div className="text-center">
                                <CardTitle className="text-lg">Vote Receipt</CardTitle>
                                <p className="mt-1 text-xs text-gray-600">Official Voting Record</p>
                            </div>
                        </CardHeader>

                        <CardContent className="space-y-6 p-6">
                            {/* Reference Number */}
                            <div className="rounded-lg bg-green-50 p-4 text-center">
                                <p className="text-sm font-medium text-gray-700">Reference Number</p>
                                <p className="mt-1 text-2xl font-bold text-green-600">{vote_ref}</p>
                            </div>

                            {/* Election Info */}
                            <div className="space-y-3">
                                <h3 className="font-semibold text-gray-900">Election Information</h3>
                                <div className="grid gap-3 rounded-lg bg-gray-50 p-4 sm:grid-cols-2">
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Election Name</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">{election.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Date</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">
                                            {new Date(election.date).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            })}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Voter Info */}
                            <div className="space-y-3">
                                <h3 className="font-semibold text-gray-900">Voter Information</h3>
                                <div className="grid gap-3 rounded-lg bg-gray-50 p-4 sm:grid-cols-2">
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Student Number</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">{voter.student_number}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Name</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">{voter.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Course</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">{voter.course}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-gray-500">Vote Timestamp</p>
                                        <p className="mt-1 text-sm font-medium text-gray-900">
                                            {new Date(timestamp).toLocaleString('en-US', {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Votes Cast */}
                            <div className="space-y-3">
                                <h3 className="font-semibold text-gray-900">Votes Cast</h3>
                                <div className="divide-y rounded-lg border">
                                    {votes.map((vote, index) => (
                                        <div key={index} className="p-4">
                                            <p className="font-medium text-green-700">{vote.position}</p>
                                            <p className="mt-1 text-sm text-gray-900">{vote.candidate}</p>
                                            <p className="text-xs text-gray-600">{vote.party}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Verification Notice */}
                            <div className="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4">
                                <div className="flex gap-3">
                                    <div className="flex-shrink-0">
                                        <svg
                                            className="h-6 w-6 text-gray-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium text-gray-900">Verification Notice</h4>
                                        <p className="mt-1 text-sm text-gray-600">
                                            This is your official voting receipt. Please keep this reference number ({vote_ref}) 
                                            for your records. Your vote has been securely recorded and cannot be changed.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="border-t pt-4 text-center text-xs text-gray-500">
                                <p>This is a computer-generated receipt. No signature required.</p>
                                <p className="mt-1">
                                    Generated on {new Date().toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Print Styles */}
            <style>{`
                @media print {
                    body {
                        background: white !important;
                    }
                    .print\\:hidden {
                        display: none !important;
                    }
                    @page {
                        margin: 1cm;
                    }
                }
            `}</style>
        </>
    );
}

