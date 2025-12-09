import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AdminRole } from '@/types/ehalal';
import { Edit, Trash2, MoreVertical, Trash } from 'lucide-react';
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

interface VotersTableProps {
    voters: Voter[];
    userRole: AdminRole;
    onEdit?: (voter: Voter) => void;
    onDelete?: (voter: Voter) => void;
    onBulkDelete?: (voterIds: number[]) => void;
    searchTerm?: string;
    bulkDeleteLoading?: boolean;
}

export function VotersTable({
    voters,
    userRole,
    onEdit,
    onDelete,
    onBulkDelete,
    searchTerm = '',
    bulkDeleteLoading = false
}: VotersTableProps) {
    const isHead = userRole === 'head';
    const [selectedVoters, setSelectedVoters] = useState<number[]>([]);
    
    // Single deletion dialog state
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [voterToDelete, setVoterToDelete] = useState<Voter | null>(null);
    
    // Bulk deletion dialog state
    const [bulkDeleteDialogOpen, setBulkDeleteDialogOpen] = useState(false);

    // Filter voters based on search term
    const filteredVoters = voters.filter(voter => {
        const searchableFields = `${voter.student_number} ${voter.course || ''}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Get voters that can be deleted (haven't voted)
    const deletableVoters = filteredVoters.filter(voter => !voter.has_voted);
    const selectedDeletableVoters = selectedVoters.filter(id => 
        deletableVoters.some(voter => voter.id === id)
    );

    // Handle select all checkbox
    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedVoters(deletableVoters.map(voter => voter.id));
        } else {
            setSelectedVoters([]);
        }
    };

    // Handle individual checkbox
    const handleSelectVoter = (voterId: number, checked: boolean) => {
        if (checked) {
            setSelectedVoters(prev => [...prev, voterId]);
        } else {
            setSelectedVoters(prev => prev.filter(id => id !== voterId));
        }
    };

    // Handle single delete
    const handleDeleteClick = (voter: Voter) => {
        setVoterToDelete(voter);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (voterToDelete) {
            onDelete?.(voterToDelete);
            setDeleteDialogOpen(false);
            setVoterToDelete(null);
        }
    };

    // Handle bulk delete
    const handleBulkDeleteClick = () => {
        if (selectedDeletableVoters.length === 0) return;
        setBulkDeleteDialogOpen(true);
    };

    const handleBulkDeleteConfirm = () => {
        if (selectedDeletableVoters.length > 0) {
            onBulkDelete?.(selectedDeletableVoters);
            setSelectedVoters([]);
            setBulkDeleteDialogOpen(false);
        }
    };

    // Check if all deletable voters are selected
    const isAllSelected = deletableVoters.length > 0 && 
        deletableVoters.every(voter => selectedVoters.includes(voter.id));
    
    // Check if some deletable voters are selected
    const isSomeSelected = selectedVoters.length > 0 && !isAllSelected;

    return (
        <div className="flex flex-col gap-6">
            {/* Bulk Actions */}
            {isHead && selectedDeletableVoters.length > 0 && (
                <div className="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-medium text-red-800">
                            {selectedDeletableVoters.length} voter(s) selected
                        </span>
                    </div>
                    <Button
                        variant="destructive"
                        size="sm"
                        onClick={handleBulkDeleteClick}
                        disabled={bulkDeleteLoading}
                        className="bg-red-600 hover:bg-red-700"
                    >
                        {bulkDeleteLoading ? (
                            <>
                                <Spinner className="h-4 w-4 mr-2" />
                                Deleting...
                            </>
                        ) : (
                            <>
                                <Trash className="h-4 w-4 mr-2" />
                                Delete Selected
                            </>
                        )}
                    </Button>
                </div>
            )}

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            {isHead && (
                                <TableHead className="w-12">
                                    <Checkbox
                                        checked={isAllSelected ? true : isSomeSelected ? 'indeterminate' : false}
                                        onCheckedChange={handleSelectAll}
                                        disabled={deletableVoters.length === 0}
                                    />
                                </TableHead>
                            )}
                            <TableHead>Student Number</TableHead>
                            <TableHead>Course</TableHead>
                            <TableHead>Voting Status</TableHead>
                            <TableHead>Registered At</TableHead>
                            {isHead && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {filteredVoters.length > 0 ? (
                            filteredVoters.map((voter) => (
                                <TableRow key={voter.id}>
                                    {isHead && (
                                        <TableCell>
                                            <Checkbox
                                                checked={selectedVoters.includes(voter.id)}
                                                onCheckedChange={(checked) => handleSelectVoter(voter.id, checked as boolean)}
                                                disabled={voter.has_voted}
                                            />
                                        </TableCell>
                                    )}
                                    <TableCell>
                                        <div className="font-mono text-sm font-medium">{voter.student_number}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {voter.student_number}@btech.ph.education
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="text-sm">
                                            {voter.course || 'Unknown Course'}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={voter.has_voted ? 'default' : 'secondary'}>
                                            {voter.has_voted ? 'Voted' : 'Not Voted'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="text-sm text-muted-foreground">
                                            {new Date(voter.created_at).toLocaleString()}
                                        </div>
                                    </TableCell>
                                    {isHead && (
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem
                                                        onClick={() => onEdit?.(voter)}
                                                        disabled={voter.has_voted}
                                                        className="cursor-pointer"
                                                    >
                                                        <Edit className="h-4 w-4 mr-2" />
                                                        Edit Voter
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => handleDeleteClick(voter)}
                                                        disabled={voter.has_voted}
                                                        className="text-red-600 focus:text-red-600 cursor-pointer"
                                                    >
                                                        <Trash2 className="h-4 w-4 mr-2" />
                                                        Delete Voter
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={isHead ? 6 : 4} className="text-center py-8 text-muted-foreground">
                                    {searchTerm ? 'No voters found matching your search.' : 'No voters found.'}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {/* Single Delete Confirmation Dialog */}
            <AlertDialog 
                open={deleteDialogOpen} 
                onOpenChange={(open) => {
                    setDeleteDialogOpen(open);
                    if (!open) {
                        setVoterToDelete(null);
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Voter</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete voter "{voterToDelete?.student_number}"? This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="cursor-pointer">
                            Cancel
                        </AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleDeleteConfirm}
                            className="bg-red-600 border-red-600 hover:bg-red-700 cursor-pointer"
                        >
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            {/* Bulk Delete Confirmation Dialog */}
            <AlertDialog 
                open={bulkDeleteDialogOpen} 
                onOpenChange={(open) => {
                    setBulkDeleteDialogOpen(open);
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Multiple Voters</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete {selectedDeletableVoters.length} voter(s)? This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="cursor-pointer">
                            Cancel
                        </AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleBulkDeleteConfirm}
                            className="bg-red-600 border-red-600 hover:bg-red-700 cursor-pointer"
                        >
                            Delete All Selected
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
