import { Button } from '@/components/ui/button';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    photo?: string;
    partylist_id: number;
    platform?: string;
}

interface Position {
    id: number;
    description: string;
    max_vote: number;
    priority: number;
}

interface BallotPositionProps {
    position: Position;
    candidates: Candidate[];
    onReset?: () => void;
    showAdminControls?: boolean;
    onMoveUp?: () => void;
    onMoveDown?: () => void;
    canMoveUp?: boolean;
    canMoveDown?: boolean;
    positionNumber?: number;
    totalPositions?: number;
    isMoving?: boolean;
}

export function BallotPosition({
    position,
    candidates,
    onReset,
    showAdminControls = false,
    onMoveUp,
    onMoveDown,
    canMoveUp = true,
    canMoveDown = true,
    positionNumber,
    totalPositions,
    isMoving = false
}: BallotPositionProps) {
    const getPartyName = (partylistId: number) => {
        switch (partylistId) {
            case 1: return 'Angat';
            case 2: return 'Malaya';
            case 3: return 'Sandigan';
            default: return 'Independent';
        }
    };

    return (
        <div className={`position-section bg-white border border-gray-200 rounded-lg p-6 transition-all duration-300 ${
            isMoving ? 'opacity-50 scale-[0.98] cursor-wait' : 'hover:shadow-md'
        }`}>
            {/* Position Header - Only show admin controls when needed */}
            {showAdminControls && (
                <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center space-x-4">
                        <div className="flex flex-col space-y-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={onMoveUp}
                                disabled={!canMoveUp || isMoving}
                                className="h-8 w-8 p-0 moveup hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors"
                                data-id={position.id}
                            >
                                ↑
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={onMoveDown}
                                disabled={!canMoveDown || isMoving}
                                className="h-8 w-8 p-0 movedown hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors"
                                data-id={position.id}
                            >
                                ↓
                            </Button>
                        </div>
                        <div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-1">
                                {position.description}
                            </h3>
                            <p className="text-sm text-gray-600">
                                Maximum votes: {position.max_vote} • Priority: {positionNumber}
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="text-sm font-medium text-gray-900">
                            Position {positionNumber}
                        </div>
                        <div className="text-xs text-gray-500">
                            {totalPositions} total positions
                        </div>
                    </div>
                </div>
            )}

            {/* Position Title - For voter view */}
            {!showAdminControls && (
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-semibold text-green-600">
                        {position.description}
                    </h3>
                    {onReset && (
                        <Button 
                            variant="outline" 
                            size="sm" 
                            className="text-red-600 border-red-300 hover:bg-red-50"
                            onClick={onReset}
                        >
                            Reset
                        </Button>
                    )}
                </div>
            )}

            {/* Candidates for this position */}
            {candidates.length > 0 ? (
                <div className="bg-white border-2 border-gray-300 rounded-lg p-4">
                    {/* Selection Rule */}
                    <div className="flex justify-between items-center mb-4">
                        <p className="text-sm text-gray-600">
                            {position.max_vote === 1 
                                ? 'Select only one candidate'
                                : `You may select up to ${position.max_vote} candidates`
                            }
                        </p>
                        {showAdminControls && onReset && (
                            <Button 
                                variant="outline" 
                                size="sm" 
                                className="text-red-600 border-red-300 hover:bg-red-50"
                                onClick={onReset}
                            >
                                Reset
                            </Button>
                        )}
                    </div>

                    {/* Candidate Grid */}
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                        {candidates.map((candidate) => (
                            <div
                                key={candidate.id}
                                className="bg-white border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow"
                            >
                                {/* Candidate Photo */}
                                <div className="flex justify-center mb-3">
                                    <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border-2 border-gray-200">
                                        {candidate.photo ? (
                                            <img
                                                src={`/storage/${candidate.photo}`}
                                                alt={`${candidate.firstname} ${candidate.lastname}`}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <div className="w-full h-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-medium">
                                                {candidate.firstname[0]}{candidate.lastname[0]}
                                            </div>
                                        )}
                                    </div>
                                </div>
                                
                                {/* Candidate Name */}
                                <h4 className="font-medium text-gray-900 mb-1">
                                    {candidate.firstname} {candidate.lastname}
                                </h4>
                                
                                {/* Party Affiliation */}
                                <p className="text-sm text-gray-600">
                                    {getPartyName(candidate.partylist_id)}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <p>No candidates registered for this position yet.</p>
                </div>
            )}
        </div>
    );
}
