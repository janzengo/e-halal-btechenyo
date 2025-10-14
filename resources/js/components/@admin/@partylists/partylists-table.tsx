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
import { AdminRole } from '@/types/ehalal';
import { Edit, Eye, Trash2, Plus } from 'lucide-react';

interface Partylist {
    id: number;
    name: string;
    acronym: string;
    color: string;
    description: string;
    candidates_count: number;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

interface PartylistsTableProps {
    partylists: Partylist[];
    userRole: AdminRole;
    onView?: (partylist: Partylist) => void;
    onEdit?: (partylist: Partylist) => void;
    onDelete?: (partylist: Partylist) => void;
    onAddNew?: () => void;
}

export function PartylistsTable({ 
    partylists, 
    userRole, 
    onView, 
    onEdit, 
    onDelete, 
    onAddNew 
}: PartylistsTableProps) {
    const isHead = userRole === 'head';

    return (
        <div className="space-y-4">
            {/* Header with Add Button */}
            {isHead && (
                <div className="flex justify-between items-center">
                    <h2 className="text-lg font-semibold">Partylists Management</h2>
                    <Button onClick={onAddNew} className="flex items-center gap-2">
                        <Plus className="h-4 w-4" />
                        Add New Partylist
                    </Button>
                </div>
            )}

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Acronym</TableHead>
                            <TableHead>Color</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Candidates</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created</TableHead>
                            {isHead && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {partylists.map((partylist) => (
                            <TableRow key={partylist.id}>
                                <TableCell>
                                    <div className="font-medium">{partylist.name}</div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" className="font-mono">
                                        {partylist.acronym}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        <div 
                                            className="w-4 h-4 rounded-full border"
                                            style={{ backgroundColor: partylist.color }}
                                        />
                                        <span className="text-sm text-muted-foreground">
                                            {partylist.color}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="max-w-xs truncate text-sm text-muted-foreground">
                                        {partylist.description}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{partylist.candidates_count}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant={partylist.status === 'active' ? 'default' : 'secondary'}>
                                        {partylist.status}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="text-sm text-muted-foreground">
                                        {new Date(partylist.created_at).toLocaleDateString()}
                                    </div>
                                </TableCell>
                                {isHead && (
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onView?.(partylist)}
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onEdit?.(partylist)}
                                            >
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => onDelete?.(partylist)}
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

            {partylists.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                    No partylists found.
                </div>
            )}
        </div>
    );
}
