import { StatsCard } from '@/components/@admin/@dashboard/stats-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AdminLayout from '@/layouts/admin/admin-layout';
import { DashboardChart } from '@/components/@admin/@analytics/dashboard-chart';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect, useState } from 'react';
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

function formatSince(iso: string | null | undefined, now: Date): string {
    if (!iso) return 'Now';
    const start = new Date(iso);
    const diffMs = now.getTime() - start.getTime();
    if (Number.isNaN(diffMs) || diffMs < 0) return 'Now';
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} min${minutes !== 1 ? 's' : ''} ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hr${hours !== 1 ? 's' : ''} ago`;
    const days = Math.floor(hours / 24);
    return `${days} day${days !== 1 ? 's' : ''} ago`;
}

interface DashboardProps extends Record<string, any> {
    lastStatusChange?: string | null;
    endTime?: string | null;
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
    const [now, setNow] = useState<Date>(new Date());

    useEffect(() => {
        const id = setInterval(() => setNow(new Date()), 60_000); // update every minute
        return () => clearInterval(id);
    }, []);
    const { totalVoters, votedVoters, totalCandidates, totalPositions, electionStatus, voteDistribution, electionName, lastStatusChange, endTime } = usePage<DashboardProps>().props;
    const { auth } = usePage<{ auth: { user: { firstname: string; lastname: string; role: string } | null } }>().props;
    const { isPageLoading } = useLoading();

    // Show login success toast on first load if there's no server flash
    const { flash } = usePage<{ flash?: { success?: string; error?: string } }>().props;
    useEffect(() => {
        // Check if this is a fresh login by checking session storage and absence of flash success
        const hasShownLoginToast = sessionStorage.getItem('head-login-toast-shown');
        if (!hasShownLoginToast && auth.user && !flash?.success) {
            const adminName = `${auth.user.firstname} ${auth.user.lastname}`;
            toast.success(`Welcome back, ${adminName}!`, {
                description: 'You have successfully logged in to the system.',
                duration: 4000,
            });
            sessionStorage.setItem('head-login-toast-shown', 'true');
        }
    }, [auth.user, flash?.success]);

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
                               <span className="text-xs text-muted-foreground">{formatSince(lastStatusChange ?? null, now)}</span>
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
