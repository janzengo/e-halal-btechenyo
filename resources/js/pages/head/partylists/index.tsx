import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { PartylistsCards } from '@/components/@admin/@partylists/partylists-cards';
import { PartylistsSearch } from '@/components/@admin/@partylists/partylists-search';
import { Button } from '@/components/ui/button';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { PartylistDialog, PartylistFormData } from '@/components/@admin/@components/@dialogs/partylist';
import { PartylistViewDialog } from '@/components/@admin/@components/@dialogs/@view/partylist';
import { Plus, Users } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonCards, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface Partylist {
    id: number;
    name: string;
    color: string;
    platform?: string;
    candidates_count: number;
    created_at?: string;
    updated_at?: string;
}

interface HeadPartylistsProps extends Record<string, any> {
    partylists: Partylist[];
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function HeadPartylists() {
    const { partylists, flash } = usePage<HeadPartylistsProps>().props;
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const { isPageLoading } = useLoading();
    
    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [editingPartylist, setEditingPartylist] = useState<Partylist | null>(null);

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
    const [selectedPartylist, setSelectedPartylist] = useState<Partylist | null>(null);
    const [candidates, setCandidates] = useState<any[]>([]);
    const [candidatesLoading, setCandidatesLoading] = useState(false);


    const handleEdit = (partylist: Partylist) => {
        setEditingPartylist(partylist);
        setFormOpen(true);
    };

    const handleDelete = (partylist: Partylist) => {
        router.delete(`/head/partylists/${partylist.id}`, {
            preserveScroll: true,
        });
    };

    const handleAddNew = () => {
        setEditingPartylist(null);
        setFormOpen(true);
    };

    const handleFormSubmit = async (data: PartylistFormData) => {
        if (editingPartylist) {
            // Update existing partylist
            router.put(`/head/partylists/${editingPartylist.id}`, data, {
                preserveScroll: true,
                onSuccess: () => {
                    setFormOpen(false);
                    setEditingPartylist(null);
                },
                onError: (errors) => {
                    toast.error(errors.name || 'Failed to update partylist');
                }
            });
        } else {
            // Create new partylist
            router.post('/head/partylists', data, {
                preserveScroll: true,
                onSuccess: () => {
                    setFormOpen(false);
                },
                onError: (errors) => {
                    toast.error(errors.name || 'Failed to create partylist');
                }
            });
        }
    };

    const handleFormClose = (open: boolean) => {
        if (!open) {
            setEditingPartylist(null);
        }
        setFormOpen(open);
    };

    const handleViewCandidates = async (partylist: Partylist) => {
        setSelectedPartylist(partylist);
        setViewDialogOpen(true);
        setCandidatesLoading(true);
        
        try {
            // Fetch candidates for this partylist
            const response = await fetch(`/head/partylists/${partylist.id}/candidates`);
            const data = await response.json();
            setCandidates(data.candidates || []);
        } catch (error) {
            console.error('Error fetching candidates:', error);
            setCandidates([]);
        } finally {
            setCandidatesLoading(false);
        }
    };

    const handleViewDialogClose = (open: boolean) => {
        if (!open) {
            setSelectedPartylist(null);
            setCandidates([]);
        }
        setViewDialogOpen(open);
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/partylists"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Partylists', href: '/head/partylists' },
            ]}
        >
            <Head title="Manage Partylists" />

            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Partylists</h2>
                        <p className="text-gray-600">Manage political parties and their information</p>
                    </div>
                    <Button onClick={handleAddNew} variant="outlinePrimary">
                        <Plus className="h-4 w-4" />
                        Add New Partylist
                    </Button>
                </div>
            )}

            {isPageLoading ? (
                <SkeletonCards count={6} />
            ) : partylists.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Users />
                        </EmptyMedia>
                        <EmptyTitle>No Partylists Yet</EmptyTitle>
                        <EmptyDescription>
                            Create partylists to organize candidates into political groups or organizations.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={handleAddNew}>
                            <Plus className="h-4 w-4" />
                            Create First Partylist
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
                    {/* Search */}
                    <PartylistsSearch
                        searchTerm={searchTerm}
                        onSearchChange={(value) => {
                            setSearchTerm(value);
                            setCurrentPage(1);
                        }}
                    />
                    
                    <div>
                        <PartylistsCards
                            partylists={partylists.map(p => ({
                                ...p,
                                status: 'active' as const,
                                created_at: p.created_at || new Date().toISOString(),
                                updated_at: p.updated_at || new Date().toISOString()
                            }))}
                            userRole="head"
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            onViewCandidates={handleViewCandidates}
                            searchTerm={searchTerm}
                            currentPage={currentPage}
                            onPageChange={setCurrentPage}
                        />
                    </div>
                </>
            )}

            {/* Partylist Dialog */}
            <PartylistDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                partylist={editingPartylist}
                onSubmit={handleFormSubmit}
            />

            {/* View Candidates Dialog */}
            <PartylistViewDialog
                open={viewDialogOpen}
                onOpenChange={handleViewDialogClose}
                partylist={selectedPartylist}
                candidates={candidates}
                loading={candidatesLoading}
            />
        </AdminLayout>
    );
}
