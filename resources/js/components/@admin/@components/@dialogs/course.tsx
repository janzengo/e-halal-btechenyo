import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, Save, X, GraduationCap } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface CourseDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    course?: any | null;
    onSubmit: (data: CourseFormData) => Promise<void>;
    loading?: boolean;
}

export interface CourseFormData {
    code: string;
    description: string;
}

export function CourseDialog({ 
    open, 
    onOpenChange, 
    course, 
    onSubmit, 
    loading = false 
}: CourseDialogProps) {
    const [formData, setFormData] = useState<CourseFormData>({
        code: '',
        description: ''
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when course changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (course) {
                // Edit mode - populate with existing data
                setFormData({
                    code: course.code || '',
                    description: course.description || ''
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    code: '',
                    description: ''
                });
            }
        } else {
            setErrors({});
        }
    }, [open, course]);

    const handleInputChange = (field: keyof CourseFormData, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: '' }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.code.trim()) {
            newErrors.code = 'Course code is required';
        } else if (formData.code.length > 10) {
            newErrors.code = 'Course code cannot exceed 10 characters';
        }

        if (!formData.description.trim()) {
            newErrors.description = 'Course description is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        try {
            await onSubmit(formData);
        } catch (error) {
            console.error('Error submitting form:', error);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <GraduationCap className="h-5 w-5" />
                        {course ? 'Edit Course' : 'Add New Course'}
                    </DialogTitle>
                    <DialogDescription>
                        {course ?
                            'Update the course details below.' :
                            'Fill in the details to create a new course for voter registration.'
                        }
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Course Code */}
                    <div className="space-y-2">
                        <Label htmlFor="code">Course Code *</Label>
                        <Input
                            id="code"
                            value={formData.code}
                            onChange={(e) => handleInputChange('code', e.target.value.toUpperCase())}
                            placeholder="e.g., BSCS, BAPOL, BSIT..."
                            maxLength={10}
                            className={errors.code ? 'border-red-500' : ''}
                        />
                        {errors.code && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.code}
                            </p>
                        )}
                        <p className="text-xs text-gray-500">
                            Short code to identify the course (max 10 characters)
                        </p>
                    </div>

                    {/* Course Description */}
                    <div className="space-y-2">
                        <Label htmlFor="description">Course Description *</Label>
                        <Input
                            id="description"
                            value={formData.description}
                            onChange={(e) => handleInputChange('description', e.target.value)}
                            placeholder="e.g., Bachelor of Science in Computer Science, Bachelor of Arts in Political Science..."
                            className={errors.description ? 'border-red-500' : ''}
                        />
                        {errors.description && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.description}
                            </p>
                        )}
                        <p className="text-xs text-gray-500">
                            This will be the full name of the course that appears in voter registration
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
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <Save className="h-4 w-4 mr-2" />
                                    {course ? 'Update Course' : 'Create Course'}
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
