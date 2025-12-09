import { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { X, Users, Search, Calendar, GraduationCap } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface Voter {
    id: number;
    student_number: string;
    course_id: number;
    course?: string; // From relationship
    has_voted: boolean;
    created_at: string;
    updated_at: string;
}

interface CoursesViewDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    course: {
        id: number;
        code: string;
        name: string;
        students_count: number;
    } | null;
    voters: Voter[];
    loading?: boolean;
}

export function CoursesViewDialog({
    open,
    onOpenChange,
    course,
    voters = [],
    loading = false
}: CoursesViewDialogProps) {
    const [searchTerm, setSearchTerm] = useState('');

    // Reset search when dialog opens/closes
    useEffect(() => {
        if (!open) {
            setSearchTerm('');
        }
    }, [open]);

    // Filter voters based on search term
    const filteredVoters = voters.filter(voter => {
        const searchableFields = `${voter.student_number}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-3">
                        <div className="h-8 w-8 rounded-lg flex items-center justify-center border-2 bg-blue-100 border-blue-200">
                            <GraduationCap className="h-4 w-4 text-blue-700" />
                        </div>
                        <div>
                            <span className="text-xl font-bold">{course?.code || 'Course'}</span>
                            {course?.name && (
                                <p className="text-sm text-gray-600 font-normal">
                                    {course.name}
                                </p>
                            )}
                            <p className="text-sm text-gray-500 font-normal">
                                {voters.length} voter(s) enrolled
                            </p>
                        </div>
                    </DialogTitle>
                    <DialogDescription>
                        View all voters enrolled in this course
                    </DialogDescription>
                </DialogHeader>

                <div className="flex-1 overflow-hidden flex flex-col">
                    {/* Search - Only show when not loading */}
                    {!loading && (
                        <div className="relative mb-4">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                            <Input
                                type="text"
                                placeholder="Search voters..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    )}

                    {/* Results count - Only show when not loading */}
                    {!loading && (
                        <div className="text-sm text-gray-600 mb-4">
                            Showing {filteredVoters.length} of {voters.length} voters
                        </div>
                    )}

                    {/* Voters List */}
                    <div className="flex-1 overflow-y-auto">
                        {loading ? (
                            <div className="flex items-center justify-center py-12">
                                <div className="flex flex-col items-center gap-3">
                                    <Spinner className="h-8 w-8" />
                                    <p className="text-sm text-gray-500">Loading voters...</p>
                                </div>
                            </div>
                        ) : filteredVoters.length === 0 ? (
                            <Empty className="border my-8">
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <Users />
                                    </EmptyMedia>
                                    <EmptyTitle>
                                        {searchTerm ? 'No voters found' : 'No voters enrolled'}
                                    </EmptyTitle>
                                    <EmptyDescription>
                                        {searchTerm 
                                            ? 'Try adjusting your search criteria to find voters.' 
                                            : voters.length === 0 
                                                ? 'No voters have been enrolled in this course yet. Voters will appear here once they are registered and assigned to this course.'
                                                : 'No voters match your search criteria.'
                                        }
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {filteredVoters.map((voter) => (
                                    <Card key={voter.id} className="hover:shadow-md transition-shadow">
                                        <CardContent className="p-4">
                                            <div className="flex items-start gap-3">
                                                {/* Voter Avatar */}
                                                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <div className="h-full w-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-medium">
                                                        {voter.student_number.slice(-2)}
                                                    </div>
                                                </div>

                                                {/* Voter Info */}
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="font-semibold text-gray-900 truncate">
                                                        {voter.student_number}
                                                    </h3>
                                                    <p className="text-sm text-gray-600 mt-1">
                                                        {voter.student_number}@btech.ph.education
                                                    </p>
                                                    
                                                    <div className="flex items-center gap-2 mt-2">
                                                        <Badge 
                                                            variant={voter.has_voted ? "default" : "secondary"}
                                                            className="text-xs"
                                                        >
                                                            {voter.has_voted ? 'Voted' : 'Not Voted'}
                                                        </Badge>
                                                    </div>

                                                    <div className="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3" />
                                                            {new Date(voter.created_at).toLocaleDateString()}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Dialog Actions */}
                <div className="flex justify-end pt-4 border-t">
                    <Button 
                        variant="outline" 
                        onClick={() => onOpenChange(false)}
                        className="cursor-pointer"
                    >
                        <X className="h-4 w-4 mr-2" />
                        Close
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
