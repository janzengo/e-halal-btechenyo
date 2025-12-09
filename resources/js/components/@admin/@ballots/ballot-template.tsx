import { BallotPosition } from './ballot-position';

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    photo: string;
    partylist_id: number;
    platform?: string;
    position_id: number;
}

interface Position {
    id: number;
    description: string;
    max_vote: number;
    priority: number;
}

interface BallotTemplateProps {
    positions: Position[];
    candidates: Candidate[];
    showAdminControls?: boolean;
    onPositionMove?: (positionId: number, direction: 'up' | 'down') => void;
    onReset?: (positionId: number) => void;
    isMoving?: boolean;
}

export function BallotTemplate({
    positions,
    candidates,
    showAdminControls = false,
    onPositionMove,
    onReset,
    isMoving = false
}: BallotTemplateProps) {
    const getCandidatesForPosition = (positionId: number) => {
        return candidates.filter(candidate => candidate.position_id === positionId);
    };

    const handleMoveUp = (positionId: number) => {
        if (onPositionMove) {
            onPositionMove(positionId, 'up');
        }
    };

    const handleMoveDown = (positionId: number) => {
        if (onPositionMove) {
            onPositionMove(positionId, 'down');
        }
    };

    const handleReset = (positionId: number) => {
        if (onReset) {
            onReset(positionId);
        }
    };

    if (positions.length === 0) {
        return (
            <div className="text-center py-12">
                <div className="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <span className="text-4xl text-gray-400">📋</span>
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-2">No Ballot Positions Found</h3>
                <p className="text-gray-500 mb-4">
                    There are no positions configured for the ballot yet. 
                    Add positions first to arrange them in the ballot.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {positions.map((position, index) => (
                <BallotPosition
                    key={position.id}
                    position={position}
                    candidates={getCandidatesForPosition(position.id)}
                    showAdminControls={showAdminControls}
                    onMoveUp={() => handleMoveUp(position.id)}
                    onMoveDown={() => handleMoveDown(position.id)}
                    canMoveUp={index > 0}
                    canMoveDown={index < positions.length - 1}
                    positionNumber={index + 1}
                    totalPositions={positions.length}
                    isMoving={isMoving}
                    onReset={() => handleReset(position.id)}
                />
            ))}
        </div>
    );
}
