import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { SettingsNav } from '@/components/@admin/settings-nav';
import AdminLayout from '@/layouts/admin/admin-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Save, User, Shield, Mail, Calendar, CheckCircle, Edit3, AlertTriangle } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/officers/settings',
    },
    {
        title: 'Profile',
        href: '/officers/settings/profile',
    },
];

export default function OfficerProfile() {
    // Dummy user data - replace with actual data from backend
    const user = {
        name: 'Electoral Officer',
        email: 'officer@ehalal.test',
        role: 'officer',
        avatar: '/images/profile.jpg',
        created_at: '2024-01-01',
        last_login: '2024-01-15 10:30:00'
    };

    const [isEditing, setIsEditing] = useState(false);
    const [profileData, setProfileData] = useState({
        name: user.name,
        email: user.email,
    });

    const handleSave = () => {
        console.log('Saving profile...', profileData);
        // Implement save logic here
        setIsEditing(false);
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const getRoleDisplay = (role: string) => {
        return role === 'head' ? 'Electoral Head' : 'Election Officer';
    };

    return (
        <AdminLayout
            userRole="officer"
            currentPath="/officers/settings/profile"
            breadcrumbs={breadcrumbs}
        >
            <Head title="Profile Settings" />

            {/* Header */}
            <div className="flex items-center gap-3 mb-6">
                <div className="p-2 bg-green-100 rounded-lg">
                    <User className="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Profile Settings</h1>
                    <p className="text-gray-600">Manage your account information and preferences</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* Settings Navigation */}
                <div className="lg:col-span-1">
                    <SettingsNav userRole="officer" />
                </div>

                {/* Main Content */}
                <div className="lg:col-span-3 space-y-6">
                            {/* Profile Overview Card */}
                            <Card className="border-green-200 bg-gradient-to-br from-green-50 to-white">
                                <CardHeader className="text-center pb-4">
                                    <div className="flex justify-center mb-4">
                                        <Avatar className="w-24 h-24 border-4 border-green-200">
                                            <AvatarFallback className="text-xl font-semibold bg-green-100 text-green-600">
                                                {getInitials(user.name)}
                                            </AvatarFallback>
                                        </Avatar>
                                    </div>
                                    <CardTitle className="text-2xl">{user.name}</CardTitle>
                                    <CardDescription className="text-sm">
                                        {getRoleDisplay(user.role)}
                                    </CardDescription>
                                    <Badge variant="secondary" className="mt-2 bg-green-100 text-green-700">
                                        Active Account
                                    </Badge>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <Mail className="w-4 h-4 text-green-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Email</p>
                                                <p className="text-sm font-medium text-gray-900">{user.email}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <Calendar className="w-4 h-4 text-green-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Member Since</p>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {new Date(user.created_at).toLocaleDateString('en-US', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric'
                                                    })}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Personal Information */}
                            <Card>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <CardTitle className="flex items-center gap-2">
                                                <User className="w-5 h-5 text-green-600" />
                                                Personal Information
                                            </CardTitle>
                                            <CardDescription>
                                                Update your personal information and contact details.
                                            </CardDescription>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setIsEditing(!isEditing)}
                                            className="flex items-center gap-2"
                                        >
                                            <Edit3 className="w-4 h-4" />
                                            {isEditing ? 'Cancel' : 'Edit'}
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={(e) => { e.preventDefault(); handleSave(); }} className="space-y-6">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="text-sm font-medium">
                                                    Full Name
                                                </Label>
                                                <Input
                                                    id="name"
                                                    type="text"
                                                    value={profileData.name}
                                                    onChange={(e) => setProfileData({...profileData, name: e.target.value})}
                                                    disabled={!isEditing}
                                                    className={!isEditing ? 'bg-gray-50' : ''}
                                                    required
                                                    autoComplete="name"
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="email" className="text-sm font-medium">
                                                    Email Address
                                                </Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    value={profileData.email}
                                                    onChange={(e) => setProfileData({...profileData, email: e.target.value})}
                                                    disabled={!isEditing}
                                                    className={!isEditing ? 'bg-gray-50' : ''}
                                                    required
                                                    autoComplete="username"
                                                />
                                            </div>
                                        </div>

                                        {isEditing && (
                                            <div className="flex items-center gap-4 pt-4">
                                                <Button 
                                                    type="submit" 
                                                    className="flex items-center gap-2 bg-green-600 hover:bg-green-700"
                                                >
                                                    <Save className="w-4 h-4" />
                                                    Save Changes
                                                </Button>
                                                <Button 
                                                    type="button" 
                                                    variant="outline"
                                                    onClick={() => setIsEditing(false)}
                                                >
                                                    Cancel
                                                </Button>
                                            </div>
                                        )}
                                    </form>
                                </CardContent>
                            </Card>

                            {/* Account Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Shield className="w-5 h-5 text-green-600" />
                                        Account Information
                                    </CardTitle>
                                    <CardDescription>
                                        View your account details and role information.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-gray-500">Account Role</Label>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline" className="text-green-600 border-green-200">
                                                    {getRoleDisplay(user.role)}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-gray-500">Account Status</Label>
                                            <div className="flex items-center gap-2">
                                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span className="text-sm font-medium text-green-600">Active</span>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-gray-500">Email Verification</Label>
                                            <div className="flex items-center gap-2">
                                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span className="text-sm font-medium text-green-600">Verified</span>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-gray-500">Last Updated</Label>
                                            <span className="text-sm text-gray-600">Today at 2:30 PM</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
