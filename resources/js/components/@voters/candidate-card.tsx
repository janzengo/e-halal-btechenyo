import { Button } from '@/components/ui/button';

interface Candidate {
    id: number;
    name: string;
    party: string;
    photo?: string;
    platform: string;
}

interface CandidateCardProps {
    candidate: Candidate;
    isSelected: boolean;
    isDisabled: boolean;
    onSelect: () => void;
    onViewPlatform: (candidate: Candidate) => void;
}

export function CandidateCard({ 
    candidate, 
    isSelected, 
    isDisabled, 
    onSelect, 
    onViewPlatform 
}: CandidateCardProps) {
    return (
        <div
            className={`group relative rounded-lg border shadow-sm p-6 transition-all ${
                isSelected
                    ? 'border-green-500 bg-green-50 shadow-green-200'
                    : isDisabled
                    ? 'cursor-not-allowed border-gray-200 bg-gray-50 opacity-50'
                    : 'border-gray-200 bg-white hover:border-green-300 hover:shadow-md'
            }`}
        >
            <button
                onClick={onSelect}
                disabled={isDisabled}
                className="w-full text-center cursor-pointer"
            >
                {/* Profile Picture */}
                <div className="flex justify-center mb-4">
                    <div className="h-20 w-20 overflow-hidden rounded-full bg-gray-100 border-2 border-gray-100">
                        <img
                            src={candidate.photo || '/images/profile.jpg'}
                            alt={candidate.name}
                            className="h-full w-full object-cover"
                        />
                    </div>
                </div>
                
                {/* Candidate Name */}
                <h4 className="text-lg font-bold text-gray-900 mb-1">
                    {candidate.name}
                </h4>
                
                {/* Party */}
                <p className="text-sm text-gray-600 mb-4">
                    {candidate.party}
                </p>
            </button>
            
            {/* Platform Button */}
            {candidate.platform && (
                <Button
                    onClick={() => onViewPlatform(candidate)}
                    className="w-full bg-green-600 hover:bg-green-700 text-white font-medium"
                    size="sm"
                >
                    Platform
                </Button>
            )}
        </div>
    );
}
