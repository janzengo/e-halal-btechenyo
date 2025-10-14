import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import AdminNavbar from '@/components/@admin/admin-navbar';
import { AdminRole } from '@/types/ehalal';
import { type ReactNode } from 'react';

interface AdminSetupLayoutProps {
    children: ReactNode;
    userRole?: AdminRole;
    handleLogout: () => void;
}

export default function AdminSetupLayout({ 
    children, 
    userRole = 'officer',
    handleLogout,
}: AdminSetupLayoutProps) {
    return (
        <AppShell variant="header">
            <AdminNavbar handleLogout={handleLogout} />
            <AppContent variant="header">
                {children}
            </AppContent>
        </AppShell>
    );
}
