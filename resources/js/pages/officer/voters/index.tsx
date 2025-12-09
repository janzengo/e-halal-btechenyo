import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { VotersTable } from '@/components/@admin/@voters/voters-table';
import { VotersStatisticsCard } from '@/components/@admin/@voters/voters-statistics-card';
import { VotersSearch } from '@/components/@admin/@voters/voters-search';
import { Button } from '@/components/ui/button';
import { UserPlus } from 'lucide-react';

// Dummy voters data - replace with actual data from backend
const dummyVoters = [
    {
        id: 1,
        student_id: '2021-00001',
        firstname: 'John',
        lastname: 'Doe',
        email: 'john.doe@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        course: 'BS Computer Science',
        year_level: 3,
        has_voted: true,
        voted_at: '2024-01-20T10:30:00Z',
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 2,
        student_id: '2021-00002',
        firstname: 'Jane',
        lastname: 'Smith',
        email: 'jane.smith@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        course: 'BS Information Technology',
        year_level: 2,
        has_voted: false,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 3,
        student_id: '2021-00003',
        firstname: 'Mike',
        lastname: 'Johnson',
        email: 'mike.johnson@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        course: 'BS Computer Engineering',
        year_level: 4,
        has_voted: true,
        voted_at: '2024-01-20T14:15:00Z',
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 4,
        student_id: '2021-00004',
        firstname: 'Sarah',
        lastname: 'Wilson',
        email: 'sarah.wilson@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        course: 'BS Electronics Engineering',
        year_level: 3,
        has_voted: false,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 5,
        student_id: '2021-00005',
        firstname: 'David',
        lastname: 'Brown',
        email: 'david.brown@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        course: 'BS Computer Science',
        year_level: 2,
        has_voted: true,
        voted_at: '2024-01-20T16:45:00Z',
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
    },
];

export default function OfficerVoters() {
    const [searchTerm, setSearchTerm] = useState('');

    const handleView = (voter: any) => {
        console.log('View voter:', voter);
        // Implement view logic
    };

    const handleEdit = (voter: any) => {
        console.log('Edit voter:', voter);
        // Implement edit logic
    };

    const handleAddNew = () => {
        console.log('Add new voter');
        // Implement add new logic
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/voters"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Voters', href: '/officers/voters' },
            ]}
        >
            <Head title="Manage Voters" />

            {/* Header with Add Button */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Voters</h2>
                    <p className="text-gray-600">Manage registered voters and their voting status</p>
                </div>
                <Button onClick={handleAddNew} variant="outlinePrimary">
                    <UserPlus className="h-4 w-4" />
                    Add New Voter
                </Button>
            </div>

            {/* Statistics Cards */}
            <VotersStatisticsCard voters={dummyVoters} />

            {/* Search */}
            <VotersSearch
                searchTerm={searchTerm}
                onSearchChange={setSearchTerm}
            />
            
            <div>
                <VotersTable
                    voters={dummyVoters}
                    userRole="officer"
                    onView={handleView}
                    onEdit={handleEdit}
                    onAddNew={handleAddNew}
                    searchTerm={searchTerm}
                />
            </div>
        </AdminLayout>
    );
}
