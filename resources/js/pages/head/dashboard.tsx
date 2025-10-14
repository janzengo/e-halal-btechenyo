import { StatsCard } from '@/components/@admin/@dashboard/stats-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AdminLayout from '@/layouts/admin/admin-layout';
import { DashboardChart } from '@/components/@admin/@analytics/dashboard-chart';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect } from 'react';
import { 
    CheckCircle, 
    List, 
    Users, 
    Users2,
    BarChart3
} from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonStatsCard, SkeletonChart } from '@/components/@admin/@loading/skeleton-cards';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/head/dashboard',
    },
];

interface DashboardProps extends Record<string, any> {
    totalVoters: number;
    votedVoters: number;
    totalCandidates: number;
    totalPositions: number;
    electionStatus: string;
    voteDistribution: Array<{
        position: string;
        votes: number;
        candidates_count: number;
    }>;
    electionName: string;
}

export default function HeadDashboard() {
    const { totalVoters, votedVoters, totalCandidates, totalPositions, electionStatus, voteDistribution, electionName } = usePage<DashboardProps>().props;
    const { auth } = usePage<{ auth: { user: { firstname: string; lastname: string; role: string } | null } }>().props;
    const { isPageLoading } = useLoading();

    // Show login success toast on first load
    useEffect(() => {
        // Check if this is a fresh login by checking session storage
        const hasShownLoginToast = sessionStorage.getItem('head-login-toast-shown');
        if (!hasShownLoginToast && auth.user) {
            const adminName = `${auth.user.firstname} ${auth.user.lastname}`;
            toast.success(`Welcome back, ${adminName}!`, {
                description: 'You have successfully logged in to the system.',
                duration: 4000,
            });
            sessionStorage.setItem('head-login-toast-shown', 'true');
        }
    }, [auth.user]);

    return (
        <AdminLayout 
            breadcrumbs={breadcrumbs} 
            userRole="head"
            currentPath="/head/dashboard"
        >
            <Head title="Head Dashboard" />
            
            <div className="space-y-8">
                {/* Welcome Message */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Dashboard Control Panel</h1>
                    <p className="text-muted-foreground">
                        Welcome back{auth.user ? `, ${auth.user.firstname} ${auth.user.lastname}` : ''}
                    </p>
                    {electionName !== 'No Active Election' && (
                        <p className="text-sm text-muted-foreground mt-1">
                            {electionName}
                        </p>
                    )}
                </div>

                {/* Stats Cards */}
                {isPageLoading ? (
                    <SkeletonStatsCard />
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <StatsCard
                            title="Positions"
                            value={totalPositions}
                            icon={List}
                            description="Total positions available"
                        />
                        <StatsCard
                            title="Candidates"
                            value={totalCandidates}
                            icon={Users}
                            description="Total candidates registered"
                        />
                        <StatsCard
                            title="Total Voters"
                            value={totalVoters}
                            icon={Users2}
                            description="Registered voters"
                        />
                        <StatsCard
                            title="Voters Voted"
                            value={votedVoters}
                            icon={CheckCircle}
                            description="Voters who have voted"
                        />
                    </div>
                )}

                {/* Vote Distribution Chart */}
                {isPageLoading ? (
                    <div className="mt-8">
                        <SkeletonChart />
                    </div>
                ) : voteDistribution.length > 0 ? (
                    <div className="mt-8">
                        <DashboardChart 
                            data={voteDistribution.map(item => ({
                                position: item.position,
                                votes: item.votes,
                                candidates: item.candidates_count
                            }))} 
                            title="Vote Distribution by Position"
                        />
                    </div>
                ) : (
                    <Empty className="border my-8">
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <BarChart3 />
                            </EmptyMedia>
                            <EmptyTitle>No Vote Data Available</EmptyTitle>
                            <EmptyDescription>
                                Vote distribution will appear here once voting begins. Set up positions and candidates to get started.
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                )}

                {/* Election Status Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Election Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center space-x-4">
                                <div className={`h-2 w-2 rounded-full ${electionStatus === 'active' ? 'bg-green-500' : electionStatus === 'idle' ? 'bg-gray-400' : 'bg-yellow-500'}`}></div>
                                <div className="flex-1">
                                    <p className="text-sm capitalize">{electionStatus === 'active' ? 'Election is currently active' : electionStatus === 'idle' ? 'No active election' : 'Election setup in progress'}</p>
                                    <p className="text-xs text-muted-foreground">{electionName}</p>
                                </div>
                                <span className="text-xs text-muted-foreground">Now</span>
                            </div>
                            {votedVoters > 0 && (
                                <div className="flex items-center space-x-4">
                                    <div className="h-2 w-2 rounded-full bg-blue-500"></div>
                                    <div className="flex-1">
                                        <p className="text-sm">{votedVoters} voter{votedVoters !== 1 ? 's' : ''} have cast their vote</p>
                                        <p className="text-xs text-muted-foreground">
                                            {totalVoters > 0 ? `${((votedVoters / totalVoters) * 100).toFixed(1)}% turnout` : ''}
                                        </p>
                                    </div>
                                </div>
                            )}
                            {totalPositions === 0 && (
                                <div className="flex items-center space-x-4">
                                    <div className="h-2 w-2 rounded-full bg-yellow-500"></div>
                                    <div className="flex-1">
                                        <p className="text-sm">No positions set up yet</p>
                                        <p className="text-xs text-muted-foreground">Create positions to get started</p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
