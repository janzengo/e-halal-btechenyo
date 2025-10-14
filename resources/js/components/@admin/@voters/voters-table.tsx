import React from 'react';
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
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { AdminRole } from '@/types/ehalal';
import { Edit, Eye, UserPlus, Trash2 } from 'lucide-react';

interface Voter {
    id: number;
    student_id: string;
    firstname: string;
    lastname: string;
    email: string;
    photo: string;
    course: string;
    year_level: number;
    has_voted: boolean;
    voted_at?: string;
    status: 'active' | 'inactive';
    created_at: string;
}

interface VotersTableProps {
    voters: Voter[];
    userRole: AdminRole;
    onView?: (voter: Voter) => void;
    onEdit?: (voter: Voter) => void;
    onDelete?: (voter: Voter) => void;
    onAddNew?: () => void;
    searchTerm?: string;
}

export function VotersTable({ 
    voters, 
    userRole, 
    onView, 
    onEdit, 
    onDelete,
    onAddNew,
    searchTerm = ''
}: VotersTableProps) {
    const isHead = userRole === 'head';

    // Filter voters based on search term
    const filteredVoters = voters.filter(voter => {
        const searchableFields = `${voter.firstname} ${voter.lastname} ${voter.email} ${voter.student_id} ${voter.course}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    return (
        <div className="flex flex-col gap-6">
            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Photo</TableHead>
                            <TableHead>Student ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Course & Year</TableHead>
                            <TableHead>Voting Status</TableHead>
                            <TableHead>Voted At</TableHead>
                            <TableHead>Status</TableHead>
                            {isHead && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {filteredVoters.length > 0 ? (
                            filteredVoters.map((voter) => (
                            <TableRow key={voter.id}>
                                <TableCell>
                                    <Avatar className="h-10 w-10">
                                        <AvatarImage src={voter.photo} alt={`${voter.firstname} ${voter.lastname}`} />
                                        <AvatarFallback>
                                            {voter.firstname[0]}{voter.lastname[0]}
                                        </AvatarFallback>
                                    </Avatar>
                                </TableCell>
                                <TableCell>
                                    <div className="font-mono text-sm">{voter.student_id}</div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div className="font-medium">{voter.firstname} {voter.lastname}</div>
                                        <div className="text-sm text-muted-foreground">{voter.email}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="text-sm">
                                        <div>{voter.course}</div>
                                        <div className="text-muted-foreground">Year {voter.year_level}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant={voter.has_voted ? 'default' : 'secondary'}>
                                        {voter.has_voted ? 'Voted' : 'Not Voted'}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {voter.voted_at ? (
                                        <div className="text-sm text-muted-foreground">
                                            {new Date(voter.voted_at).toLocaleString()}
                                        </div>
                                    ) : (
                                        <span className="text-muted-foreground">-</span>
                                    )}
                                </TableCell>
                                <TableCell>
                                    <Badge variant={voter.status === 'active' ? 'default' : 'secondary'}>
                                        {voter.status}
                                    </Badge>
                                </TableCell>
                                {isHead && (
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onView?.(voter)}
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onEdit?.(voter)}
                                                disabled={voter.has_voted}
                                            >
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onDelete?.(voter)}
                                                disabled={voter.has_voted}
                                                className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                )}
                            </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={isHead ? 7 : 6} className="text-center py-8 text-muted-foreground">
                                    {searchTerm ? 'No voters found matching your search.' : 'No voters found.'}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
