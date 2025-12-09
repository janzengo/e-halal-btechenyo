import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { PositionsCards } from '@/components/@admin/@positions/positions-cards';
import { PositionsSearch } from '@/components/@admin/@positions/positions-search';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

// Dummy positions data - replace with actual data from backend
const dummyPositions = [
    {
        id: 1,
        title: 'President',
        description: 'The highest executive position in the student government',
        max_winners: 1,
        candidates_count: 3,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 2,
        title: 'Vice President',
        description: 'Assists the President in executive functions',
        max_winners: 1,
        candidates_count: 2,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 3,
        title: 'Secretary',
        description: 'Handles documentation and record keeping',
        max_winners: 1,
        candidates_count: 4,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 4,
        title: 'Treasurer',
        description: 'Manages financial affairs of the organization',
        max_winners: 1,
        candidates_count: 2,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
];

export default function OfficerPositions() {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);

    const handleView = (position: any) => {
        console.log('View position:', position);
        // Implement view logic
    };

    const handleEdit = (position: any) => {
        console.log('Edit position:', position);
        // Implement edit logic
    };

    const handleDelete = (position: any) => {
        console.log('Delete position:', position);
        // Implement delete logic
    };

    const handleAddNew = () => {
        console.log('Add new position');
        // Implement add new logic
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/positions"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Positions', href: '/officers/positions' },
            ]}
        >
            <Head title="Manage Positions" />

            {/* Header with Add Button */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Positions</h2>
                    <p className="text-gray-600">Manage available positions for the election</p>
                </div>
                <Button onClick={handleAddNew} variant="outlinePrimary">
                    <Plus className="h-4 w-4" />
                    Add New Position
                </Button>
            </div>

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
                    positions={dummyPositions}
                    userRole="officer"
                    onView={handleView}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    searchTerm={searchTerm}
                    currentPage={currentPage}
                    onPageChange={setCurrentPage}
                />
            </div>
        </AdminLayout>
    );
}
