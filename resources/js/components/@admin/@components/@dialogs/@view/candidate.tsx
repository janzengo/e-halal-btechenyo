import React from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { User, X } from 'lucide-react';
import { SkeletonCandidateView } from '@/components/@admin/@loading/skeleton-cards';

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

interface CandidateViewDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    candidate: Candidate | null;
}

export function CandidateViewDialog({
    open,
    onOpenChange,
    candidate,
}: CandidateViewDialogProps) {
    if (!candidate) {
        return (
            <Dialog open={open} onOpenChange={onOpenChange}>
                <DialogContent className="sm:max-w-md">
                    <Empty>
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <User />
                            </EmptyMedia>
                            <EmptyTitle>No Candidate Selected</EmptyTitle>
                            <EmptyDescription>
                                Please select a candidate to view details.
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <User className="h-5 w-5" />
                        Candidate Details
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-4">
                    {/* Candidate Info Card */}
                    <Card className="border-green-100">
                        <CardHeader className="pb-2">
                            <div className="flex flex-col items-center text-center gap-3">
                                <Avatar className="h-20 w-20 ring-2 ring-green-100">
                                    <AvatarImage 
                                        src={candidate.photo ? `/storage/${candidate.photo}` : undefined} 
                                        alt={`${candidate.firstname} ${candidate.lastname}`} 
                                    />
                                    <AvatarFallback className="bg-green-100 text-green-700 text-xl">
                                        {candidate.firstname[0]}{candidate.lastname[0]}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="w-full">
                                    <h3 className="text-lg font-bold text-gray-900">
                                        {candidate.firstname} {candidate.lastname}
                                    </h3>
                                    <p className="text-sm text-gray-600 mt-1">{candidate.position}</p>
                                    <div className="flex items-center justify-center gap-2 mt-2">
                                        <Badge variant="secondary" className="bg-green-100 text-green-700 text-xs">
                                            {candidate.partylist}
                                        </Badge>
                                        <Badge variant={candidate.status === 'active' ? 'default' : 'secondary'} className="text-xs">
                                            {candidate.status}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Platform Section */}
                    {candidate.platform && (
                        <Card className="border-green-100">
                            <CardHeader>
                                <h4 className="font-semibold text-gray-900 text-md">Platform</h4>
                            </CardHeader>
                            <CardContent>
                                <p className="text-gray-700 text-sm leading-relaxed">
                                    {candidate.platform}
                                </p>
                            </CardContent>
                        </Card>
                    )}

                    {/* Candidate Information */}
                    <Card className="border-green-100">
                        <CardHeader>
                            <h4 className="font-semibold text-gray-900 text-md">Information</h4>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <span className="text-xs text-gray-500">Position</span>
                                    <p className="font-medium text-sm">{candidate.position}</p>
                                </div>
                                <div>
                                    <span className="text-xs text-gray-500">Partylist</span>
                                    <p className="font-medium text-sm">{candidate.partylist}</p>
                                </div>
                                <div>
                                    <span className="text-xs text-gray-500">Status</span>
                                    <p className="font-medium text-sm capitalize">{candidate.status}</p>
                                </div>
                                <div>
                                    <span className="text-xs text-gray-500">Date Added</span>
                                    <p className="font-medium text-sm">
                                        {new Date(candidate.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Close Button */}
                <div className="flex justify-end pt-3">
                    <Button 
                        variant="outline" 
                        size="sm"
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
