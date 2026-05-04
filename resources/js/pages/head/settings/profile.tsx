import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { SettingsNav } from '@/components/@admin/settings-nav';
import AdminLayout from '@/layouts/admin/admin-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { toast } from 'sonner';
import { Save, User, Shield, Mail, Calendar, Edit3, Pencil, Loader2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Dropzone, DropzoneContent, DropzoneEmptyState } from '../../../../../components/ui/shadcn-io/dropzone';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/head/settings',
    },
    {
        title: 'Profile',
        href: '/head/settings/profile',
    },
];

export default function HeadProfile() {
    const { auth } = usePage<{ auth: { user: { id: number; firstname: string; lastname: string; email: string; role: string; photo?: string; created_on?: string } | null } }>().props;
    const currentUser = auth.user!;

    const [isEditing, setIsEditing] = useState(false);
    const [profileData, setProfileData] = useState({
        firstname: currentUser.firstname,
        lastname: currentUser.lastname,
        email: currentUser.email,
        photo_file: null as File | null,
        previewUrl: currentUser.photo ? (currentUser.photo.startsWith('/storage/') ? currentUser.photo : `/storage/${currentUser.photo}`) : undefined,
    });
    const [isAvatarDialogOpen, setIsAvatarDialogOpen] = useState(false);

    const { post, processing, errors, clearErrors, reset, setData, data } = useForm({
        firstname: profileData.firstname,
        lastname: profileData.lastname,
        email: profileData.email,
        photo_file: null as File | null,
    });

    useEffect(() => {
        return () => {
            if (profileData.previewUrl?.startsWith('blob:')) {
                URL.revokeObjectURL(profileData.previewUrl);
            }
        };
    }, [profileData.previewUrl]);

    const handleSave = () => {
        post('/head/settings/profile', {
            forceFormData: true,
            onSuccess: () => {
                setIsEditing(false);
                clearErrors();
                // Reload to get updated profile data from server
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Profile update failed:', errors);
                toast.error('Failed to update profile. Please try again.');
            }
        });
    };

    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [previewImage, setPreviewImage] = useState<string | null>(null);
    const [isUploadingAvatar, setIsUploadingAvatar] = useState(false);

    const handleAvatarDrop = (acceptedFiles: File[]) => {
        if (acceptedFiles.length === 0) {
            return;
        }

        const file = acceptedFiles[0];

        // Clean up previous preview
        if (previewImage?.startsWith('blob:')) {
            URL.revokeObjectURL(previewImage);
        }

        setSelectedFile(file);
        setPreviewImage(URL.createObjectURL(file));
    };

    const handleSaveAvatar = () => {
        if (!selectedFile) {
            toast.error('Please select an image first.');
            return;
        }

        setIsUploadingAvatar(true);

        const formData = new FormData();
        formData.append('photo_file', selectedFile);

        router.post('/head/settings/profile/avatar', formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success('Profile photo updated successfully!');

                const newPhoto = page.props?.auth?.user?.photo;
                if (typeof newPhoto === 'string' && newPhoto.length > 0) {
                    setProfileData((prev) => ({
                        ...prev,
                        previewUrl: newPhoto.startsWith('/storage/') ? newPhoto : `/storage/${newPhoto}`,
                    }));
                }

                setIsAvatarDialogOpen(false);
                setSelectedFile(null);
                if (previewImage?.startsWith('blob:')) {
                    URL.revokeObjectURL(previewImage);
                }
                setPreviewImage(null);
            },
            onError: (errors) => {
                console.error('Avatar upload error:', errors);
                toast.error('Failed to update profile photo. Please try again.');
            },
            onFinish: () => {
                setIsUploadingAvatar(false);
            },
        });
    };

    const handleAvatarUploadError = (error: Error) => {
        toast.error(error.message ?? 'Unable to upload photo.');
    };

    const handleAvatarDialogChange = (open: boolean) => {
        setIsAvatarDialogOpen(open);
        // Clean up when closing
        if (!open) {
            setSelectedFile(null);
            if (previewImage?.startsWith('blob:')) {
                URL.revokeObjectURL(previewImage);
            }
            setPreviewImage(null);
        }
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .filter(Boolean)
            .map((word) => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const getRoleDisplay = (role: string) => {
        return role === 'head' ? 'Electoral Head' : 'Election Officer';
    };

    const formFullName = `${data.firstname} ${data.lastname}`.trim();
    const displayName = formFullName || `${currentUser.firstname} ${currentUser.lastname}`.trim();
    const avatarInitials = getInitials(displayName || currentUser.firstname);
    const hasAvatarImage = Boolean(profileData.previewUrl);

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/settings/profile"
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
                    <SettingsNav userRole="head" />
                </div>

                {/* Main Content */}
                <div className="lg:col-span-3 space-y-6">
                            {/* Profile Overview Card */}
                            <Card className="border-green-200 bg-gradient-to-br from-green-50 to-white">
                                <CardHeader className="text-center pb-4">
                                    <div className="flex justify-center mb-4">
                                        <div className="relative">
                                            <Avatar className="w-24 h-24 border-4 border-green-200">
                                                {hasAvatarImage && (
                                                    <AvatarImage src={profileData.previewUrl} alt={displayName} />
                                                )}
                                                <AvatarFallback className="text-xl font-semibold bg-green-100 text-green-600">
                                                    {avatarInitials}
                                                </AvatarFallback>
                                            </Avatar>
                                            <Button
                                                type="button"
                                                size="icon"
                                                className="absolute bottom-0 right-0 h-9 w-9 rounded-full border border-white bg-green-600 text-white hover:bg-green-700 shadow-md"
                                                onClick={() => setIsAvatarDialogOpen(true)}
                                            >
                                                <Pencil className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <CardTitle className="text-2xl">{`${currentUser.firstname} ${currentUser.lastname}`}</CardTitle>
                                    <CardDescription className="text-sm">
                                        {getRoleDisplay(currentUser.role)}
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
                                                <p className="text-sm font-medium text-gray-900">{currentUser.email}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 p-3 bg-white rounded-lg border border-green-100">
                                            <Calendar className="w-4 h-4 text-green-500" />
                                            <div>
                                                <p className="text-xs text-gray-500">Member Since</p>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {currentUser.created_on
                                                        ? new Date(currentUser.created_on).toLocaleDateString('en-US', {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric'
                                                        })
                                                        : '—'}
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
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div className="space-y-2">
                                                    <Label htmlFor="firstname" className="text-sm font-medium">First Name</Label>
                                                    <Input
                                                        id="firstname"
                                                        type="text"
                                                        value={data.firstname}
                                                        onChange={(e) => setData('firstname', e.target.value)}
                                                        disabled={!isEditing}
                                                        className={!isEditing ? 'bg-gray-50' : ''}
                                                        required
                                                        autoComplete="given-name"
                                                    />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label htmlFor="lastname" className="text-sm font-medium">Last Name</Label>
                                                    <Input
                                                        id="lastname"
                                                        type="text"
                                                        value={data.lastname}
                                                        onChange={(e) => setData('lastname', e.target.value)}
                                                        disabled={!isEditing}
                                                        className={!isEditing ? 'bg-gray-50' : ''}
                                                        required
                                                        autoComplete="family-name"
                                                    />
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="email" className="text-sm font-medium">
                                                    Email Address
                                                </Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    value={data.email}
                                                    onChange={(e) => setData('email', e.target.value)}
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
                                                    {getRoleDisplay(currentUser.role)}
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

            <Dialog open={isAvatarDialogOpen} onOpenChange={handleAvatarDialogChange}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Update Profile Photo</DialogTitle>
                        <DialogDescription>Upload a clear square image (PNG, JPG, GIF up to 5MB).</DialogDescription>
                    </DialogHeader>

                    <Dropzone
                        accept={{
                            'image/*': ['.png', '.jpg', '.jpeg', '.gif'],
                        }}
                        maxFiles={1}
                        minSize={1 * 1024 * 1024}
                        maxSize={20 * 1024 * 1024}
                        onDrop={(acceptedFiles) => handleAvatarDrop(acceptedFiles)}
                        onError={handleAvatarUploadError}
                        src={selectedFile ? [selectedFile] : undefined}
                    >
                        <div className="w-full">
                            {selectedFile && previewImage ? (
                                <div className="flex flex-col items-center gap-4">
                                    <img
                                        src={previewImage}
                                        alt="Preview"
                                        className="h-32 w-32 rounded-full object-cover border-2 border-green-500"
                                    />
                                    <p className="text-sm text-green-600 font-medium">Ready to upload</p>
                                </div>
                            ) : (
                                <>
                                    <DropzoneEmptyState className="w-full" />
                                    <div className="mt-4 flex flex-col items-center gap-2">
                                        {hasAvatarImage && profileData.previewUrl && !profileData.previewUrl.startsWith('blob:') ? (
                                            <img
                                                src={profileData.previewUrl}
                                                alt="Current profile"
                                                className="h-20 w-20 rounded-full object-cover border border-green-200"
                                            />
                                        ) : (
                                            <div className="h-20 w-20 rounded-full border border-dashed border-green-200 bg-green-50 flex items-center justify-center text-lg font-semibold text-green-700">
                                                {avatarInitials}
                                            </div>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            {hasAvatarImage ? 'Current profile photo' : 'No photo yet — drag & drop or click to upload'}
                                        </p>
                                    </div>
                                </>
                            )}
                        </div>
                    </Dropzone>

                    <DialogFooter className="flex flex-row gap-2 justify-end">
                        {selectedFile ? (
                            <>
                                <Button 
                                    type="button" 
                                    variant="outline" 
                                    onClick={() => {
                                        setSelectedFile(null);
                                        if (previewImage?.startsWith('blob:')) {
                                            URL.revokeObjectURL(previewImage);
                                        }
                                        setPreviewImage(null);
                                    }}
                                    disabled={isUploadingAvatar}
                                >
                                    Cancel
                                </Button>
                                <Button 
                                    type="button" 
                                    onClick={handleSaveAvatar}
                                    disabled={isUploadingAvatar}
                                    className="bg-green-600 hover:bg-green-700 flex items-center gap-2"
                                >
                                    {isUploadingAvatar ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Uploading...
                                        </>
                                    ) : (
                                        'Save Avatar'
                                    )}
                                </Button>
                            </>
                        ) : (
                            <Button type="button" variant="outline" onClick={() => setIsAvatarDialogOpen(false)}>
                                Close
                            </Button>
                        )}
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
