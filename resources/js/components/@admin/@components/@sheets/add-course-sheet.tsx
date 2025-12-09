import React, { useState, useEffect } from 'react';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, Save, X, GraduationCap } from 'lucide-react';

interface AddCourseSheetProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    course?: any | null;
    onSubmit: (data: CourseFormData) => Promise<void>;
    loading?: boolean;
}

export interface CourseFormData {
    description: string;
}

export function AddCourseSheet({ 
    open, 
    onOpenChange, 
    course, 
    onSubmit, 
    loading = false 
}: AddCourseSheetProps) {
    const [formData, setFormData] = useState<CourseFormData>({
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
                    description: course.description || ''
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
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
        <Sheet
            open={open}
            onOpenChange={onOpenChange}
            key="add-course-sheet"
        >
            <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
                <SheetHeader className="px-6 pt-6">
                    <SheetTitle className="text-gray-900 flex items-center gap-2">
                        <GraduationCap className="h-5 w-5" />
                        {course ? 'Edit Course' : 'Add New Course'}
                    </SheetTitle>
                    <SheetDescription>
                        {course ?
                            'Update the course details below.' :
                            'Fill in the details to create a new course for voter registration.'
                        }
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={handleSubmit} className="space-y-4 px-6 pb-6">
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
                    <div className="flex justify-end gap-3">
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
                            <Save className="h-4 w-4 mr-2" />
                            {loading ? 'Saving...' : (course ? 'Update Course' : 'Create Course')}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
