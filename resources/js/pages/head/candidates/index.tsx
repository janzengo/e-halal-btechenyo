import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { CandidatesCards } from '@/components/@admin/@candidates/candidates-cards';
import { CandidatesSearch } from '@/components/@admin/@candidates/candidates-search';
import { Button } from '@/components/ui/button';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { CandidateDialog, CandidateFormData } from '@/components/@admin/@components/@dialogs/candidate';
import { CandidateViewDialog } from '@/components/@admin/@components/@dialogs/@view/candidate';
import { UserPlus, ContactRound } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonCards, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    position?: string;
    position_id: number;
    partylist?: string;
    partylist_id: number;
    photo?: string;
    platform?: string;
    votes: number;
    created_at?: string;
    updated_at?: string;
}

interface HeadCandidatesProps {
    candidates: Candidate[];
    positions: any[];
    partylists: any[];
}

export default function HeadCandidates() {
    const { candidates, positions, partylists } = usePage<HeadCandidatesProps>().props;
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const { isPageLoading } = useLoading();

    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [editingCandidate, setEditingCandidate] = useState<Candidate | null>(null);
    const [loading, setLoading] = useState(false);

    // View dialog state
    const [viewDialogOpen, setViewDialogOpen] = useState(false);
    const [selectedCandidate, setSelectedCandidate] = useState<Candidate | null>(null);

    const handleView = (candidate: Candidate) => {
        setSelectedCandidate(candidate);
        setViewDialogOpen(true);
    };

    const handleEdit = (candidate: Candidate) => {
        setEditingCandidate(candidate);
        setFormOpen(true);
    };

    const handleDelete = (candidate: Candidate) => {
        router.delete(`/head/candidates/${candidate.id}`, {
            preserveScroll: true,
            onError: (errors) => {
                toast.error('Failed to delete candidate');
                console.error('Delete errors:', errors);
            }
        });
    };

    const handleAddNew = () => {
        setEditingCandidate(null);
        setFormOpen(true);
    };

    const handleFormSubmit = async (data: CandidateFormData) => {
        setLoading(true);
        try {
            if (editingCandidate) {
                // Update existing candidate
                router.put(`/head/candidates/${editingCandidate.id}`, data, {
                    preserveScroll: true,
                    onSuccess: () => {
                        setFormOpen(false);
                        setEditingCandidate(null);
                    },
                    onError: (errors) => {
                        toast.error('Failed to update candidate');
                        console.error('Update errors:', errors);
                    },
                    onFinish: () => {
                        setLoading(false);
                    }
                });
            } else {
                // Create new candidate
                router.post('/head/candidates', data, {
                    preserveScroll: true,
                    onSuccess: () => {
                        setFormOpen(false);
                    },
                    onError: (errors) => {
                        toast.error('Failed to create candidate');
                        console.error('Create errors:', errors);
                    },
                    onFinish: () => {
                        setLoading(false);
                    }
                });
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            toast.error('An unexpected error occurred');
            setLoading(false);
        }
    };

    const handleFormClose = (open: boolean) => {
        if (!open) {
            setEditingCandidate(null);
        }
        setFormOpen(open);
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/candidates"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Candidates', href: '/head/candidates' },
            ]}
        >
            <Head title="Manage Candidates" />

            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Candidates</h2>
                        <p className="text-gray-600">Manage election candidates and their information</p>
                    </div>
                    <Button onClick={handleAddNew} variant="outlinePrimary">
                        <UserPlus className="h-4 w-4" />
                        Add New Candidate
                    </Button>
                </div>
            )}

            {isPageLoading ? (
                <SkeletonCards count={6} />
            ) : candidates.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <ContactRound />
                        </EmptyMedia>
                        <EmptyTitle>No Candidates Yet</EmptyTitle>
                        <EmptyDescription>
                            Start adding candidates for the election. Make sure positions are set up first.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={handleAddNew}>
                            <UserPlus className="h-4 w-4" />
                            Add First Candidate
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
                    {/* Search */}
                    <CandidatesSearch
                        searchTerm={searchTerm}
                        onSearchChange={(value) => {
                            setSearchTerm(value);
                            setCurrentPage(1);
                        }}
                    />

                    <div>
                        <CandidatesCards
                            candidates={candidates.map(c => ({
                                ...c,
                                status: 'active' as const
                            }))}
                            userRole="head"
                            onView={handleView}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            searchTerm={searchTerm}
                            currentPage={currentPage}
                            onPageChange={setCurrentPage}
                        />
                    </div>
                </>
            )}

            {/* Candidate Dialog */}
            <CandidateDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                candidate={editingCandidate}
                positions={positions}
                partylists={partylists}
                onSubmit={handleFormSubmit}
                loading={loading}
            />

            {/* Candidate View Dialog */}
            <CandidateViewDialog
                open={viewDialogOpen}
                onOpenChange={setViewDialogOpen}
                candidate={selectedCandidate}
            />
        </AdminLayout>
    );
}
