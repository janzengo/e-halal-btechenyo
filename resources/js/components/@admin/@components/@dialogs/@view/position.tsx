import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { AlertCircle, X, Users, Search, User, Calendar, MapPin, Award } from 'lucide-react';
import { SkeletonDialogList } from '@/components/@admin/@loading/skeleton-cards';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    partylist: {
        id: number;
        name: string;
        color: string;
    } | null;
    photo: string | null;
    platform: string | null;
    created_at: string;
}

interface Position {
    id: number;
    title: string;
    description: string;
    max_winners: number;
    candidates_count: number;
}

interface PositionViewDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    position: Position | null;
    candidates: Candidate[];
    loading: boolean;
}

export function PositionViewDialog({
    open,
    onOpenChange,
    position,
    candidates,
    loading,
}: PositionViewDialogProps) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredCandidates = candidates.filter(candidate => {
        const fullName = `${candidate.firstname} ${candidate.lastname}`.toLowerCase();
        const partylistName = candidate.partylist?.name.toLowerCase() || '';
        return fullName.includes(searchTerm.toLowerCase()) || partylistName.includes(searchTerm.toLowerCase());
    });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Award className="h-5 w-5 text-green-600" />
                        {position ? `Candidates for ${position.title}` : 'View Candidates'}
                    </DialogTitle>
                    <DialogDescription>
                        {position ?
                            `Browse all candidates running for the "${position.title}" position.` :
                            'View candidates details.'
                        }
                    </DialogDescription>
                </DialogHeader>

                <div className="flex-1 overflow-hidden flex flex-col">
                    {/* Search Input */}
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                        <Input
                            type="text"
                            placeholder="Search candidates by name or partylist..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="pl-10"
                        />
                    </div>

                    {/* Results count */}
                    <div className="text-sm text-gray-600 mb-4">
                        Showing {filteredCandidates.length} of {candidates.length} candidates
                    </div>

                    {/* Candidates List */}
                    <div className="flex-1 overflow-y-auto">
                        {loading ? (
                            <SkeletonDialogList />
                        ) : filteredCandidates.length === 0 ? (
                            <Empty className="border my-8">
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <Users />
                                    </EmptyMedia>
                                    <EmptyTitle>
                                        {searchTerm ? 'No candidates found' : 'No candidates yet'}
                                    </EmptyTitle>
                                    <EmptyDescription>
                                        {searchTerm
                                            ? 'Try adjusting your search criteria to find candidates.'
                                            : 'Candidates will appear here once they are assigned to this position.'
                                        }
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {filteredCandidates.map((candidate) => (
                                    <Card key={candidate.id} className="hover:shadow-md transition-shadow">
                                        <CardContent className="p-4">
                                            <div className="flex items-center gap-4">
                                                <div className="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border">
                                                    {candidate.photo ? (
                                                        <img
                                                            src={`/storage/${candidate.photo}`}
                                                            alt={`${candidate.firstname} ${candidate.lastname}`}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="h-full w-full bg-green-100 text-green-700 flex items-center justify-center text-lg font-medium">
                                                            {candidate.firstname[0]}{candidate.lastname[0]}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <h4 className="font-semibold text-lg">
                                                        {candidate.firstname} {candidate.lastname}
                                                    </h4>
                                                    {candidate.partylist && (
                                                        <Badge 
                                                            variant="secondary" 
                                                            className="mt-1"
                                                            style={{ 
                                                                backgroundColor: `${candidate.partylist.color}20`,
                                                                color: candidate.partylist.color,
                                                                borderColor: candidate.partylist.color
                                                            }}
                                                        >
                                                            {candidate.partylist.name}
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                            {candidate.platform && (
                                                <p className="text-sm text-gray-600 mt-3 line-clamp-2">
                                                    {candidate.platform}
                                                </p>
                                            )}
                                            <div className="flex items-center text-xs text-gray-500 mt-3 pt-3 border-t">
                                                <Calendar className="h-3 w-3 mr-1" />
                                                Joined: {new Date(candidate.created_at).toLocaleDateString()}
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex justify-end pt-4 border-t">
                    <Button 
                        variant="outline" 
                        onClick={() => onOpenChange(false)}
                        className="cursor-pointer"
                    >
                        <X className="h-4 w-4 mr-2" /> Close
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

