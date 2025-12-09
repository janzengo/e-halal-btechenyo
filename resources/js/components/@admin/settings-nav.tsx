import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    User,
    Lock,
    Settings,
    CheckCircle,
    AlertCircle
} from 'lucide-react';

interface SettingsNavItem {
    title: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    description: string;
    status?: 'active' | 'warning' | 'success';
}

interface SettingsNavProps {
    userRole?: string;
}

const getSettingsNavItems = (userRole: string): SettingsNavItem[] => {
    const baseUrl = userRole === 'head' ? '/head/settings' : '/officers/settings';

    return [
        {
            title: 'Profile',
            href: `${baseUrl}/profile`,
            icon: User,
            description: 'Manage your personal information and account details',
            status: 'active'
        },
        {
            title: 'Password',
            href: `${baseUrl}/password`,
            icon: Lock,
            description: 'Update your password and security settings',
            status: 'success'
        }
    ];
};

export function SettingsNav({ userRole }: SettingsNavProps = {}) {
    const page = usePage();
    const currentUrl = page.url;
    const settingsNavItems = getSettingsNavItems(userRole || 'head');

    const getStatusColor = (status?: 'active' | 'warning' | 'success') => {
        switch (status) {
            case 'warning':
                return 'text-yellow-600 bg-yellow-50 border-yellow-200';
            case 'success':
                return 'text-green-600 bg-green-50 border-green-200';
            default:
                return 'text-green-600 bg-green-50 border-green-200';
        }
    };

    const getStatusIcon = (status?: 'active' | 'warning' | 'success') => {
        switch (status) {
            case 'warning':
                return <AlertCircle className="w-4 h-4" />;
            case 'success':
                return <CheckCircle className="w-4 h-4" />;
            default:
                return <Settings className="w-4 h-4" />;
        }
    };

    return (
        <Card className="border-0 shadow-sm bg-gradient-to-br from-green-50 to-white">
            <CardContent className="p-6">
                <div className="space-y-6">
                    {/* Header */}
                    <div className="text-center pb-4">
                        <div className="flex justify-center mb-3">
                            <div className="p-3 bg-green-100 rounded-full">
                                <Settings className="w-6 h-6 text-green-600" />
                            </div>
                        </div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-1">Settings</h3>
                        <p className="text-sm text-gray-600">Manage your account preferences</p>
                    </div>

                    <Separator className="bg-green-200" />

                    {/* Navigation Items */}
                    <nav className="space-y-2">
                        {settingsNavItems.map((item) => {
                            const isActive = currentUrl === item.href;
                            const Icon = item.icon;

                            return (
                                <Link
                                    key={item.title}
                                    href={item.href}
                                    className={`group relative block rounded-lg p-4 transition-all duration-200 hover:shadow-md ${
                                        isActive
                                            ? 'bg-green-100 border-green-300 shadow-md'
                                            : 'bg-white border border-gray-200 hover:border-green-300 hover:bg-green-50'
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        {/* Icon */}
                                        <div className={`p-2 rounded-lg transition-colors ${
                                            isActive
                                                ? 'bg-green-200 text-green-700'
                                                : 'bg-gray-100 text-gray-600 group-hover:bg-green-100 group-hover:text-green-700'
                                        }`}>
                                            <Icon className="w-4 h-4" />
                                        </div>

                                        {/* Content */}
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2 mb-1">
                                                <h4 className={`font-medium text-sm ${
                                                    isActive ? 'text-green-900' : 'text-gray-900'
                                                }`}>
                                                    {item.title}
                                                </h4>
                                                {item.status && (
                                                    <div className={`p-1 rounded-full ${getStatusColor(item.status)}`}>
                                                        {getStatusIcon(item.status)}
                                                    </div>
                                                )}
                                            </div>
                                            <p className={`text-xs ${
                                                isActive ? 'text-green-700' : 'text-gray-600'
                                            }`}>
                                                {item.description}
                                            </p>
                                        </div>

                                        {/* Active Indicator */}
                                        {isActive && (
                                            <div className="absolute top-0 right-0 w-2 h-full bg-green-600 rounded-r-lg"></div>
                                        )}
                                    </div>
                                </Link>
                            );
                        })}
                    </nav>
                </div>
            </CardContent>
        </Card>
    );
}
