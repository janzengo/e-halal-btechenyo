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
import { Eye, Download } from 'lucide-react';

interface Vote {
    id: number;
    voter_id: number;
    voter_name: string;
    voter_email: string;
    voter_photo: string;
    candidate_id: number;
    candidate_name: string;
    candidate_photo: string;
    position: string;
    partylist: string;
    voted_at: string;
    ip_address: string;
}

interface VotesTableProps {
    votes: Vote[];
    userRole: AdminRole;
    onView?: (vote: Vote) => void;
    onExport?: () => void;
}

export function VotesTable({ 
    votes, 
    userRole, 
    onView, 
    onExport 
}: VotesTableProps) {
    const isHead = userRole === 'head';

    return (
        <div className="flex flex-col gap-6">
            {/* Header with Export Button */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Votes Records</h2>
                    <p className="text-gray-600">View and manage all vote records</p>
                </div>
                <div className="flex gap-2">
                    <Button onClick={onExport} variant="outline" className="flex items-center gap-2">
                        <Download className="h-4 w-4" />
                        Export Votes
                    </Button>
                </div>
            </div>

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Voter</TableHead>
                            <TableHead>Candidate</TableHead>
                            <TableHead>Position</TableHead>
                            <TableHead>Partylist</TableHead>
                            <TableHead>Voted At</TableHead>
                            <TableHead>IP Address</TableHead>
                            {isHead && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {votes.map((vote) => (
                            <TableRow key={vote.id}>
                                <TableCell>
                                    <div className="flex items-center gap-3">
                                        <Avatar className="h-8 w-8">
                                            <AvatarImage src={vote.voter_photo} alt={vote.voter_name} />
                                            <AvatarFallback>
                                                {vote.voter_name.split(' ').map(n => n[0]).join('')}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div className="font-medium text-sm">{vote.voter_name}</div>
                                            <div className="text-xs text-muted-foreground">{vote.voter_email}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-3">
                                        <Avatar className="h-8 w-8">
                                            <AvatarImage src={vote.candidate_photo} alt={vote.candidate_name} />
                                            <AvatarFallback>
                                                {vote.candidate_name.split(' ').map(n => n[0]).join('')}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div className="font-medium text-sm">{vote.candidate_name}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{vote.position}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{vote.partylist}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="text-sm">
                                        {new Date(vote.voted_at).toLocaleString()}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="font-mono text-xs text-muted-foreground">
                                        {vote.ip_address}
                                    </div>
                                </TableCell>
                                {isHead && (
                                    <TableCell>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => onView?.(vote)}
                                        >
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            {votes.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                    No votes recorded yet.
                </div>
            )}
        </div>
    );
}
