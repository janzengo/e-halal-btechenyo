import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/admin-layout';
import { AdminLogsTable } from '@/components/@admin/@logs/admin-logs-table';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Button } from '@/components/ui/button';
import { FileClock, Activity, Download } from 'lucide-react';
import { useLoading } from '@/contexts/loading-context';
import { SkeletonTable, SkeletonHeader } from '@/components/@admin/@loading/skeleton-cards';

interface AdminLog {
    id: number;
    timestamp: string;
    user_id: string;
    role: string;
    action: string;
    action_type: string;
    model_type: string | null;
    ip_address: string;
    admin_name: string;
}

interface PaginationInfo {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    has_more_pages: boolean;
}

interface LogsProps extends Record<string, any> {
    logs: AdminLog[];
    pagination: PaginationInfo;
}

export default function HeadLogs() {
    const { logs, pagination } = usePage<LogsProps>().props;
    const { isPageLoading } = useLoading();
    const handleView = (log: any) => {
        console.log('View log:', log);
        // Implement view logic
    };

    const handleExport = () => {
        console.log('Export admin logs');
        // Implement export logic
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/logs"
            breadcrumbs={[
                { title: 'Administration', href: '/head/dashboard' },
                { title: 'Admin Logs', href: '/head/logs' },
            ]}
        >
            <Head title="Admin Logs" />
            {/* Header */}
            {isPageLoading ? (
                <SkeletonHeader showButton={true} />
            ) : (
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Admin Logs</h2>
                        <p className="text-gray-600">Monitor system activities and admin actions</p>
                    </div>
                    <Button onClick={handleExport} variant="outline" className="flex items-center gap-2">
                        <Download className="h-4 w-4" />
                        Export Logs
                    </Button>
                </div>
            )}
            
            {isPageLoading ? (
                <SkeletonTable rows={10} />
            ) : logs.length === 0 ? (
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <FileClock />
                        </EmptyMedia>
                        <EmptyTitle>No Admin Activity Logs</EmptyTitle>
                        <EmptyDescription>
                            Administrative actions and system events will be logged here for auditing and monitoring purposes.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            ) : (
                    <div>
                        <AdminLogsTable
                            logs={logs}
                            pagination={pagination}
                            userRole="head"
                            onView={handleView}
                            onExport={handleExport}
                        />
                    </div>
            )}
        </AdminLayout>
    );
}