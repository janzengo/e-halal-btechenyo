import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { type NavItem, type NavGroup } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useState } from 'react';

interface AdminNavMainProps {
    items?: NavItem[];
    groups?: NavGroup[];
}

// Helper component for rendering menu items with optional submenus
function AdminNavMenuItem({ item, currentUrl }: { item: NavItem; currentUrl: string }) {
    const [isOpen, setIsOpen] = useState(false);
    const hasSubItems = item.subItems && item.subItems.length > 0;
    const isActive = item.href === currentUrl || (hasSubItems && item.subItems?.some(subItem => subItem.href === currentUrl));

    if (hasSubItems) {
        return (
            <Collapsible open={isOpen} onOpenChange={setIsOpen}>
                <SidebarMenuItem>
                    <CollapsibleTrigger asChild>
                        <SidebarMenuButton
                            isActive={isActive}
                            tooltip={{ children: item.title }}
                            className="hover:bg-green-50 hover:text-green-700 data-[active=true]:bg-green-100 data-[active=true]:text-green-800"
                        >
                            {item.icon && <item.icon />}
                            <span className="group-data-[collapsible=icon]:hidden">{item.title}</span>
                            <ChevronRight className={`ml-auto h-4 w-4 transition-transform duration-200 group-data-[collapsible=icon]:hidden ${isOpen ? 'rotate-90' : ''}`} />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent className="group-data-[collapsible=icon]:hidden">
                        <SidebarMenuSub>
                            {item.subItems?.map((subItem) => (
                                <SidebarMenuSubItem key={subItem.title}>
                                    <SidebarMenuSubButton
                                        asChild
                                        isActive={subItem.href === currentUrl}
                                        className="hover:bg-green-50 hover:text-green-700 data-[active=true]:bg-green-100 data-[active=true]:text-green-800"
                                    >
                                        <Link href={subItem.href} prefetch>
                                            <span>{subItem.title}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            ))}
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        );
    }

    return (
        <SidebarMenuItem>
            <SidebarMenuButton
                asChild
                isActive={isActive}
                tooltip={{ children: item.title }}
                className="hover:bg-green-50 hover:text-green-700 data-[active=true]:bg-green-100 data-[active=true]:text-green-800"
            >
                <Link href={item.href} prefetch>
                    {item.icon && <item.icon />}
                    <span className="group-data-[collapsible=icon]:hidden">{item.title}</span>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    );
}

export function AdminNavMain({ items = [], groups = [] }: AdminNavMainProps) {
    const page = usePage();
    
    // Extract pathname from URL to handle query parameters
    const currentPath = new URL(page.url, window.location.origin).pathname;

    // If groups are provided, use the new grouped navigation
    if (groups.length > 0) {
        return (
            <>
                {groups.map((group) => (
                    <SidebarGroup key={group.title} className="px-2 py-0">
                        <SidebarGroupLabel className="text-green-700 font-medium group-data-[collapsible=icon]:hidden">
                            {group.title}
                        </SidebarGroupLabel>
                        <SidebarMenu>
                            {group.items.map((item) => (
                                <AdminNavMenuItem key={item.title} item={item} currentUrl={currentPath} />
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                ))}
            </>
        );
    }

    // Fallback to the old single-group navigation for backward compatibility
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="group-data-[collapsible=icon]:hidden">Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <AdminNavMenuItem key={item.title} item={item} currentUrl={currentPath} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}