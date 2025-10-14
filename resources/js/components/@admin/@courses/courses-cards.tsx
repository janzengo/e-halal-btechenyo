import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Search,
    Users,
    GraduationCap,
    BookOpen
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { AdminRole } from '@/types/ehalal';

interface Course {
    id: number;
    code: string;
    name: string;
    description: string;
    students_count: number;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

interface CoursesCardsProps {
    courses: Course[];
    userRole: AdminRole;
    onView?: (course: Course) => void;
    onEdit?: (course: Course) => void;
    onDelete?: (course: Course) => void;
    searchTerm?: string;
    currentPage?: number;
    onPageChange?: (page: number) => void;
}

export function CoursesCards({ 
    courses, 
    userRole, 
    onView, 
    onEdit, 
    onDelete, 
    searchTerm: externalSearchTerm,
    currentPage: externalCurrentPage,
    onPageChange
}: CoursesCardsProps) {
    const [internalSearchTerm, setInternalSearchTerm] = useState('');
    const [internalCurrentPage, setInternalCurrentPage] = useState(1);
    const itemsPerPage = 6;
    const isHead = userRole === 'head';
    
    // Delete dialog state
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [courseToDelete, setCourseToDelete] = useState<Course | null>(null);

    // Use external props if provided, otherwise use internal state
    const searchTerm = externalSearchTerm !== undefined ? externalSearchTerm : internalSearchTerm;
    const currentPage = externalCurrentPage !== undefined ? externalCurrentPage : internalCurrentPage;
    
    const setSearchTerm = externalSearchTerm !== undefined ? () => {} : setInternalSearchTerm;

    // Handle delete confirmation
    const handleDeleteClick = (course: Course) => {
        setCourseToDelete(course);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (courseToDelete) {
            onDelete?.(courseToDelete);
            setDeleteDialogOpen(false);
            setCourseToDelete(null);
        }
    };

    const handleDeleteCancel = () => {
        setDeleteDialogOpen(false);
        setCourseToDelete(null);
    };
    
    const handlePageChange = (pageOrCallback: number | ((prev: number) => number)) => {
        if (externalCurrentPage !== undefined && onPageChange) {
            if (typeof pageOrCallback === 'function') {
                const newPage = pageOrCallback(externalCurrentPage);
                onPageChange(newPage);
            } else {
                onPageChange(pageOrCallback);
            }
        } else {
            if (typeof pageOrCallback === 'function') {
                setInternalCurrentPage(pageOrCallback);
            } else {
                setInternalCurrentPage(pageOrCallback);
            }
        }
    };

    // Reset pagination when courses change
    useEffect(() => {
        handlePageChange(1);
    }, [courses.length]);

    // Filter courses based on search term
    const filteredCourses = courses.filter(course => {
        const searchableFields = `${course.code} ${course.name} ${course.description}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredCourses.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedCourses = filteredCourses.slice(startIndex, startIndex + itemsPerPage);

    return (
        <div className="flex flex-col gap-6">
            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedCourses.length} of {filteredCourses.length} courses
            </div>

            {/* Cards Grid */}
            {paginatedCourses.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedCourses.map((course) => (
                        <Card key={course.id} className="hover:shadow-lg transition-shadow duration-200">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <BookOpen className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{course.name}</h3>
                                            <p className="text-sm text-gray-500 font-mono">{course.code}</p>
                                        </div>
                                    </div>
                                    {isHead && (
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm" className="h-8 w-8 p-0 cursor-pointer">
                                                    <MoreVertical className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem onClick={() => onView?.(course)} className="cursor-pointer">
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    View Voters
                                                </DropdownMenuItem>
                                                <DropdownMenuItem onClick={() => onEdit?.(course)} className="cursor-pointer">
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => handleDeleteClick(course)}
                                                    className="text-red-600 cursor-pointer"
                                                >
                                                    <Trash2 className="h-4 w-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="pt-3">
                                <div className="flex items-center gap-2">
                                    <GraduationCap className="h-4 w-4 text-gray-400" />
                                    <span className="text-sm font-medium text-gray-600">
                                        {course.students_count} voters
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            ) : (
                <div className="text-center py-12">
                    <div className="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                        <div className="text-gray-400 mb-4">
                            <Search className="h-12 w-12 mx-auto" />
                        </div>
                        <p className="text-lg font-medium text-gray-600">No courses found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new course.</p>
                    </div>
                </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
                <div className="flex justify-end items-center space-x-3 mt-8 py-4">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(prev => Math.max(prev - 1, 1))}
                        disabled={safePage === 1}
                        className="border-green-200 hover:bg-green-50 disabled:opacity-50 px-4"
                    >
                        Previous
                    </Button>
                    
                    <div className="flex items-center space-x-1">
                        {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                            <Button
                                key={page}
                                variant={page === safePage ? "default" : "outline"}
                                size="sm"
                                onClick={() => handlePageChange(page)}
                                className={`w-8 h-8 p-0 ${
                                    page === safePage 
                                        ? "bg-green-600 hover:bg-green-700" 
                                        : "border-green-200 hover:bg-green-50"
                                }`}
                            >
                                {page}
                            </Button>
                        ))}
                    </div>
                    
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(prev => Math.min(prev + 1, totalPages))}
                        disabled={safePage === totalPages}
                        className="border-green-200 hover:bg-green-50 disabled:opacity-50 px-4"
                    >
                        Next
                    </Button>
                </div>
            )}

            {/* Delete Confirmation Dialog */}
            <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Course</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete the course "{courseToDelete?.name}"? 
                            This action cannot be undone.
                            {courseToDelete?.students_count ? (
                                <div className="mt-2 p-2 bg-red-50 border border-red-200 rounded text-red-700">
                                    <strong>Warning:</strong> This course has {courseToDelete.students_count} students assigned to it. 
                                    You must reassign or remove these students first before deleting the course.
                                </div>
                            ) : null}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="cursor-pointer">Cancel</AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleDeleteConfirm}
                            className="bg-red-600 border-red-600 hover:bg-red-700 cursor-pointer"
                        >
                            Delete Course
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
