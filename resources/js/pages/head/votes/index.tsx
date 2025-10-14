import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { VoteDistributionDashboard } from '@/components/@admin/@analytics/vote-distribution-dashboard';
import { Users, Vote, TrendingUp, Target } from 'lucide-react';

interface VotesProps extends Record<string, any> {
    voteDistribution: Array<{
        position: string;
        votes: number;
        candidates: number;
    }>;
    candidateVoteData: Array<{
        name: string;
        position: string;
        votes: number;
    }>;
    electionStats: {
        totalVoters: number;
        totalVoted: number;
        votingRate: number;
        totalPositions: number;
        totalCandidates: number;
        totalPartylists: number;
    };
}

export default function HeadVotes() {
    const { voteDistribution, candidateVoteData, electionStats } = usePage<VotesProps>().props;
    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/votes"
            breadcrumbs={[
                { title: 'Reports & Analytics', href: '#' },
                { title: 'Votes', href: '/head/votes' },
            ]}
        >
            <Head title="Voting Analytics" />

            {/* Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Voting Analytics</h2>
                    <p className="text-gray-600">Comprehensive voting statistics and trends</p>
                </div>
            </div>

            {voteDistribution.length === 0 && electionStats.totalCandidates === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Vote />
                        </EmptyMedia>
                        <EmptyTitle>No Voting Data Available</EmptyTitle>
                        <EmptyDescription>
                            Voting analytics will appear here once positions and candidates are set up and voting begins.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={() => window.location.href = '/head/positions'}>
                            <Target className="h-4 w-4" />
                            Set Up Positions
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                <>
                    {/* Statistics Cards */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Voters</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{electionStats.totalVoters.toLocaleString()}</div>
                        <p className="text-xs text-muted-foreground">Registered voters</p>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Votes Cast</CardTitle>
                        <Vote className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{electionStats.totalVoted.toLocaleString()}</div>
                        <p className="text-xs text-muted-foreground">
                            {electionStats.votingRate}% participation rate
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Positions</CardTitle>
                        <Target className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{electionStats.totalPositions}</div>
                        <p className="text-xs text-muted-foreground">Available positions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Candidates</CardTitle>
                        <TrendingUp className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{electionStats.totalCandidates}</div>
                        <p className="text-xs text-muted-foreground">From {electionStats.totalPartylists} partylists</p>
                    </CardContent>
                </Card>
            </div>

            {/* Vote Distribution Dashboard - Matching the image design */}
            <VoteDistributionDashboard 
                data={voteDistribution.map((item, index) => ({
                    position: item.position,
                    votes: item.votes,
                    color: ['#ec4899', '#3b82f6', '#eab308', '#10b981', '#f59e0b'][index % 5]
                }))} 
                candidateData={voteDistribution.map((item, index) => ({
                    position: item.position,
                    candidates: candidateVoteData
                        .filter(candidate => candidate.position === item.position)
                        .map((candidate, candidateIndex) => ({
                            name: candidate.name,
                            votes: candidate.votes,
                            color: ['#ec4899', '#3b82f6', '#eab308', '#10b981', '#f59e0b'][candidateIndex % 5]
                        }))
                }))} 
            />
                </>
            )}
        </AdminLayout>
    );
}
