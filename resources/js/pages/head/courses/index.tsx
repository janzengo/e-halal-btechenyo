import { Head, Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import AdminLayout from '@/layouts/admin/admin-layout';
import { CoursesCards } from '@/components/@admin/@courses/courses-cards';
import { CoursesSearch } from '@/components/@admin/@courses/courses-search';
import { Button } from '@/components/ui/button';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { CourseDialog, CourseFormData } from '@/components/@admin/@components/@dialogs/course';
import { CoursesViewDialog } from '@/components/@admin/@components/@dialogs/@view/courses';
import { Plus, GraduationCap } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonCards, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface Course {
    id: number;
    code: string;
    description: string;
    voters_count: number;
    created_at?: string;
    updated_at?: string;
}

interface HeadCoursesProps {
    courses: Course[];
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function HeadCourses() {
    const { courses, flash } = usePage<HeadCoursesProps>().props;
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const { isPageLoading } = useLoading();
    
    // Form state
    const [formOpen, setFormOpen] = useState(false);
    const [editingCourse, setEditingCourse] = useState<Course | null>(null);
    const [loading, setLoading] = useState(false);

    // View voters state
    const [viewDialogOpen, setViewDialogOpen] = useState(false);
    const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
    const [voters, setVoters] = useState<any[]>([]);
    const [votersLoading, setVotersLoading] = useState(false);

    // Handle flash messages
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const handleView = async (course: Course) => {
        setSelectedCourse(course);
        setVotersLoading(true);
        setViewDialogOpen(true);

        try {
            const response = await fetch(`/head/courses/${course.id}/voters`);
            const data = await response.json();
            setVoters(data.voters || []);
        } catch (error) {
            console.error('Error fetching voters:', error);
            setVoters([]);
        } finally {
            setVotersLoading(false);
        }
    };

    const handleEdit = (course: Course) => {
        setEditingCourse(course);
        setFormOpen(true);
    };

    const handleDelete = (course: Course) => {
        router.delete(`/head/courses/${course.id}`, {
            preserveScroll: true,
        });
    };

    const handleAddNew = () => {
        setEditingCourse(null);
        setFormOpen(true);
    };

    const handleFormSubmit = async (data: CourseFormData) => {
        setLoading(true);
        
        try {
            if (editingCourse) {
                router.put(`/head/courses/${editingCourse.id}`, data, {
                    onSuccess: () => {
                        setFormOpen(false);
                        setEditingCourse(null);
                    },
                    onError: (errors) => {
                        console.error('Update errors:', errors);
                    },
                    onFinish: () => {
                        setLoading(false);
                    }
                });
            } else {
                router.post('/head/courses', data, {
                    onSuccess: () => {
                        setFormOpen(false);
                        setEditingCourse(null);
                    },
                    onError: (errors) => {
                        console.error('Create errors:', errors);
                    },
                    onFinish: () => {
                        setLoading(false);
                    }
                });
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            setLoading(false);
        }
    };

    const handleFormClose = (open: boolean) => {
        if (!open) {
            setEditingCourse(null);
        }
        setFormOpen(open);
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/courses"
            breadcrumbs={[
                { title: 'Election Management', href: '#' },
                { title: 'Courses', href: '/head/courses' },
            ]}
        >
            <Head title="Manage Courses" />

            {/* Header with Add Button */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
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
            )}

            {isPageLoading ? (
                <SkeletonCards count={6} />
            ) : courses.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <GraduationCap />
                        </EmptyMedia>
                        <EmptyTitle>No Courses Added</EmptyTitle>
                        <EmptyDescription>
                            Add academic programs and courses to organize voters by their field of study.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button onClick={handleAddNew} variant="outlinePrimary">
                            <Plus className="h-4 w-4" />
                            Add First Course
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
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
                            courses={courses.map(c => ({
                                ...c,
                                name: c.description,
                                students_count: c.voters_count,
                                status: 'active' as const
                            }))}
                            userRole="head"
                            onView={handleView}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            searchTerm={searchTerm}
                            currentPage={currentPage}
                            onPageChange={setCurrentPage}
                        />
                    </div>
                </>
            )}

            {/* Course Dialog */}
            <CourseDialog
                open={formOpen}
                onOpenChange={handleFormClose}
                course={editingCourse}
                onSubmit={handleFormSubmit}
                loading={loading}
            />

            {/* View Voters Dialog */}
            <CoursesViewDialog
                open={viewDialogOpen}
                onOpenChange={setViewDialogOpen}
                course={selectedCourse}
                voters={voters}
                loading={votersLoading}
            />
        </AdminLayout>
    );
}
