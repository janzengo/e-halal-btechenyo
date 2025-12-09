import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AdminSidebar } from '@/components/@admin/admin-sidebar';
import { AdminAppHeader } from '@/components/@admin/admin-app-header';
import { AdminRole } from '@/types/ehalal';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AdminLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    userRole?: AdminRole;
    currentPath?: string;
}

export default function AdminLayout({ 
    children, 
    breadcrumbs = [], 
    userRole = 'officer',
}: AdminLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AdminSidebar userRole={userRole} />
            <AppContent variant="sidebar">
                <AdminAppHeader breadcrumbs={breadcrumbs} userRole={userRole} />
                <div className="flex flex-col gap-6 p-6 md:p-8 lg:p-10 flex-1">
                    {children}
                </div>
            </AppContent>
        </AppShell>
    );
}
