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
    currentPath = ''
}: AdminLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AdminSidebar userRole={userRole} currentPath={currentPath} />
            <AppContent variant="sidebar">
                <AdminAppHeader breadcrumbs={breadcrumbs} userRole={userRole} />
                {children}
            </AppContent>
        </AppShell>
    );
}
