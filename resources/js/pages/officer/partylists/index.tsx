import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { PartylistsCards } from '@/components/@admin/@partylists/partylists-cards';
import { PartylistsSearch } from '@/components/@admin/@partylists/partylists-search';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

// Dummy partylists data - replace with actual data from backend
const dummyPartylists = [
    {
        id: 1,
        name: 'Kabataan ng Baliwag',
        acronym: 'KABATAAN',
        color: '#3B82F6',
        description: 'A youth-focused political organization dedicated to student welfare and campus development.',
        candidates_count: 8,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 2,
        name: 'Pag-asa para sa Bayan',
        acronym: 'PAG-ASA',
        color: '#10B981',
        description: 'A progressive political party committed to transparency and student empowerment.',
        candidates_count: 6,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 3,
        name: 'Lakas ng Mag-aaral',
        acronym: 'LAKAS',
        color: '#F59E0B',
        description: 'A student organization focused on academic excellence and campus leadership.',
        candidates_count: 4,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 4,
        name: 'Bayanihan ng Kabataan',
        acronym: 'BAYANIHAN',
        color: '#EF4444',
        description: 'A community-oriented political party promoting unity and cooperation among students.',
        candidates_count: 5,
        status: 'inactive' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
];

export default function OfficerPartylists() {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);

    const handleView = (partylist: any) => {
        console.log('View partylist:', partylist);
        // Implement view logic
    };

    const handleEdit = (partylist: any) => {
        console.log('Edit partylist:', partylist);
        // Implement edit logic
    };

    const handleDelete = (partylist: any) => {
        console.log('Delete partylist:', partylist);
        // Implement delete logic
    };

    const handleAddNew = () => {
        console.log('Add new partylist');
        // Implement add new logic
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/partylists"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Partylists', href: '/officers/partylists' },
            ]}
        >
            <Head title="Manage Partylists" />

            {/* Header with Add Button */}
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
                    partylists={dummyPartylists}
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
