import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { CoursesCards } from '@/components/@admin/@courses/courses-cards';
import { CoursesSearch } from '@/components/@admin/@courses/courses-search';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

// Dummy courses data - replace with actual data from backend
const dummyCourses = [
    {
        id: 1,
        code: 'BSCS',
        name: 'Bachelor of Science in Computer Science',
        description: 'A comprehensive program covering software development, algorithms, and computer systems.',
        students_count: 150,
        candidates_count: 8,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 2,
        code: 'BSIT',
        name: 'Bachelor of Science in Information Technology',
        description: 'Focuses on information systems, network administration, and database management.',
        students_count: 120,
        candidates_count: 6,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 3,
        code: 'BSCE',
        name: 'Bachelor of Science in Computer Engineering',
        description: 'Combines computer science and electrical engineering principles.',
        students_count: 80,
        candidates_count: 4,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
    {
        id: 4,
        code: 'BSEE',
        name: 'Bachelor of Science in Electronics Engineering',
        description: 'Specializes in electronic systems, telecommunications, and embedded systems.',
        students_count: 90,
        candidates_count: 3,
        status: 'active' as const,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
    },
];

export default function OfficerCourses() {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);

    const handleView = (course: any) => {
        console.log('View course:', course);
        // Implement view logic
    };

    const handleEdit = (course: any) => {
        console.log('Edit course:', course);
        // Implement edit logic
    };

    const handleDelete = (course: any) => {
        console.log('Delete course:', course);
        // Implement delete logic
    };

    const handleAddNew = () => {
        console.log('Add new course');
        // Implement add new logic
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/courses"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Courses', href: '/officers/courses' },
            ]}
        >
            <Head title="Manage Courses" />

            {/* Header with Add Button */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Courses</h2>
                    <p className="text-gray-600">Manage academic programs and departments</p>
                </div>
                <Button onClick={handleAddNew} variant="outlinePrimary">
                    <Plus className="h-4 w-4" />
                    Add New Course
                </Button>
            </div>

            {/* Search */}
            <CoursesSearch
                searchTerm={searchTerm}
                onSearchChange={(value) => {
                    setSearchTerm(value);
                    setCurrentPage(1);
                }}
            />
            
            <div>
                <CoursesCards
                    courses={dummyCourses}
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
