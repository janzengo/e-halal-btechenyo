import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { AdminRole } from '@/types/ehalal';
import { type BreadcrumbItem } from '@/types';
import { Bell } from 'lucide-react';

interface AdminAppHeaderProps {
    breadcrumbs?: BreadcrumbItem[];
    userRole?: AdminRole;
}

export function AdminAppHeader({ breadcrumbs = [], userRole = 'officer' }: AdminAppHeaderProps) {
    return (
        <div className="border-sidebar-border/50 flex h-12 shrink-0 items-center gap-2 border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2 flex-1">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="flex items-center gap-2">
                <Button variant="ghost" size="icon" className="relative h-8 w-8">
                    <Bell className="h-4 w-4 text-muted-foreground" />
                    {/* Notification badge - uncomment when notifications exist */}
                    {/* <span className="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-[10px] font-medium text-white flex items-center justify-center">3</span> */}
                </Button>
            </div>
        </div>
    );
}