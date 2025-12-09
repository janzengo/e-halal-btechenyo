import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/admin-layout';
import { BallotTemplate } from '@/components/@admin/@ballots/ballot-template';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import { ClipboardList, ListPlus } from 'lucide-react';
import axios from 'axios';

interface Position {
    id: number;
    title: string;
    description: string;
    max_winners: number;
    max_vote: number;
    priority: number;
    candidates_count: number;
    created_at?: string;
    updated_at?: string;
}

interface Candidate {
    id: number;
    firstname: string;
    lastname: string;
    position?: string;
    position_id: number;
    partylist?: string;
    partylist_id: number;
    photo: string;
    platform?: string;
    votes: number;
    created_at?: string;
    updated_at?: string;
}

interface BallotSettingsProps extends Record<string, any> {
    positions: Position[];
    candidates: Candidate[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/head/dashboard',
    },
    {
        title: 'Ballot Settings',
        href: '/head/ballots',
    },
];

export default function BallotSettings() {
    const { positions: realPositions, candidates: realCandidates } = usePage<BallotSettingsProps>().props;
    const [positions, setPositions] = useState(realPositions);
    const [isMoving, setIsMoving] = useState(false);

    const handlePositionMove = async (positionId: number, direction: 'up' | 'down') => {
        if (isMoving) return;
        
        const currentIndex = positions.findIndex(p => p.id === positionId);
        if (
            (direction === 'up' && currentIndex <= 0) ||
            (direction === 'down' && currentIndex >= positions.length - 1)
        ) {
            return;
        }

        setIsMoving(true);
        
        // Create new array with swapped positions
        const newPositions = [...positions];
        const targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
        const temp = newPositions[currentIndex];
        newPositions[currentIndex] = newPositions[targetIndex];
        newPositions[targetIndex] = temp;
        
        // Update local state immediately for smooth UI
        setPositions(newPositions);
        
        // Prepare data for backend - update priorities
        const updatedPositions = newPositions.map((pos, index) => ({
            id: pos.id,
            priority: index + 1
        }));
        
        try {
            // Send to backend using axios (handles CSRF automatically)
            const response = await axios.post('/head/positions/reorder', {
                positions: updatedPositions
            });

            if (response.data.success) {
                toast.success('Position order updated successfully!', {
                    description: `${newPositions.find(p => p.id === positionId)?.title || 'Position'} moved ${direction}`
                });
            } else {
                throw new Error(response.data.message || 'Failed to update position order');
            }
        } catch (error) {
            console.error('Error reordering positions:', error);
            toast.error('Failed to update position order', {
                description: 'Your changes have been reverted'
            });
            // Revert to original positions on error
            setPositions(realPositions);
        } finally {
            setIsMoving(false);
        }
    };

    const handleReset = (positionId: number) => {
        console.log('Reset position:', positionId);
        // Implement reset logic here
    };

    const handleAddPosition = () => {
        router.visit('/head/positions');
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/ballots"
            breadcrumbs={breadcrumbs}
        >
            <Head title="Ballot Settings" />

            {/* Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Ballot Settings</h2>
                    <p className="text-gray-600">Arrange the order of positions in the ballot</p>
                </div>
                <Button onClick={handleAddPosition} variant="outlinePrimary">
                    <ListPlus className="h-4 w-4" />
                    Add Position
                </Button>
            </div>

            {positions.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <ClipboardList />
                        </EmptyMedia>
                        <EmptyTitle>No Positions Configured</EmptyTitle>
                        <EmptyDescription>
                            Set up positions for your election ballot. Positions will appear in the order you arrange them.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={handleAddPosition}>
                            <ListPlus className="h-4 w-4" />
                            Create First Position
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                /* Ballot Content */
                <div className="w-full max-w-7xl mx-auto">
                    <BallotTemplate
                        positions={positions}
                        candidates={realCandidates}
                        showAdminControls={true}
                        onPositionMove={handlePositionMove}
                        onReset={handleReset}
                        isMoving={isMoving}
                    />
                </div>
            )}
        </AdminLayout>
    );
}
