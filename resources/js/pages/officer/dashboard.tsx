import { StatsCard } from '@/components/@admin/@dashboard/stats-card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/admin-layout';
import { DashboardChart } from '@/components/@admin/@analytics/dashboard-chart';
import { dummyElectionStats, getCurrentAdmin } from '@/data/dummy-data';
import { dashboardVoteData } from '@/data/analytics-data';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect } from 'react';
import { 
    CheckCircle, 
    GraduationCap, 
    List, 
    Users, 
    Users2 
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/officers/dashboard',
    },
];

export default function OfficerDashboard() {
    const admin = getCurrentAdmin('officer');
    const stats = dummyElectionStats;

    // Show login success toast on first load
    useEffect(() => {
        // Check if this is a fresh login by checking session storage
        const hasShownLoginToast = sessionStorage.getItem('officer-login-toast-shown');
        if (!hasShownLoginToast) {
            toast.success('Welcome back, Election Officer!', {
                description: 'You have successfully logged in to the system.',
                duration: 4000,
            });
            sessionStorage.setItem('officer-login-toast-shown', 'true');
        }
    }, []);

    return (
        <AdminLayout 
            breadcrumbs={breadcrumbs} 
            userRole="officer"
            currentPath="/officers/dashboard"
        >
            <Head title="Officer Dashboard" />
            
            <div className="space-y-8">
                {/* Welcome Message */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Dashboard Control Panel</h1>
                    <p className="text-muted-foreground">
                        Welcome back, {admin.firstname} {admin.lastname} ({admin.role})
                    </p>
                </div>

                {/* Success Message */}
                <Alert className="border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                    <CheckCircle className="h-4 w-4" />
                    <AlertDescription>
                        Success! Login successful
                    </AlertDescription>
                </Alert>

                {/* Stats Cards */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <StatsCard
                        title="Positions"
                        value={10}
                        icon={List}
                        description="Total positions available"
                    />
                    <StatsCard
                        title="Candidates"
                        value={20}
                        icon={Users}
                        description="Total candidates registered"
                    />
                    <StatsCard
                        title="Total Voters"
                        value={stats.total_voters}
                        icon={Users2}
                        description="Registered voters"
                    />
                    <StatsCard
                        title="Voters Voted"
                        value={stats.voted_count}
                        icon={CheckCircle}
                        description="Voters who have voted"
                    />
                </div>

                {/* Vote Distribution Chart */}
                <div className="mt-8">
                    <DashboardChart 
                        data={dashboardVoteData} 
                        title="Vote Distribution by Position"
                    />
                </div>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center space-x-4">
                                <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                <div className="flex-1">
                                    <p className="text-sm">Election is currently active</p>
                                    <p className="text-xs text-muted-foreground">Sangguniang Mag-aaral 2025</p>
                                </div>
                                <span className="text-xs text-muted-foreground">Now</span>
                            </div>
                            <div className="flex items-center space-x-4">
                                <div className="h-2 w-2 rounded-full bg-blue-500"></div>
                                <div className="flex-1">
                                    <p className="text-sm">System initialized successfully</p>
                                    <p className="text-xs text-muted-foreground">All modules are operational</p>
                                </div>
                                <span className="text-xs text-muted-foreground">2 hours ago</span>
                            </div>
                            <div className="flex items-center space-x-4">
                                <div className="h-2 w-2 rounded-full bg-yellow-500"></div>
                                <div className="flex-1">
                                    <p className="text-sm">No votes recorded yet</p>
                                    <p className="text-xs text-muted-foreground">Waiting for voter participation</p>
                                </div>
                                <span className="text-xs text-muted-foreground">1 day ago</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
