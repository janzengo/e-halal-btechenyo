import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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
import {
    MoreVertical,
    Edit,
    Trash2,
    Search,
    Users,
    Palette,
    Eye
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AdminRole } from '@/types/ehalal';

interface Partylist {
    id: number;
    name: string;
    color: string;
    platform?: string;
    candidates_count: number;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

interface PartylistsCardsProps {
    partylists: Partylist[];
    userRole: AdminRole;
    onEdit?: (partylist: Partylist) => void;
    onDelete?: (partylist: Partylist) => void;
    onViewCandidates?: (partylist: Partylist) => void;
    searchTerm?: string;
    currentPage?: number;
    onPageChange?: (page: number) => void;
}

export function PartylistsCards({ 
    partylists, 
    userRole, 
    onEdit, 
    onDelete, 
    onViewCandidates,
    searchTerm: externalSearchTerm,
    currentPage: externalCurrentPage,
    onPageChange
}: PartylistsCardsProps) {
    const [internalSearchTerm, setInternalSearchTerm] = useState('');
    const [internalCurrentPage, setInternalCurrentPage] = useState(1);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [partylistToDelete, setPartylistToDelete] = useState<Partylist | null>(null);
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

    // Reset pagination when partylists change
    useEffect(() => {
        handlePageChange(1);
    }, [partylists.length]);

    // Handle delete confirmation
    const handleDeleteClick = (partylist: Partylist) => {
        setPartylistToDelete(partylist);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (partylistToDelete) {
            onDelete?.(partylistToDelete);
            setDeleteDialogOpen(false);
            setPartylistToDelete(null);
        }
    };

    const handleDeleteCancel = () => {
        setDeleteDialogOpen(false);
        setPartylistToDelete(null);
    };

    // Filter partylists based on search term
    const filteredPartylists = partylists.filter(partylist => {
        const searchableFields = `${partylist.name} ${partylist.platform || ''}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredPartylists.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedPartylists = filteredPartylists.slice(startIndex, startIndex + itemsPerPage);

    return (
        <div className="flex flex-col gap-6">
            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedPartylists.length} of {filteredPartylists.length} partylists
            </div>

            {/* Cards Grid */}
            {paginatedPartylists.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedPartylists.map((partylist) => (
                        <Card key={partylist.id} className="hover:shadow-lg transition-shadow duration-200 border-green-100">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-12 w-12 rounded-lg flex items-center justify-center border-2" style={{ backgroundColor: partylist.color, borderColor: partylist.color + '40' }}>
                                            <Users className="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{partylist.name}</h3>
                                            <p className="text-sm text-gray-500 font-mono">{partylist.acronym}</p>
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
                                                    onClick={() => onViewCandidates?.(partylist)}
                                                    className="cursor-pointer"
                                                >
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    View Candidates
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => onEdit?.(partylist)}
                                                    className="cursor-pointer"
                                                >
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    onClick={() => handleDeleteClick(partylist)}
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
                                            {partylist.candidates_count} candidates
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div 
                                            className="w-4 h-4 rounded-full border-2 border-gray-200"
                                            style={{ backgroundColor: partylist.color }}
                                        />
                                        <span className="text-xs text-gray-500 font-mono">
                                            {partylist.color}
                                        </span>
                                    </div>
                                </div>
                                
                                {partylist.platform && (
                                    <div className="pt-3 border-t border-gray-100 space-y-2">
                                        <h4 className="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                            Platform
                                        </h4>
                                        <p className="text-sm text-gray-600 line-clamp-3">
                                            {partylist.platform}
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
                        <p className="text-lg font-medium text-gray-600">No partylists found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new partylist.</p>
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
                        setPartylistToDelete(null);
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Partylist</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete "{partylistToDelete?.name}"? This action cannot be undone.
                            {partylistToDelete?.candidates_count && partylistToDelete.candidates_count > 0 ? (
                                <span className="block mt-2 text-red-600 font-medium">
                                    Warning: This partylist has {partylistToDelete.candidates_count} candidate(s) assigned to it.
                                </span>
                            ) : null}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="cursor-pointer">
                            Cancel
                        </AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleDeleteConfirm}
                            className="bg-red-600 border border-red-600 hover:bg-red-700 cursor-pointer"
                        >
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
