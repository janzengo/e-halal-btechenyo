import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { PositionsCards } from '@/components/@admin/@positions/positions-cards';
import { PositionsSearch } from '@/components/@admin/@positions/positions-search';
import { Button } from '@/components/ui/button';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { PositionDialog, PositionFormData } from '@/components/@admin/@components/@dialogs/position';
import { PositionViewDialog } from '@/components/@admin/@components/@dialogs/@view/position';
import { Plus, ListChecks, ListPlus } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonCards, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface Position {
    id: number;
    title: string;
    max_winners: number;
    priority: number;
    candidates_count: number;
    created_at?: string;
    updated_at?: string;
}

interface HeadPositionsProps extends Record<string, any> {
    positions: Position[];
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function HeadPositions() {
    const { positions, flash } = usePage<HeadPositionsProps>().props;
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const { isPageLoading } = useLoading();
    
    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [editingPosition, setEditingPosition] = useState<Position | null>(null);

    // Handle flash messages
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    // View candidates state
    const [viewDialogOpen, setViewDialogOpen] = useState(false);
    const [selectedPosition, setSelectedPosition] = useState<Position | null>(null);
    const [candidates, setCandidates] = useState<any[]>([]);
    const [candidatesLoading, setCandidatesLoading] = useState(false);

    const handleViewCandidates = async (position: Position) => {
        setSelectedPosition(position);
        setViewDialogOpen(true);
        setCandidatesLoading(true);

        try {
            const response = await fetch(`/head/positions/${position.id}/candidates`);
            const data = await response.json();
            setCandidates(data);
        } catch (error) {
            console.error('Error fetching candidates:', error);
            toast.error('Failed to load candidates');
        } finally {
            setCandidatesLoading(false);
        }
    };

    const handleEdit = (position: Position) => {
        setEditingPosition(position);
        setFormOpen(true);
    };

    const handleDelete = (position: Position) => {
        router.delete(`/head/positions/${position.id}`, {
            preserveScroll: true,
        });
    };

    const handleAddNew = () => {
        setEditingPosition(null);
        setFormOpen(true);
    };

    const handleFormSubmit = async (data: PositionFormData) => {
        if (editingPosition) {
            // Update existing position
            router.put(`/head/positions/${editingPosition.id}`, data, {
                preserveScroll: true,
                onSuccess: () => {
                    setFormOpen(false);
                    setEditingPosition(null);
                },
                onError: (errors) => {
                    toast.error(errors.title || 'Failed to update position');
                }
            });
        } else {
            // Create new position
            router.post('/head/positions', data, {
                preserveScroll: true,
                onSuccess: () => {
                    setFormOpen(false);
                },
                onError: (errors) => {
                    toast.error(errors.title || 'Failed to create position');
                }
            });
        }
    };

    const handleFormClose = (open: boolean) => {
        if (!open) {
            setEditingPosition(null);
        }
        setFormOpen(open);
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/positions"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Positions', href: '/head/positions' },
            ]}
        >
            <Head title="Manage Positions" />

            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Positions</h2>
                        <p className="text-gray-600">Manage available positions for the election</p>
                    </div>
                    <Button onClick={handleAddNew} variant="outlinePrimary">
                        <ListPlus className="h-4 w-4" />
                        Add New Position
                    </Button>
                </div>
            )}

            {isPageLoading ? (
                <SkeletonCards count={6} />
            ) : positions.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <ListChecks />
                        </EmptyMedia>
                        <EmptyTitle>No Positions Yet</EmptyTitle>
                        <EmptyDescription>
                            Get started by creating your first position for the election.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={handleAddNew}>
                            <ListPlus className="h-4 w-4" />
                            Create First Position
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
                    {/* Search */}
                    <PositionsSearch
                        searchTerm={searchTerm}
                        onSearchChange={(value) => {
                            setSearchTerm(value);
                            setCurrentPage(1);
                        }}
                    />
                    
                    <div>
                        <PositionsCards
                            positions={positions.map(p => ({
                                ...p,
                                description: p.title,
                                status: 'active' as const,
                                created_at: p.created_at || new Date().toISOString(),
                                updated_at: p.updated_at || new Date().toISOString()
                            }))}
                            userRole="head"
                            onViewCandidates={handleViewCandidates}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            searchTerm={searchTerm}
                            currentPage={currentPage}
                            onPageChange={setCurrentPage}
                        />
                    </div>
                </>
            )}

            {/* Position Dialog */}
            <PositionDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                position={editingPosition}
                onSubmit={handleFormSubmit}
            />

            {/* View Candidates Dialog */}
            <PositionViewDialog
                open={viewDialogOpen}
                onOpenChange={setViewDialogOpen}
                position={selectedPosition}
                candidates={candidates}
                loading={candidatesLoading}
            />
        </AdminLayout>
    );
}
