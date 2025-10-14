import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dropzone, DropzoneContent } from '@/components/ui/dropzone';
import { AlertCircle, Save, X, ShieldCheck } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface OfficerDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSubmit: (data: OfficerFormData) => Promise<void>;
    loading?: boolean;
}

export interface OfficerFormData extends Record<string, any> {
    username: string;
    email: string;
    firstname: string;
    lastname: string;
    role: string;
    gender: string;
    photo_file?: File;
}

export function OfficerDialog({ 
    open, 
    onOpenChange, 
    onSubmit,
    loading = false 
}: OfficerDialogProps) {
    const [formData, setFormData] = useState<OfficerFormData>({
        username: '',
        email: '',
        firstname: '',
        lastname: '',
        role: 'officer',
        gender: ''
    });

    const [errors, setErrors] = useState<Record<string, string>>({});
    const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);

    // Reset form when opening/closing
    useEffect(() => {
        if (open) {
            setErrors({});
            setUploadedFiles([]);
            setFormData({
                username: '',
                email: '',
                firstname: '',
                lastname: '',
                role: 'officer',
                gender: ''
            });
        } else {
            setErrors({});
            setUploadedFiles([]);
        }
    }, [open]);

    const handleInputChange = (field: keyof OfficerFormData, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        if (errors[field]) {
            setErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors[field];
                return newErrors;
            });
        }
    };

    const handleFileDrop = (acceptedFiles: File[]) => {
        if (acceptedFiles.length > 0) {
            setUploadedFiles([acceptedFiles[0]]);
            setFormData(prev => ({ ...prev, photo_file: acceptedFiles[0] }));
        }
    };

    const handleFileRemove = () => {
        setUploadedFiles([]);
        setFormData(prev => {
            const { photo_file, ...rest } = prev;
            return rest as OfficerFormData;
        });
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.username.trim()) {
            newErrors.username = 'Username is required';
        }

        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email address';
        }

        if (!formData.firstname.trim()) {
            newErrors.firstname = 'First name is required';
        }

        if (!formData.lastname.trim()) {
            newErrors.lastname = 'Last name is required';
        }

        if (!formData.gender) {
            newErrors.gender = 'Gender is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        await onSubmit(formData);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-2">
                        <ShieldCheck className="h-5 w-5 text-green-600" />
                        <DialogTitle>Add New Officer</DialogTitle>
                    </div>
                    <DialogDescription>
                        Create a new officer account. A password setup link will be sent to their email address.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Username */}
                    <div className="space-y-2">
                        <Label htmlFor="username">
                            Username <span className="text-red-600">*</span>
                        </Label>
                        <Input
                            id="username"
                            value={formData.username}
                            onChange={(e) => handleInputChange('username', e.target.value)}
                            placeholder="Enter username"
                            required
                        />
                        {errors.username && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.username}
                            </p>
                        )}
                    </div>

                    {/* Email */}
                    <div className="space-y-2">
                        <Label htmlFor="email">
                            Email <span className="text-red-600">*</span>
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            value={formData.email}
                            onChange={(e) => handleInputChange('email', e.target.value)}
                            placeholder="officer@example.com"
                            required
                        />
                        {errors.email && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.email}
                            </p>
                        )}
                    </div>

                    {/* Name Fields */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="firstname">
                                First Name <span className="text-red-600">*</span>
                            </Label>
                            <Input
                                id="firstname"
                                value={formData.firstname}
                                onChange={(e) => handleInputChange('firstname', e.target.value)}
                                placeholder="First name"
                                required
                            />
                            {errors.firstname && (
                                <p className="text-sm text-red-600 flex items-center gap-1">
                                    <AlertCircle className="h-4 w-4" />
                                    {errors.firstname}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="lastname">
                                Last Name <span className="text-red-600">*</span>
                            </Label>
                            <Input
                                id="lastname"
                                value={formData.lastname}
                                onChange={(e) => handleInputChange('lastname', e.target.value)}
                                placeholder="Last name"
                                required
                            />
                            {errors.lastname && (
                                <p className="text-sm text-red-600 flex items-center gap-1">
                                    <AlertCircle className="h-4 w-4" />
                                    {errors.lastname}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Gender */}
                    <div className="space-y-2">
                        <Label htmlFor="gender">
                            Gender <span className="text-red-600">*</span>
                        </Label>
                        <Select
                            value={formData.gender}
                            onValueChange={(value) => handleInputChange('gender', value)}
                            required
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select gender..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Male">Male</SelectItem>
                                <SelectItem value="Female">Female</SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.gender && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.gender}
                            </p>
                        )}
                    </div>

                    {/* Photo Upload */}
                    <div className="space-y-2">
                        <Label htmlFor="photo">Profile Photo (Optional)</Label>
                        <Dropzone
                            onDrop={handleFileDrop}
                            accept={{
                                'image/*': ['.png', '.jpg', '.jpeg', '.gif']
                            }}
                            maxSize={5 * 1024 * 1024}
                            maxFiles={1}
                        >
                            {uploadedFiles.length > 0 && (
                                <DropzoneContent 
                                    files={uploadedFiles} 
                                    onRemove={handleFileRemove}
                                />
                            )}
                        </Dropzone>
                        <p className="text-xs text-gray-500">
                            Optional: Upload a profile photo (PNG, JPG, GIF up to 5MB)
                        </p>
                    </div>

                    {/* Form Actions */}
                    <div className="flex justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={loading}
                        >
                            <X className="h-4 w-4 mr-2" />
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            variant="outlinePrimary"
                            disabled={loading}
                        >
                            {loading ? (
                                <>
                                    <Spinner className="h-4 w-4 mr-2" />
                                    Creating Account...
                                </>
                            ) : (
                                <>
                                    <Save className="h-4 w-4 mr-2" />
                                    Create Officer Account
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

