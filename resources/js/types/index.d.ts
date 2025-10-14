import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    subItems?: NavItem[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    student_number?: string;
    course?: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Candidate {
    id: number;
    name: string;
    party: string;
    photo?: string;
    platform: string;
}

export interface Position {
    id: number;
    name: string;
    max_vote: number;
    candidates: Candidate[];
}

export interface Election {
    id: number;
    name: string;
    description?: string;
    start_date: string;
    end_date: string;
    status: 'upcoming' | 'ongoing' | 'completed' | 'archived';
}

export interface VoteItem {
    position: string;
    candidate: string;
    party: string;
}

export interface VoteReceipt {
    vote_ref: string;
    election: {
        name: string;
        date: string;
    };
    voter: {
        student_number: string;
        name: string;
        course: string;
    };
    votes: VoteItem[];
    timestamp: string;
}
