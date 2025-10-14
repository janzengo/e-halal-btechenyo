import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import { SettingsNav } from '@/components/@admin/settings-nav';
import AdminLayout from '@/layouts/admin/admin-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Save, Lock, Eye, EyeOff, Shield, CheckCircle, AlertTriangle, Key, Zap, Clock, AlertCircle } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/head/settings',
    },
    {
        title: 'Password',
        href: '/head/settings/password',
    },
];

export default function HeadPassword() {
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [passwordStrength, setPasswordStrength] = useState(0);
    const [passwordData, setPasswordData] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleSave = () => {
        console.log('Updating password...', passwordData);
        // Implement password update logic here
    };

    const calculatePasswordStrength = (password: string) => {
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        return strength;
    };

    const handlePasswordChange = (value: string) => {
        setPasswordData({...passwordData, password: value});
        setPasswordStrength(calculatePasswordStrength(value));
    };

    const getPasswordStrengthColor = (strength: number) => {
        if (strength <= 25) return 'bg-red-500';
        if (strength <= 50) return 'bg-orange-500';
        if (strength <= 75) return 'bg-yellow-500';
        return 'bg-green-500';
    };

    const getPasswordStrengthText = (strength: number) => {
        if (strength <= 25) return 'Very Weak';
        if (strength <= 50) return 'Weak';
        if (strength <= 75) return 'Good';
        return 'Strong';
    };

    const passwordRequirements = [
        { label: 'At least 8 characters', met: passwordData.password.length >= 8 },
        { label: 'Contains uppercase letter', met: /[A-Z]/.test(passwordData.password) },
        { label: 'Contains lowercase letter', met: /[a-z]/.test(passwordData.password) },
        { label: 'Contains number', met: /[0-9]/.test(passwordData.password) },
        { label: 'Contains special character', met: /[^A-Za-z0-9]/.test(passwordData.password) },
    ];

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/settings/password"
            breadcrumbs={breadcrumbs}
        >
            <Head title="Password Settings" />

            {/* Header */}
            <div className="flex items-center gap-3 mb-6">
                <div className="p-2 bg-green-100 rounded-lg">
                    <Lock className="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Password Security</h1>
                    <p className="text-gray-600">Keep your account secure with a strong password</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* Settings Navigation */}
                <div className="lg:col-span-1">
                    <SettingsNav userRole="head" />
                </div>

                {/* Main Content */}
                <div className="lg:col-span-3 space-y-6">
                            {/* Security Overview Card */}
                            <Card className="border-green-200 bg-gradient-to-br from-green-50 to-white">
                                <CardHeader className="text-center pb-4">
                                    <div className="flex justify-center mb-4">
                                        <div className="p-4 bg-green-100 rounded-full">
                                            <Shield className="w-8 h-8 text-green-600" />
                                        </div>
                                    </div>
                                    <CardTitle className="text-xl">Password Security</CardTitle>
                                    <CardDescription className="text-sm">
                                        Keep your account protected
                                    </CardDescription>
                                    <Badge variant="secondary" className="mt-2 bg-green-100 text-green-700">
                                        Last Updated: 2 days ago
                                    </Badge>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <CheckCircle className="w-4 h-4 text-green-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Status</p>
                                                <p className="text-sm font-medium text-gray-900">Secure</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <Clock className="w-4 h-4 text-blue-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Updated</p>
                                                <p className="text-sm font-medium text-gray-900">Recently</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <Zap className="w-4 h-4 text-yellow-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Strength</p>
                                                <p className="text-sm font-medium text-gray-900">Strong</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Separator className="bg-green-200" />
                                    <div className="space-y-2">
                                        <h4 className="font-medium text-sm text-gray-900">Security Tips</h4>
                                        <div className="space-y-2 text-xs text-gray-600">
                                            <div className="flex items-start gap-2">
                                                <div className="w-1 h-1 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                                                <span>Use a unique password for this account</span>
                                            </div>
                                            <div className="flex items-start gap-2">
                                                <div className="w-1 h-1 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                                                <span>Never share your password with anyone</span>
                                            </div>
                                            <div className="flex items-start gap-2">
                                                <div className="w-1 h-1 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                                                <span>Change your password periodically</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Change Password Form */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Key className="w-5 h-5 text-green-600" />
                                        Change Password
                                    </CardTitle>
                                    <CardDescription>
                                        Ensure your account is using a secure password. Use at least 8 characters with a mix of letters, numbers, and symbols.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={(e) => { e.preventDefault(); handleSave(); }} className="space-y-6">
                                        {/* Current Password */}
                                        <div className="space-y-2">
                                            <Label htmlFor="current_password" className="text-sm font-medium">
                                                Current Password
                                            </Label>
                                            <div className="relative">
                                                <Input
                                                    id="current_password"
                                                    type={showCurrentPassword ? 'text' : 'password'}
                                                    value={passwordData.current_password}
                                                    onChange={(e) => setPasswordData({...passwordData, current_password: e.target.value})}
                                                    required
                                                    autoComplete="current-password"
                                                    placeholder="Enter your current password"
                                                    className="pr-10"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                    onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                                >
                                                    {showCurrentPassword ? (
                                                        <EyeOff className="h-4 w-4" />
                                                    ) : (
                                                        <Eye className="h-4 w-4" />
                                                    )}
                                                </Button>
                                            </div>
                                        </div>

                                        <Separator />

                                        {/* New Password */}
                                        <div className="space-y-2">
                                            <Label htmlFor="password" className="text-sm font-medium">
                                                New Password
                                            </Label>
                                            <div className="relative">
                                                <Input
                                                    id="password"
                                                    type={showNewPassword ? 'text' : 'password'}
                                                    value={passwordData.password}
                                                    onChange={(e) => handlePasswordChange(e.target.value)}
                                                    required
                                                    autoComplete="new-password"
                                                    placeholder="Enter your new password"
                                                    className="pr-10"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                    onClick={() => setShowNewPassword(!showNewPassword)}
                                                >
                                                    {showNewPassword ? (
                                                        <EyeOff className="h-4 w-4" />
                                                    ) : (
                                                        <Eye className="h-4 w-4" />
                                                    )}
                                                </Button>
                                            </div>
                                        </div>

                                        {/* Password Strength Indicator */}
                                        {passwordData.password && (
                                            <div className="space-y-2">
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600">Password Strength</span>
                                                    <span className={`font-medium ${getPasswordStrengthColor(passwordStrength).replace('bg-', 'text-')}`}>
                                                        {getPasswordStrengthText(passwordStrength)}
                                                    </span>
                                                </div>
                                                <Progress value={passwordStrength} className="h-2" />
                                                <div className="grid grid-cols-2 gap-2 text-xs">
                                                    {passwordRequirements.map((req, index) => (
                                                        <div key={index} className="flex items-center gap-2">
                                                            {req.met ? (
                                                                <CheckCircle className="w-3 h-3 text-green-500" />
                                                            ) : (
                                                                <AlertCircle className="w-3 h-3 text-gray-400" />
                                                            )}
                                                            <span className={req.met ? 'text-green-600' : 'text-gray-500'}>
                                                                {req.label}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Confirm Password */}
                                        <div className="space-y-2">
                                            <Label htmlFor="password_confirmation" className="text-sm font-medium">
                                                Confirm New Password
                                            </Label>
                                            <div className="relative">
                                                <Input
                                                    id="password_confirmation"
                                                    type={showConfirmPassword ? 'text' : 'password'}
                                                    value={passwordData.password_confirmation}
                                                    onChange={(e) => setPasswordData({...passwordData, password_confirmation: e.target.value})}
                                                    required
                                                    autoComplete="new-password"
                                                    placeholder="Confirm your new password"
                                                    className="pr-10"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                >
                                                    {showConfirmPassword ? (
                                                        <EyeOff className="h-4 w-4" />
                                                    ) : (
                                                        <Eye className="h-4 w-4" />
                                                    )}
                                                </Button>
                                            </div>
                                            {passwordData.password_confirmation && passwordData.password !== passwordData.password_confirmation && (
                                                <p className="text-sm text-red-600 flex items-center gap-1">
                                                    <AlertTriangle className="w-4 h-4" />
                                                    Passwords do not match
                                                </p>
                                            )}
                                        </div>

                                        <div className="flex items-center gap-4 pt-4">
                                            <Button 
                                                type="submit" 
                                                className="flex items-center gap-2 bg-green-600 hover:bg-green-700"
                                            >
                                                <Save className="w-4 h-4" />
                                                Update Password
                                            </Button>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>

                            {/* Security Best Practices */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Shield className="w-5 h-5 text-green-600" />
                                        Security Best Practices
                                    </CardTitle>
                                    <CardDescription>
                                        Follow these guidelines to keep your account secure.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-4">
                                            <div className="flex items-start gap-3">
                                                <div className="p-2 bg-green-100 rounded-lg">
                                                    <CheckCircle className="w-4 h-4 text-green-600" />
                                                </div>
                                                <div>
                                                    <h4 className="font-medium text-sm">Use a strong password</h4>
                                                    <p className="text-xs text-gray-600 mt-1">
                                                        Include uppercase, lowercase, numbers, and special characters
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-start gap-3">
                                                <div className="p-2 bg-blue-100 rounded-lg">
                                                    <Key className="w-4 h-4 text-blue-600" />
                                                </div>
                                                <div>
                                                    <h4 className="font-medium text-sm">Don't reuse passwords</h4>
                                                    <p className="text-xs text-gray-600 mt-1">
                                                        Use a unique password for this account
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="space-y-4">
                                            <div className="flex items-start gap-3">
                                                <div className="p-2 bg-yellow-100 rounded-lg">
                                                    <AlertTriangle className="w-4 h-4 text-yellow-600" />
                                                </div>
                                                <div>
                                                    <h4 className="font-medium text-sm">Keep it private</h4>
                                                    <p className="text-xs text-gray-600 mt-1">
                                                        Never share your password with anyone
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-start gap-3">
                                                <div className="p-2 bg-green-100 rounded-lg">
                                                    <Clock className="w-4 h-4 text-green-600" />
                                                </div>
                                                <div>
                                                    <h4 className="font-medium text-sm">Update regularly</h4>
                                                    <p className="text-xs text-gray-600 mt-1">
                                                        Change your password periodically for better security
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
