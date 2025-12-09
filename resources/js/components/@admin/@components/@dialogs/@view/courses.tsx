import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { X, Users, Search, Calendar, GraduationCap } from 'lucide-react';
import { SkeletonDialogList } from '@/components/@admin/@loading/skeleton-cards';

interface Voter {
    id: number;
    student_id: string;
    firstname: string;
    lastname: string;
    year_level: number;
    has_voted: boolean;
    created_at: string;
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
        const searchableFields = `${voter.firstname} ${voter.lastname} ${voter.student_id}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-3">
                        <div className="h-8 w-8 rounded-lg flex items-center justify-center border-2 bg-green-100 border-green-200">
                            <GraduationCap className="h-4 w-4 text-green-700" />
                        </div>
                        <div>
                            <span className="text-xl font-bold">{course?.code || 'Course'}</span>
                            <p className="text-sm text-gray-500 font-normal">
                                {course?.students_count || 0} voter(s)
                            </p>
                        </div>
                    </DialogTitle>
                    <DialogDescription>
                        View all voters enrolled in this course
                    </DialogDescription>
                </DialogHeader>

                <div className="flex-1 overflow-hidden flex flex-col">
                    {/* Search */}
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

                    {/* Results count */}
                    <div className="text-sm text-gray-600 mb-4">
                        Showing {filteredVoters.length} of {voters.length} voters
                    </div>

                    {/* Voters List */}
                    <div className="flex-1 overflow-y-auto">
                        {loading ? (
                            <SkeletonDialogList />
                        ) : filteredVoters.length === 0 ? (
                            <Empty className="border my-8">
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <Users />
                                    </EmptyMedia>
                                    <EmptyTitle>
                                        {searchTerm ? 'No voters found' : 'No voters yet'}
                                    </EmptyTitle>
                                    <EmptyDescription>
                                        {searchTerm 
                                            ? 'Try adjusting your search criteria to find voters.' 
                                            : 'Voters will appear here once they are enrolled in this course.'
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
                                                        {voter.firstname[0]}{voter.lastname[0]}
                                                    </div>
                                                </div>

                                                {/* Voter Info */}
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="font-semibold text-gray-900 truncate">
                                                        {voter.firstname} {voter.lastname}
                                                    </h3>
                                                    <p className="text-sm text-gray-600 mt-1">
                                                        ID: {voter.student_id}
                                                    </p>
                                                    
                                                    <div className="flex items-center gap-2 mt-2">
                                                        <Badge variant="outline" className="text-xs">
                                                            Year {voter.year_level}
                                                        </Badge>
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
