import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { OfficersCards } from '@/components/@admin/@officers/officers-cards';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { Download, ShieldCheck, UserPlus } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonCards, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';
import { OfficerDialog, OfficerFormData } from '@/components/@admin/@components/@dialogs/officer';

interface Officer {
    id: number;
    username: string;
    email: string;
    firstname: string;
    lastname: string;
    photo?: string;
    role: string;
    gender?: string;
    created_at?: string;
}

interface OfficersProps extends Record<string, any> {
    officers: Officer[];
    flash?: {
        success?: string;
        error?: string;
    };
}


export default function HeadOfficers() {
    const { officers, flash } = usePage<OfficersProps>().props;
    const { isPageLoading } = useLoading();
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    
    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    // Handle flash messages
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const handleAddNew = () => {
        setFormOpen(true);
    };

    const handleFormSubmit = async (data: OfficerFormData) => {
        setLoading(true);
        try {
            // Create new officer - no password, will be set via email verification
            router.post('/head/officers', data as any, {
                preserveScroll: true,
                onSuccess: () => {
                    setFormOpen(false);
                },
                onError: (errors) => {
                    toast.error('Failed to create officer');
                    console.error('Create errors:', errors);
                },
                onFinish: () => {
                    setLoading(false);
                }
            });
        } catch (error) {
            console.error('Error submitting form:', error);
            toast.error('An unexpected error occurred');
            setLoading(false);
        }
    };

    const handleFormClose = (open: boolean) => {
        setFormOpen(open);
    };

    const handleToggleStatus = (officer: Officer) => {
        console.log('Toggle status for officer:', officer);
        // Implement toggle status logic later
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/officers"
            breadcrumbs={[
                { title: 'Administration', href: '/head/dashboard' },
                { title: 'Officers', href: '/head/officers' },
            ]}
        >
            <Head title="Manage Officers" />
            
            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Officers Management</h2>
                        <p className="text-gray-600">Manage election officers and their permissions</p>
                    </div>
                    {officers.length > 0 && (
                        <Button onClick={handleAddNew} variant="outlinePrimary">
                            <UserPlus className="h-4 w-4" />
                            Add New Officer
                        </Button>
                    )}
                </div>
            )}

            {isPageLoading ? (
                <SkeletonCards count={6} />
            ) : officers.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <ShieldCheck />
                        </EmptyMedia>
                        <EmptyTitle>No Officers Added</EmptyTitle>
                        <EmptyDescription>
                            Add officers to help manage the election system. Officers can assist with voter management and monitoring.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Link href="/head/officers/create">
                            <Button variant="outlinePrimary">
                                <UserPlus className="h-4 w-4" />
                                Add First Officer
                            </Button>
                        </Link>
                    </EmptyContent>
                </Empty>
            ) : (
                <div>
                    {/* Officers Cards */}
                    <OfficersCards
                        officers={officers.map(o => ({
                            id: o.id,
                            username: o.username,
                            email: o.email,
                            firstname: o.firstname,
                            lastname: o.lastname,
                            photo: o.photo || '/images/profile.jpg',
                            role: 'officer' as const,
                            status: 'active' as const,
                            last_login: o.created_at || new Date().toISOString(),
                            created_at: o.created_at || new Date().toISOString()
                        }))}
                        userRole="head"
                        onAddNew={handleAddNew}
                        onToggleStatus={handleToggleStatus}
                    />
                </div>
            )}

            {/* Officer Dialog - Create Only */}
            <OfficerDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                onSubmit={handleFormSubmit}
                loading={loading}
            />
        </AdminLayout>
    );
}