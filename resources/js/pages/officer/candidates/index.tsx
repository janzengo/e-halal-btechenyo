import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { CandidatesCards } from '@/components/@admin/@candidates/candidates-cards';
import { CandidatesSearch } from '@/components/@admin/@candidates/candidates-search';
import { Button } from '@/components/ui/button';
import { UserPlus } from 'lucide-react';
import { dummyAdmins } from '@/data/dummy-data';

// Dummy candidates data - replace with actual data from backend
const dummyCandidates = [
    {
        id: 1,
        firstname: 'John',
        lastname: 'Doe',
        email: 'john.doe@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        position: 'President',
        partylist: 'KABATAAN',
        year_level: 3,
        course: 'BS Computer Science',
        status: 'active' as const,
        votes_received: 45,
        created_at: '2024-01-15T00:00:00Z',
    },
    {
        id: 2,
        firstname: 'Jane',
        lastname: 'Smith',
        email: 'jane.smith@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        position: 'Vice President',
        partylist: 'PAG-ASA',
        year_level: 2,
        course: 'BS Information Technology',
        status: 'active' as const,
        votes_received: 38,
        created_at: '2024-01-15T00:00:00Z',
    },
    {
        id: 3,
        firstname: 'Mike',
        lastname: 'Johnson',
        email: 'mike.johnson@student.baliwag.edu.ph',
        photo: '/images/profile.jpg',
        position: 'Secretary',
        partylist: 'KABATAAN',
        year_level: 4,
        course: 'BS Computer Engineering',
        status: 'active' as const,
        votes_received: 52,
        created_at: '2024-01-15T00:00:00Z',
    },
];

export default function OfficerCandidates() {
    const admin = dummyAdmins[1]; // Officer admin
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);

    const handleView = (candidate: any) => {
        console.log('View candidate:', candidate);
        // Implement view logic
    };

    const handleEdit = (candidate: any) => {
        console.log('Edit candidate:', candidate);
        // Implement edit logic
    };

    const handleDelete = (candidate: any) => {
        console.log('Delete candidate:', candidate);
        // Implement delete logic
    };

    const handleAddNew = () => {
        console.log('Add new candidate');
        // Implement add new logic
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/candidates"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Candidates', href: '/officers/candidates' },
            ]}
        >
            <Head title="Manage Candidates" />

            {/* Header with Add Button */}
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
                    candidates={dummyCandidates}
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
