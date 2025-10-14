import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Search
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

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    photo?: string;
    position: string;
    partylist: string;
    platform?: string;
    status: 'active' | 'inactive';
    created_at: string;
}

interface CandidatesCardsProps {
    candidates: Candidate[];
    userRole: AdminRole;
    onView?: (candidate: Candidate) => void;
    onEdit?: (candidate: Candidate) => void;
    onDelete?: (candidate: Candidate) => void;
    searchTerm?: string;
    currentPage?: number;
    onPageChange?: (page: number) => void;
}

export function CandidatesCards({ 
    candidates, 
    userRole, 
    onView, 
    onEdit, 
    onDelete, 
    searchTerm: externalSearchTerm,
    currentPage: externalCurrentPage,
    onPageChange
}: CandidatesCardsProps) {
    const [internalSearchTerm, setInternalSearchTerm] = useState('');
    const [internalCurrentPage, setInternalCurrentPage] = useState(1);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [candidateToDelete, setCandidateToDelete] = useState<Candidate | null>(null);
    const itemsPerPage = 6;
    const isHead = userRole === 'head';

    // Use external props if provided, otherwise use internal state
    const searchTerm = externalSearchTerm !== undefined ? externalSearchTerm : internalSearchTerm;
    const currentPage = externalCurrentPage !== undefined ? externalCurrentPage : internalCurrentPage;
    
    const setSearchTerm = externalSearchTerm !== undefined ? () => {} : setInternalSearchTerm;
    
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

    // Reset pagination when candidates change
    useEffect(() => {
        handlePageChange(1);
    }, [candidates.length]);

    const handleDeleteClick = (candidate: Candidate) => {
        setCandidateToDelete(candidate);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (candidateToDelete && onDelete) {
            onDelete(candidateToDelete);
        }
        setDeleteDialogOpen(false);
        setCandidateToDelete(null);
    };

    const handleDeleteCancel = () => {
        setDeleteDialogOpen(false);
        setCandidateToDelete(null);
    };

    // Filter candidates based on search term
    const filteredCandidates = candidates.filter(candidate => {
        const searchableFields = `${candidate.firstname} ${candidate.lastname} ${candidate.position} ${candidate.partylist}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredCandidates.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedCandidates = filteredCandidates.slice(startIndex, startIndex + itemsPerPage);

    return (
        <div className="flex flex-col gap-6">
            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedCandidates.length} of {filteredCandidates.length} candidates
            </div>

            {/* Cards Grid */}
            {paginatedCandidates.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedCandidates.map((candidate) => (
                        <Card key={candidate.id} className="hover:shadow-lg transition-shadow duration-200 border-green-100">
                            <CardHeader className="pb-0">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-5">
                                        <Avatar className="h-20 w-20">
                                            <AvatarImage 
                                                src={candidate.photo ? `/storage/${candidate.photo}` : undefined} 
                                                alt={`${candidate.firstname} ${candidate.lastname}`} 
                                            />
                                            <AvatarFallback className="bg-green-100 text-green-700">
                                                {candidate.firstname[0]}{candidate.lastname[0]}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{candidate.firstname} {candidate.lastname}</h3>
                                            <p className="text-sm text-gray-500">{candidate.position}</p>
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
                                                <DropdownMenuItem 
                                                    onClick={() => onView?.(candidate)}
                                                    className="cursor-pointer"
                                                >
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => onEdit?.(candidate)}
                                                    className="cursor-pointer"
                                                >
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => handleDeleteClick(candidate)}
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
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Badge variant="secondary" className="bg-green-100 text-green-700">
                                        {candidate.partylist}
                                    </Badge>
                                    <Badge variant={candidate.status === 'active' ? 'default' : 'secondary'}>
                                        {candidate.status}
                                    </Badge>
                                </div>
                                
                                {candidate.platform && (
                                    <div className="text-sm">
                                        <span className="text-gray-500">Platform:</span>
                                        <p className="font-medium mt-1 text-gray-700 line-clamp-2">
                                            {candidate.platform}
                                        </p>
                                    </div>
                                )}
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
                        <p className="text-lg font-medium text-gray-600">No candidates found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new candidate.</p>
                    </div>
                </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
                <div className="flex justify-center items-center space-x-3 mt-8 py-4">
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
            <AlertDialog open={deleteDialogOpen} onOpenChange={(open) => {
                setDeleteDialogOpen(open);
                if (!open) {
                    setCandidateToDelete(null);
                }
            }}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Candidate</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete <strong>{candidateToDelete?.firstname} {candidateToDelete?.lastname}</strong>?
                            This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="cursor-pointer">
                            Cancel
                        </AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleDeleteConfirm}
                            className="bg-red-600 hover:bg-red-700 cursor-pointer"
                        >
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
