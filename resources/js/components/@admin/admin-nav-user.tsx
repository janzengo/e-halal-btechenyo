import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { useIsMobile } from '@/hooks/use-mobile';
import { AdminRole } from '@/types/ehalal';
import { ChevronsUpDown, LogOut, Settings, Clock } from 'lucide-react';
import { router, useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';

interface AdminNavUserProps {
    userRole: AdminRole;
}

interface AuthUser {
    id: number;
    firstname: string;
    lastname: string;
    email: string;
    photo?: string;
    role: string;
}

export function AdminNavUser({ userRole }: AdminNavUserProps) {
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const getInitials = useInitials();
    const { post, processing } = useForm();
    const { auth } = usePage<{ auth: { user: AuthUser | null } }>().props;

    // Use actual authenticated user data
    const user = auth.user ? {
        name: `${auth.user.firstname} ${auth.user.lastname}`,
        email: auth.user.email,
        avatar: auth.user.photo ? `/storage/${auth.user.photo}` : undefined,
        role: auth.user.role
    } : {
        name: userRole === 'head' ? 'Electoral Head' : 'Election Officer',
        email: userRole === 'head' ? 'head@ehalal.test' : 'officer@ehalal.test',
        avatar: undefined,
        role: userRole
    };

    // Navigation handlers
    const handleSettings = () => {
        const settingsUrl = userRole === 'head' ? '/head/settings/profile' : '/officers/settings/profile';
        router.visit(settingsUrl);
    };

    const handleLogout = () => {
        post('/auth/admin-btech/logout', {
            onSuccess: () => {
                // Clear session storage to reset login toast flags
                sessionStorage.removeItem('head-login-toast-shown');
                sessionStorage.removeItem('officer-login-toast-shown');
                toast.success('Logged out successfully!');
            },
            onError: () => {
                toast.error('Logout failed. Please try again.');
            }
        });
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="group text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent"
                        >
                            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                                <AvatarImage src={user.avatar} alt={user.name} />
                                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {getInitials(user.name)}
                                </AvatarFallback>
                            </Avatar>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-medium">{user.name}</span>
                                <span className="truncate text-xs text-muted-foreground">
                                    {user.email}
                                </span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="end"
                        side={
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                  ? 'left'
                                  : 'bottom'
                        }
                    >
                        <div className="flex items-center justify-start gap-2 p-2 text-left text-sm">
                            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                                <AvatarImage src={user.avatar} alt={user.name} />
                                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {getInitials(user.name)}
                                </AvatarFallback>
                            </Avatar>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-medium">{user.name}</span>
                                <span className="truncate text-xs text-muted-foreground">
                                    {user.email}
                                </span>
                            </div>
                        </div>
                        <div className="border-t border-sidebar-border" />
                        <div className="p-1">
                            <div 
                                className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-sidebar-accent hover:text-sidebar-accent-foreground cursor-pointer"
                                onClick={handleSettings}
                            >
                                <Settings className="h-4 w-4" />
                                Settings
                            </div>
                        </div>
                        <div className="border-t border-sidebar-border" />
                        <div className="p-1">
                            <div 
                                className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                onClick={handleLogout}
                            >
                                {processing ? (
                                    <>
                                        <Clock className="h-4 w-4 animate-spin" />
                                        Logging out...
                                    </>
                                ) : (
                                    <>
                                        <LogOut className="h-4 w-4" />
                                        Logout
                                    </>
                                )}
                            </div>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
