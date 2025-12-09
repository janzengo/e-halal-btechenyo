import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Users,
    Award,
    Search,
    AlertCircle
} from 'lucide-react';
import { PositionsSearch } from './positions-search';
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

interface Position {
    id: number;
    title: string;
    description: string;
    max_winners: number;
    candidates_count: number;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

interface PositionsCardsProps {
    positions: Position[];
    userRole: AdminRole;
    onViewCandidates?: (position: Position) => void;
    onEdit?: (position: Position) => void;
    onDelete?: (position: Position) => void;
    searchTerm?: string;
    currentPage?: number;
    onPageChange?: (page: number) => void;
}

export function PositionsCards({ 
    positions, 
    userRole, 
    onViewCandidates, 
    onEdit, 
    onDelete, 
    searchTerm: externalSearchTerm,
    currentPage: externalCurrentPage,
    onPageChange
}: PositionsCardsProps) {
    const [internalSearchTerm, setInternalSearchTerm] = useState('');
    const [internalCurrentPage, setInternalCurrentPage] = useState(1);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [positionToDelete, setPositionToDelete] = useState<Position | null>(null);
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

    // Reset pagination when positions change
    useEffect(() => {
        handlePageChange(1);
    }, [positions.length]);

    // Filter positions based on search term
    const filteredPositions = positions.filter(position => {
        const searchableFields = `${position.title} ${position.description}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredPositions.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedPositions = filteredPositions.slice(startIndex, startIndex + itemsPerPage);

    // Delete handlers
    const handleDeleteClick = (position: Position) => {
        setPositionToDelete(position);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (positionToDelete && onDelete) {
            onDelete(positionToDelete);
        }
        setDeleteDialogOpen(false);
        setPositionToDelete(null);
    };

    const handleDeleteCancel = () => {
        setDeleteDialogOpen(false);
        setPositionToDelete(null);
    };

    return (
        <div className="flex flex-col gap-6">
            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedPositions.length} of {filteredPositions.length} positions
            </div>

            {/* Cards Grid */}
            {paginatedPositions.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedPositions.map((position) => (
                        <Card key={position.id} className="hover:shadow-lg transition-shadow duration-200 border-green-100">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <Award className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{position.title}</h3>
                                            <p className="text-sm text-gray-500">
                                                Created {new Date(position.created_at).toLocaleDateString()}
                                            </p>
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
                                                <DropdownMenuItem onClick={() => onViewCandidates?.(position)} className="cursor-pointer">
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    View Candidates
                                                </DropdownMenuItem>
                                                <DropdownMenuItem onClick={() => onEdit?.(position)} className="cursor-pointer">
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => handleDeleteClick(position)}
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
                                    <div className="flex items-center gap-2">
                                        <Users className="h-4 w-4 text-gray-400" />
                                        <span className="text-sm font-medium text-gray-600">
                                            {position.candidates_count} candidates
                                        </span>
                                    </div>
                                    <Badge variant="outline" className="border-green-200 text-green-700">
                                        {position.max_winners} winner{position.max_winners > 1 ? 's' : ''}
                                    </Badge>
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
                        <p className="text-lg font-medium text-gray-600">No positions found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new position.</p>
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
            <AlertDialog 
                open={deleteDialogOpen} 
                onOpenChange={(open) => {
                    setDeleteDialogOpen(open);
                    if (!open) {
                        setPositionToDelete(null);
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle className="flex items-center gap-2">
                            <AlertCircle className="h-5 w-5 text-red-600" />
                            Delete Position
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete the position "{positionToDelete?.title}"? 
                            {positionToDelete?.candidates_count ? (
                                <span className="block mt-2 text-red-600 font-medium">
                                    Warning: This position has {positionToDelete.candidates_count} candidate{positionToDelete.candidates_count > 1 ? 's' : ''} assigned to it.
                                </span>
                            ) : null}
                            <span className="block mt-2">
                                This action cannot be undone.
                            </span>
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
                            Delete Position
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
