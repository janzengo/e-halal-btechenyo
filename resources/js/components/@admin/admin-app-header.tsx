import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { AdminRole } from '@/types/ehalal';
import { type BreadcrumbItem } from '@/types';
import AppLogo from '../app-logo';

interface AdminAppHeaderProps {
    breadcrumbs?: BreadcrumbItem[];
    userRole?: AdminRole;
}

export function AdminAppHeader({ breadcrumbs = [], userRole = 'officer' }: AdminAppHeaderProps) {
    return (
        <>
            <div className="border-sidebar-border/50 flex h-16 shrink-0 items-center gap-2 border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div className="flex items-center gap-2 flex-1">
                    <SidebarTrigger className="-ml-1" />
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>
        </>
    );
}