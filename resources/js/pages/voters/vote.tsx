import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LogOut, Eye, Send, RotateCcw, AlertCircle, Link } from 'lucide-react';
import { toast } from 'sonner';
import HorizontalNavbar from '@/components/horizontal-navbar';
import { PlatformDialog, PositionCard } from '@/components/@voters';

interface Candidate {
    id: number;
    name: string;
    party: string;
    photo?: string;
    platform: string;
}

interface Position {
    id: number;
    name: string;
    max_vote: number;
    candidates: Candidate[];
}

interface VotePageProps {
    auth: {
        user: {
            student_number: string;
            name: string;
        };
    };
    positions: Position[];
    election: {
        id: number;
        name: string;
    };
    has_voted: boolean;
    vote_ref?: string;
}

export default function VotePage({ auth, positions, election, has_voted, vote_ref }: VotePageProps) {
    const [selectedVotes, setSelectedVotes] = useState<Record<number, number[]>>({});
    const [showPreview, setShowPreview] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [showPlatformDialog, setShowPlatformDialog] = useState(false);
    const [selectedCandidate, setSelectedCandidate] = useState<Candidate | null>(null);

    const handleCandidateSelect = (positionId: number, candidateId: number, maxVote: number) => {
        setSelectedVotes((prev) => {
            const currentVotes = prev[positionId] || [];
            
            if (maxVote === 1) {
                // Radio behavior - single selection
                return { ...prev, [positionId]: [candidateId] };
            } else {
                // Checkbox behavior - multiple selection
                if (currentVotes.includes(candidateId)) {
                    return { ...prev, [positionId]: currentVotes.filter(id => id !== candidateId) };
                } else if (currentVotes.length < maxVote) {
                    return { ...prev, [positionId]: [...currentVotes, candidateId] };
                }
                return prev;
            }
        });
    };

    const handleReset = (positionId: number) => {
        setSelectedVotes((prev) => {
            const newVotes = { ...prev };
            delete newVotes[positionId];
            return newVotes;
        });
    };

    const handlePreview = () => {
        const hasVotes = Object.values(selectedVotes).some(votes => votes.length > 0);
        
        if (!hasVotes) {
            toast.warning('No Votes Selected', {
                description: 'Please select at least one candidate before previewing.',
            });
            return;
        }

        setShowPreview(true);
    };

    const handleSubmitClick = () => {
        const hasVotes = Object.values(selectedVotes).some(votes => votes.length > 0);
        
        if (!hasVotes) {
            toast.error('No Votes Cast', {
                description: 'Please select at least one candidate before submitting.',
            });
            return;
        }

        setShowConfirmDialog(true);
    };

    const handleConfirmSubmit = () => {
        setShowConfirmDialog(false);
        toast.promise(
            new Promise((resolve) => {
                router.post('/voters/vote/submit', { votes: selectedVotes }, {
                    preserveState: false,
                    preserveScroll: false,
                    onSuccess: () => resolve(true),
                });
            }),
            {
                loading: 'Submitting your votes...',
                success: 'Votes submitted successfully!',
                error: 'Failed to submit votes. Please try again.',
            }
        );
    };

    const handleLogout = () => {
        router.post('/logout');
    };

    const showPlatform = (candidate: Candidate) => {
        setSelectedCandidate(candidate);
        setShowPlatformDialog(true);
    };

    const getCandidateById = (candidateId: number): Candidate | undefined => {
        for (const position of positions) {
            const candidate = position.candidates.find(c => c.id === candidateId);
            if (candidate) return candidate;
        }
        return undefined;
    };

    if (has_voted && vote_ref) {
        return (
            <>
                <Head title="Thank You for Voting" />
                
                <HorizontalNavbar handleLogout={handleLogout} />

                {/* Success Content */}
                <div className="flex min-h-[calc(100vh-4rem)] items-center justify-center p-4">
                    <Card className="w-full max-w-lg">
                        <CardContent className="p-8 text-center">
                            <div className="mb-6 flex justify-center">
                                <div className="flex h-24 w-24 items-center justify-center rounded-full bg-green-500 shadow-lg">
                                    <svg
                                        className="h-12 w-12 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={3}
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>
                            </div>
                            
                            <h2 className="mb-4 text-2xl font-semibold text-gray-900">
                                Thank You for Voting!
                            </h2>
                            
                            <div className="mb-6 space-y-2">
                                <p className="text-sm text-gray-600">Reference Number:</p>
                                <p className="text-xl font-semibold text-green-600">{vote_ref}</p>
                                <p className="text-sm text-gray-600">
                                    Your vote has been recorded successfully and a receipt has been sent to your email.
                                </p>
                            </div>
                            
                            <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                                <Button asChild className="bg-green-600 hover:bg-green-700">
                                    <a href={`/voters/receipt/${vote_ref}`}>
                                        <Eye className="h-4 w-4" />
                                        View Receipt
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title={`Vote - ${election.name}`} />
            
            <HorizontalNavbar handleLogout={handleLogout} />

            {/* Main Content */}
            <div className="min-h-screen bg-gray-50 pb-24">
                <div className="container mx-auto px-4 py-8">
                    <h1 className="mb-8 text-center text-3xl font-bold text-gray-900">
                        {election.name}
                    </h1>

                    {/* Preview Modal */}
                    <Dialog open={showPreview} onOpenChange={setShowPreview}>
                        <DialogContent className="max-h-[80vh] overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle>Review Your Votes</DialogTitle>
                                <DialogDescription>
                                    Please review your selections before submitting.
                                </DialogDescription>
                            </DialogHeader>
                            <div className="space-y-4 py-4">
                                {positions.map((position) => {
                                    const votes = selectedVotes[position.id] || [];
                                    if (votes.length === 0) return null;

                                    return (
                                        <div key={position.id} className="border-b pb-4 last:border-0">
                                            <h3 className="mb-2 font-semibold text-gray-900">{position.name}</h3>
                                            <ul className="space-y-1">
                                                {votes.map((candidateId) => {
                                                    const candidate = getCandidateById(candidateId);
                                                    return candidate ? (
                                                        <li key={candidateId} className="text-sm text-gray-700">
                                                            • {candidate.name} ({candidate.party})
                                                        </li>
                                                    ) : null;
                                                })}
                                            </ul>
                                        </div>
                                    );
                                })}
                            </div>
                            <DialogFooter>
                                <Button onClick={() => setShowPreview(false)} variant="outline">
                                    Back
                                </Button>
                                <Button onClick={handleSubmitClick} className="bg-green-600 hover:bg-green-700">
                                    <Send className="h-4 w-4" />
                                    Submit Votes
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    {/* Confirm Submit Dialog */}
                    <Dialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle className="flex items-center gap-2">
                                    <AlertCircle className="h-5 w-5 text-orange-500" />
                                    Submit Votes?
                                </DialogTitle>
                                <DialogDescription>
                                    Are you sure you want to submit your votes? This action cannot be undone.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <Button onClick={() => setShowConfirmDialog(false)} variant="outline">
                                    Cancel
                                </Button>
                                <Button onClick={handleConfirmSubmit} className="bg-green-600 hover:bg-green-700">
                                    Confirm
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    {/* Platform Dialog */}
                    <PlatformDialog
                        candidate={selectedCandidate}
                        isOpen={showPlatformDialog}
                        onClose={() => setShowPlatformDialog(false)}
                    />

                    {/* Positions and Candidates */}
                    <div className="space-y-6">
                        {positions.map((position) => (
                            <PositionCard
                                key={position.id}
                                position={position}
                                selectedCandidates={selectedVotes[position.id] || []}
                                onCandidateSelect={handleCandidateSelect}
                                onReset={handleReset}
                                onViewPlatform={showPlatform}
                            />
                        ))}
                    </div>
                </div>
                
                <div className="container mx-auto flex justify-center gap-3">
                    <Button onClick={handlePreview} variant="outline">
                            <Eye className="h-4 w-4" />
                            Preview
                        </Button>
                        <Button onClick={handleSubmitClick} className="bg-green-600 hover:bg-green-700">
                            <Send className="h-4 w-4" />
                            Submit Votes
                    </Button>
                </div>
            </div>

            {/* Sticky Action Buttons */}
            <div className="bottom-0 left-0 right-0 border-t bg-white p-3 shadow-lg">
                <div className="container mx-auto flex justify-center gap-3">
                    <p className="text-sm text-gray-500">Copyright © 2025 E-Halal BTECHenyo. All rights reserved.</p>
                </div>
            </div>
        </>
    );
}
