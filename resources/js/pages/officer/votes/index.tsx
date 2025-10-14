import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { VoteDistributionDashboard } from '@/components/@admin/@analytics/vote-distribution-dashboard';
import { 
    dashboardVoteData,
    candidateVoteData,
    electionStats 
} from '@/data/analytics-data';
import { Users, Vote, TrendingUp, Target } from 'lucide-react';

export default function OfficerVotes() {
    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/votes"
            breadcrumbs={[
                { title: 'Reports & Analytics', href: '#' },
                { title: 'Votes', href: '/officers/votes' },
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
                data={dashboardVoteData} 
                candidateData={candidateVoteData} 
            />
        </AdminLayout>
    );
}
