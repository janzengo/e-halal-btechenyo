import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { VotersTable } from '@/components/@admin/@voters/voters-table';
import { VotersStatisticsCard } from '@/components/@admin/@voters/voters-statistics-card';
import { VotersSearch } from '@/components/@admin/@voters/voters-search';
import { Button } from '@/components/ui/button';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { VoterDialog, VoterFormData } from '@/components/@admin/@components/@dialogs/voter';
import { VoterImportDialog } from '@/components/@admin/@components/@dialogs/voter-import';
import { UserPlus, UserCheck, Upload, Download } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonTable, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface Voter {
    id: number;
    student_number: string;
    course_id: number;
    course?: string; // From relationship
    has_voted: boolean;
    created_at: string;
    updated_at: string;
}

interface HeadVotersProps extends Record<string, any> {
    voters: Voter[];
    courses: any[];
    totalVoters: number;
    votedCount: number;
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function HeadVoters() {
    const { voters, totalVoters, votedCount, courses, flash } = usePage<HeadVotersProps>().props;
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const { isPageLoading } = useLoading();
    
    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [importOpen, setImportOpen] = useState(false);
    const [editingVoter, setEditingVoter] = useState<Voter | null>(null);
    const [loading, setLoading] = useState(false);
    const [bulkDeleteLoading, setBulkDeleteLoading] = useState(false);
    const [downloadingTemplate, setDownloadingTemplate] = useState(false);

    // Handle flash messages
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);



    const handleEdit = (voter: Voter) => {
        setEditingVoter(voter);
        setFormOpen(true);
    };

    const handleAddNew = () => {
        setEditingVoter(null);
        setFormOpen(true);
    };

    const handleDelete = (voter: Voter) => {
        if (voter.has_voted) {
            toast.error('Cannot delete voter who has already voted');
            return;
        }

        router.delete(`/head/voters/${voter.id}`, {
            preserveScroll: true,
        });
    };

    const handleBulkDelete = (voterIds: number[]) => {
        if (voterIds.length === 0) {
            toast.error('No voters selected for deletion');
            return;
        }

        setBulkDeleteLoading(true);
        router.post('/head/voters/bulk-delete', {
            voter_ids: voterIds
        }, {
            preserveScroll: true,
            // Note: Success toast is handled by flash message in useEffect
            onError: (errors) => {
                toast.error('Failed to delete selected voters');
                console.error('Bulk delete errors:', errors);
            },
            onFinish: () => {
                setBulkDeleteLoading(false);
            }
        });
    };

    const handleFormSubmit = async (data: VoterFormData) => {
        setLoading(true);
        try {
            if (editingVoter) {
                // Update existing voter
                router.put(`/head/voters/${editingVoter.id}`, data as any, {
                    preserveScroll: true,
                    onSuccess: () => {
                        setFormOpen(false);
                        setEditingVoter(null);
                    },
                    onError: (errors) => {
                        toast.error('Failed to update voter');
                        console.error('Update errors:', errors);
                    },
                    onFinish: () => {
                        setLoading(false);
                    }
                });
            } else {
                // Create new voter
                router.post('/head/voters', data as any, {
                    preserveScroll: true,
                    onSuccess: () => {
                        setFormOpen(false);
                    },
                    onError: (errors) => {
                        toast.error('Failed to register voter');
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
            setEditingVoter(null);
        }
        setFormOpen(open);
    };

    const handleImportClick = () => {
        setImportOpen(true);
    };

    const handleDownloadTemplate = async () => {
        setDownloadingTemplate(true);
        
        try {
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = '/head/voters/template/download';
            link.download = 'voters_template.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Add a small delay to show the spinner
            setTimeout(() => {
                setDownloadingTemplate(false);
            }, 1500);
        } catch (error) {
            console.error('Download error:', error);
            setDownloadingTemplate(false);
        }
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/voters"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Voters', href: '/head/voters' },
            ]}
        >
            <Head title="Manage Voters" />

            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Voters</h2>
                        <p className="text-gray-600">Manage registered voters and their voting status</p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Button 
                            onClick={handleDownloadTemplate} 
                            variant="outline" 
                            disabled={downloadingTemplate}
                            className="border-blue-200 text-blue-700 hover:bg-blue-50 disabled:opacity-50"
                        >
                            {downloadingTemplate ? (
                                <>
                                    <Spinner className="h-4 w-4 mr-2" />
                                    Downloading...
                                </>
                            ) : (
                                <>
                                    <Download className="h-4 w-4 mr-2" />
                                    Download Template
                                </>
                            )}
                        </Button>
                        <Button onClick={handleImportClick} variant="outline" className="border-green-200 text-green-700 hover:bg-green-50">
                            <Upload className="h-4 w-4" />
                            Import Voters
                        </Button>
                        <Button onClick={handleAddNew} variant="outlinePrimary">
                            <UserPlus className="h-4 w-4" />
                            Add New Voter
                        </Button>
                    </div>
                </div>
            )}

            {isPageLoading ? (
                <div className="space-y-6">
                    <SkeletonTable rows={10} />
                </div>
            ) : voters.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <UserCheck />
                        </EmptyMedia>
                        <EmptyTitle>No Voters Registered</EmptyTitle>
                        <EmptyDescription>
                            Import or add voters to get started. Voters need to be registered before they can participate in the election.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <div className="flex gap-3">
                            <Button onClick={handleImportClick} variant="outlinePrimary">
                                <Upload className="h-4 w-4" />
                                Import Voters
                            </Button>
                            <Button onClick={handleAddNew} variant="outline">
                                <UserPlus className="h-4 w-4" />
                                Add Single Voter
                            </Button>
                        </div>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
                    {/* Statistics Cards */}
                    <VotersStatisticsCard voters={voters} />

                    {/* Search */}
                    <VotersSearch
                        searchTerm={searchTerm}
                        onSearchChange={setSearchTerm}
                    />
                    
                    <div>
                        <VotersTable
                            voters={voters}
                            userRole="head"
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            onBulkDelete={handleBulkDelete}
                            searchTerm={searchTerm}
                            bulkDeleteLoading={bulkDeleteLoading}
                        />
                    </div>
                </>
            )}

            {/* Voter Dialog */}
            <VoterDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                voter={editingVoter}
                courses={courses}
                onSubmit={handleFormSubmit}
                loading={loading}
            />

            {/* Import Dialog */}
            <VoterImportDialog
                open={importOpen}
                onOpenChange={setImportOpen}
            />
        </AdminLayout>
    );
}
