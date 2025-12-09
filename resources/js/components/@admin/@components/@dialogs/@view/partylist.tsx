import { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { X, Users, Search, Calendar } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    position: {
        id: number;
        title: string;
    };
    photo?: string;
    platform?: string;
    created_at: string;
}

interface PartylistViewDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    partylist: {
        id: number;
        name: string;
        color: string;
        candidates_count: number;
    } | null;
    candidates: Candidate[];
    loading?: boolean;
}

export function PartylistViewDialog({
    open,
    onOpenChange,
    partylist,
    candidates = [],
    loading = false
}: PartylistViewDialogProps) {
    const [searchTerm, setSearchTerm] = useState('');

    // Reset search when dialog opens/closes
    useEffect(() => {
        if (!open) {
            setSearchTerm('');
        }
    }, [open]);

    // Filter candidates based on search term
    const filteredCandidates = candidates.filter(candidate => {
        const searchableFields = `${candidate.firstname} ${candidate.lastname} ${candidate.position.title}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-3">
                        <div 
                            className="h-8 w-8 rounded-lg flex items-center justify-center border-2"
                            style={{ 
                                backgroundColor: partylist?.color || '#3B82F6', 
                                borderColor: (partylist?.color || '#3B82F6') + '40' 
                            }}
                        >
                            <Users className="h-4 w-4 text-white" />
                        </div>
                        <div>
                            <span className="text-xl font-bold">{partylist?.name || 'Partylist'}</span>
                            <p className="text-sm text-gray-500 font-normal">
                                {partylist?.candidates_count || 0} candidate(s)
                            </p>
                        </div>
                    </DialogTitle>
                    <DialogDescription>
                        View all candidates belonging to this partylist
                    </DialogDescription>
                </DialogHeader>

                <div className="flex-1 overflow-hidden flex flex-col">
                    {/* Search - Only show when not loading */}
                    {!loading && (
                        <div className="relative mb-4">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                            <Input
                                type="text"
                                placeholder="Search candidates..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    )}

                    {/* Results count - Only show when not loading */}
                    {!loading && (
                        <div className="text-sm text-gray-600 mb-4">
                            Showing {filteredCandidates.length} of {candidates.length} candidates
                        </div>
                    )}

                    {/* Candidates List */}
                    <div className="flex-1 overflow-y-auto">
                        {loading ? (
                            <div className="flex items-center justify-center py-12">
                                <div className="flex flex-col items-center gap-3">
                                    <Spinner className="h-8 w-8" />
                                    <p className="text-sm text-gray-500">Loading candidates...</p>
                                </div>
                            </div>
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
                                            : 'Candidates will appear here once they are assigned to this partylist.'
                                        }
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {filteredCandidates.map((candidate) => (
                                    <Card key={candidate.id} className="hover:shadow-md transition-shadow">
                                        <CardContent className="p-4">
                                            <div className="flex items-start gap-3">
                                                {/* Candidate Photo */}
                                                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                                    {candidate.photo ? (
                                                        <img 
                                                            src={`/storage/${candidate.photo}`} 
                                                            alt={`${candidate.firstname} ${candidate.lastname}`}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="h-full w-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-medium">
                                                            {candidate.firstname[0]}{candidate.lastname[0]}
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Candidate Info */}
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="font-semibold text-gray-900 truncate">
                                                        {candidate.firstname} {candidate.lastname}
                                                    </h3>
                                                    <Badge variant="outline" className="mt-1">
                                                        {candidate.position.title}
                                                    </Badge>
                                                    
                                                    {candidate.platform && (
                                                        <p className="text-sm text-gray-600 mt-2 line-clamp-2">
                                                            {candidate.platform}
                                                        </p>
                                                    )}

                                                    <div className="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3" />
                                                            {new Date(candidate.created_at).toLocaleDateString()}
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
