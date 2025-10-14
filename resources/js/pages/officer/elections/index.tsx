import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { HistoryCards } from '@/components/@admin/@history/history-cards';

// Dummy elections data - replace with actual data from backend
const dummyElections = [
    {
        id: 1,
        title: 'Student Council Election 2024',
        description: 'Annual election for student council positions including President, Vice President, Secretary, and Treasurer.',
        start_date: '2024-01-15T08:00:00Z',
        end_date: '2024-01-20T17:00:00Z',
        status: 'completed' as const,
        total_voters: 1500,
        votes_cast: 1200,
        positions_count: 8,
        candidates_count: 24,
        created_at: '2024-01-01T00:00:00Z',
        completed_at: '2024-01-20T17:00:00Z',
    },
    {
        id: 2,
        title: 'Student Council Election 2023',
        description: 'Annual election for student council positions with focus on campus development and student welfare.',
        start_date: '2023-01-15T08:00:00Z',
        end_date: '2023-01-20T17:00:00Z',
        status: 'completed' as const,
        total_voters: 1400,
        votes_cast: 1150,
        positions_count: 6,
        candidates_count: 18,
        created_at: '2023-01-01T00:00:00Z',
        completed_at: '2023-01-20T17:00:00Z',
    },
    {
        id: 3,
        title: 'Student Council Election 2022',
        description: 'Annual election for student council positions during the pandemic recovery period.',
        start_date: '2022-01-15T08:00:00Z',
        end_date: '2022-01-20T17:00:00Z',
        status: 'completed' as const,
        total_voters: 1200,
        votes_cast: 980,
        positions_count: 5,
        candidates_count: 15,
        created_at: '2022-01-01T00:00:00Z',
        completed_at: '2022-01-20T17:00:00Z',
    },
    {
        id: 4,
        title: 'Student Council Election 2025',
        description: 'Upcoming annual election for student council positions with new policies and procedures.',
        start_date: '2025-01-15T08:00:00Z',
        end_date: '2025-01-20T17:00:00Z',
        status: 'upcoming' as const,
        total_voters: 1600,
        votes_cast: 0,
        positions_count: 8,
        candidates_count: 0,
        created_at: '2024-12-01T00:00:00Z',
    },
];

export default function OfficerElections() {
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
            userRole="officer"
            currentPath="/officer/elections"
            breadcrumbs={[
                { title: 'Dashboard', href: '/officer/dashboard' },
                { title: 'Election History', href: '/officer/elections' },
            ]}
        >
            <Head title="Election History" />
            
            <div>
                <HistoryCards
                    elections={dummyElections}
                    userRole="officer"
                    onView={handleView}
                    onExport={handleExport}
                />
            </div>
        </AdminLayout>
    );
}
