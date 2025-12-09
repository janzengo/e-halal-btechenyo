import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { HistoryCards } from '@/components/@admin/@history/history-cards';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { FolderClock } from 'lucide-react';

interface BackendElection {
    id: number;
    title: string;
    status: string;
    end_time?: string;
    control_number: string;
    details_pdf?: string;
    results_pdf?: string;
    created_at?: string;
}

interface Election {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    status: 'completed' | 'active' | 'pending' | 'setup';
    total_voters: number;
    votes_cast: number;
    positions_count: number;
    candidates_count: number;
    created_at: string;
    completed_at?: string;
}

interface ElectionsProps extends Record<string, any> {
    elections: BackendElection[];
}

export default function HeadElections() {
    const { elections } = usePage<ElectionsProps>().props;
    const handleView = (election: any) => {
        console.log('View election:', election);
        // Implement view logic
    };

    const handleExport = (election: any) => {
        console.log('Export election results:', election);
        // Implement export logic
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/elections"
            breadcrumbs={[
                { title: 'Reports & Analytics', href: '#' },
                { title: 'Election History', href: '/head/elections' },
            ]}
        >
            <Head title="Election History" />
            
            {/* Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Election History</h2>
                    <p className="text-gray-600">View past elections and their results</p>
                </div>
            </div>

            {elections.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <FolderClock />
                        </EmptyMedia>
                        <EmptyTitle>No Election History</EmptyTitle>
                        <EmptyDescription>
                            Past elections will be archived here. Once an election is completed, it will appear in this history.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            ) : (
                <div>
                    <HistoryCards
                        elections={elections.map((e): Election => ({
                            id: e.id,
                            title: e.title,
                            description: `Control Number: ${e.control_number}`,
                            start_date: e.created_at || new Date().toISOString(),
                            end_date: e.end_time || new Date().toISOString(),
                            status: e.status as 'completed' | 'active' | 'pending' | 'setup',
                            total_voters: 0,
                            votes_cast: 0,
                            positions_count: 0,
                            candidates_count: 0,
                            created_at: e.created_at || new Date().toISOString(),
                            completed_at: e.end_time
                        }))}
                        userRole="head"
                        onView={handleView}
                        onExport={handleExport}
                    />
                </div>
            )}
        </AdminLayout>
    );
}
