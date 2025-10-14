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
import { Edit, Eye, Trash2, UserPlus } from 'lucide-react';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    email: string;
    photo: string;
    position: string;
    partylist: string;
    year_level: number;
    course: string;
    status: 'active' | 'inactive';
    votes_received: number;
    created_at: string;
}

interface CandidatesTableProps {
    candidates: Candidate[];
    userRole: AdminRole;
    onView?: (candidate: Candidate) => void;
    onEdit?: (candidate: Candidate) => void;
    onDelete?: (candidate: Candidate) => void;
    onAddNew?: () => void;
}

export function CandidatesTable({ 
    candidates, 
    userRole, 
    onView, 
    onEdit, 
    onDelete, 
    onAddNew 
}: CandidatesTableProps) {
    const isHead = userRole === 'head';

    return (
        <div className="space-y-4">
            {/* Header with Add Button */}
            {isHead && (
                <div className="flex justify-between items-center">
                    <h2 className="text-lg font-semibold">Candidates Management</h2>
                    <Button onClick={onAddNew} className="flex items-center gap-2">
                        <UserPlus className="h-4 w-4" />
                        Add New Candidate
                    </Button>
                </div>
            )}

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Photo</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Position</TableHead>
                            <TableHead>Partylist</TableHead>
                            <TableHead>Course & Year</TableHead>
                            <TableHead>Votes</TableHead>
                            <TableHead>Status</TableHead>
                            {isHead && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {candidates.map((candidate) => (
                            <TableRow key={candidate.id}>
                                <TableCell>
                                    <Avatar className="h-10 w-10">
                                        <AvatarImage src={candidate.photo} alt={`${candidate.firstname} ${candidate.lastname}`} />
                                        <AvatarFallback>
                                            {candidate.firstname[0]}{candidate.lastname[0]}
                                        </AvatarFallback>
                                    </Avatar>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div className="font-medium">{candidate.firstname} {candidate.lastname}</div>
                                        <div className="text-sm text-muted-foreground">{candidate.email}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{candidate.position}</Badge>
                                </TableCell>
                                <TableCell>{candidate.partylist}</TableCell>
                                <TableCell>
                                    <div className="text-sm">
                                        <div>{candidate.course}</div>
                                        <div className="text-muted-foreground">Year {candidate.year_level}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span className="font-medium">{candidate.votes_received}</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant={candidate.status === 'active' ? 'default' : 'secondary'}>
                                        {candidate.status}
                                    </Badge>
                                </TableCell>
                                {isHead && (
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onView?.(candidate)}
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onEdit?.(candidate)}
                                            >
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onDelete?.(candidate)}
                                                className="text-red-600 hover:text-red-700"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            {candidates.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                    No candidates found.
                </div>
            )}
        </div>
    );
}
