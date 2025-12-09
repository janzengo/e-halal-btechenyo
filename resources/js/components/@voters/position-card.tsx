import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { RotateCcw } from 'lucide-react';
import { CandidateCard } from './candidate-card';

interface Candidate {
    id: number;
    name: string;
    party: string;
    photo?: string;
    platform: string;
}

interface Position {
    id: number;
    name: string;
    max_vote: number;
    candidates: Candidate[];
}

interface PositionCardProps {
    position: Position;
    selectedCandidates: number[];
    onCandidateSelect: (positionId: number, candidateId: number, maxVote: number) => void;
    onReset: (positionId: number) => void;
    onViewPlatform: (candidate: Candidate) => void;
}

export function PositionCard({ 
    position, 
    selectedCandidates, 
    onCandidateSelect, 
    onReset, 
    onViewPlatform 
}: PositionCardProps) {
    const isMaxSelected = selectedCandidates.length >= position.max_vote;

    return (
        <Card className="overflow-hidden">
            <div className="p-6 pb-0 pt-0">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="text-lg font-semibold text-green-700">{position.name}</h3>
                        <p className="mt-1 text-sm text-gray-600">
                            {position.max_vote === 1 
                                ? 'Select one candidate' 
                                : `Select up to ${position.max_vote} candidates`}
                        </p>
                    </div>
                    {selectedCandidates.length > 0 && (
                        <Button
                            onClick={() => onReset(position.id)}
                            variant="destructive"
                            size="sm"
                        >
                            <RotateCcw className="h-4 w-4" />
                            Reset
                        </Button>
                    )}
                </div>
            </div>
            <div className="px-6 pb-6">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {position.candidates.map((candidate) => {
                        const isSelected = selectedCandidates.includes(candidate.id);
                        const isDisabled = !isSelected && isMaxSelected && position.max_vote > 1;

                        return (
                            <CandidateCard
                                key={candidate.id}
                                candidate={candidate}
                                isSelected={isSelected}
                                isDisabled={isDisabled}
                                onSelect={() => onCandidateSelect(position.id, candidate.id, position.max_vote)}
                                onViewPlatform={onViewPlatform}
                            />
                        );
                    })}
                </div>
            </div>
        </Card>
    );
}
