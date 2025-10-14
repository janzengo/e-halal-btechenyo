import React from 'react';
import { Link } from '@inertiajs/react';

interface AdminSidebarLogoProps {
    dashboardUrl: string;
}

export default function AdminSidebarLogo({ dashboardUrl }: AdminSidebarLogoProps) {
    return (
        <Link href={dashboardUrl} prefetch className="flex items-center w-full">
            <img
                src="/images/h-logo.jpg"
                alt="E-Halal Logo"
                className="w-full h-auto object-contain rounded"
            />
        </Link>
    );
}
