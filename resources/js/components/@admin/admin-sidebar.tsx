import { NavFooter } from '@/components/nav-footer';
import { AdminNavMain } from '@/components/@admin/admin-nav-main';
import { AdminNavUser } from '@/components/@admin/admin-nav-user';
import AdminSidebarLogo from '@/components/@admin/admin-sidebar-logo';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { AdminRole } from '@/types/ehalal';
import { type NavGroup } from '@/types';
import { 
    ClipboardList,
    FileClock, 
    GraduationCap, 
    Users, 
    Users2, 
    Vote, 
    LayoutDashboard, 
    ListChecks, 
    UserCheck, 
    ContactRound,
    FolderClock, 
    ShieldCheck, 
    Settings,
    BookOpen,
    Bug,
    Plus, 

} from 'lucide-react';

interface AdminSidebarProps {
    userRole: AdminRole;
}

export function AdminSidebar({ userRole }: AdminSidebarProps) {
    const isHead = userRole === 'head';
    
    // Generate navigation groups based on role
    const getAdminNavGroups = (): NavGroup[] => {
        const baseGroups: NavGroup[] = [
            {
                title: 'Overview',
                items: [
                    {
                        title: 'Dashboard',
                        href: isHead ? '/head/dashboard' : '/officers/dashboard',
                        icon: LayoutDashboard,
                    }
                ]
            },
            {
                title: 'Election Management',
                items: [
                    {
                        title: 'Positions',
                        href: isHead ? '/head/positions' : '/officers/positions',
                        icon: ListChecks,
                    },
                    {
                        title: 'Candidates',
                        href: isHead ? '/head/candidates' : '/officers/candidates',
                        icon: ContactRound,
                    },
                    {
                        title: 'Partylists',
                        href: isHead ? '/head/partylists' : '/officers/partylists',
                        icon:  Users,
                    },
                    {
                        title: 'Voters',
                        href: isHead ? '/head/voters' : '/officers/voters',
                        icon: UserCheck,
                    },
                    {
                        title: 'Courses',
                        href: isHead ? '/head/courses' : '/officers/courses',
                        icon: GraduationCap,
                    }
                ]
            },
            {
                title: 'Reports & Analytics',
                items: [
                    {
                        title: 'Votes',
                        href: isHead ? '/head/votes' : '/officers/votes',
                        icon: Vote,
                    },
                    {
                        title: 'Election History',
                        href: isHead ? '/head/elections' : '/officers/elections',
                        icon: FolderClock,
                    }
                ]
            }
        ];

        // Add administration section for Head only
        if (isHead) {
            baseGroups.push({
                title: 'Administration',
                items: [
                    {
                        title: 'Officers',
                        href: '/head/officers',
                        icon: ShieldCheck,
                    },
                    {
                        title: 'Admin Logs',
                        href: '/head/logs',
                        icon: FileClock,
                    }
                ]
            });

            baseGroups.push({
                title: 'Settings',
                items: [
                    {
                        title: 'Configure Election',
                        href: '/head/configure',
                        icon: Settings,
                    },
                    {
                        title: 'Ballot Settings',
                        href: '/head/ballots',
                        icon: ClipboardList,
                    }
                ]
            });
        }

        return baseGroups;
    };

    const footerNavItems = [
            {
                title: 'Report a Bug',
                href: '#',
                icon: Bug,
            },
            {
                title: 'Suggest a Feature',
                href: '#',
                icon: Plus,
            },
        ];

    const dashboardUrl = isHead ? '/head/dashboard' : '/officers/dashboard';
    const navGroups = getAdminNavGroups();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <AdminSidebarLogo dashboardUrl={dashboardUrl} />
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <AdminNavMain groups={navGroups} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <AdminNavUser userRole={userRole} />
            </SidebarFooter>
        </Sidebar>
    );
}
