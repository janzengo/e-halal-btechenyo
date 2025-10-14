import React, { useState, useEffect } from 'react';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, Save, X, UserCheck } from 'lucide-react';

interface AddVoterSheetProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    voter?: any | null;
    courses: any[];
    onSubmit: (data: VoterFormData) => Promise<void>;
    loading?: boolean;
}

export interface VoterFormData {
    student_number: string;
    course_id: number;
}

export function AddVoterSheet({ 
    open, 
    onOpenChange, 
    voter, 
    courses,
    onSubmit, 
    loading = false 
}: AddVoterSheetProps) {
    const [formData, setFormData] = useState<VoterFormData>({
        student_number: '',
        course_id: 0
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when voter changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (voter) {
                // Edit mode - populate with existing data
                setFormData({
                    student_number: voter.student_number || '',
                    course_id: voter.course_id || 0
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    student_number: '',
                    course_id: 0
                });
            }
        } else {
            setErrors({});
        }
    }, [open, voter]);

    const handleInputChange = (field: keyof VoterFormData, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: '' }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.student_number.trim()) {
            newErrors.student_number = 'Student number is required';
        }

        if (!formData.course_id) {
            newErrors.course_id = 'Please select a course';
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
            key="add-voter-sheet"
        >
            <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
                <SheetHeader className="px-6 pt-6">
                    <SheetTitle className="text-gray-900 flex items-center gap-2">
                        <UserCheck className="h-5 w-5" />
                        {voter ? 'Edit Voter' : 'Add New Voter'}
                    </SheetTitle>
                    <SheetDescription>
                        {voter ?
                            'Update the voter details below.' :
                            'Fill in the details to register a new voter for the election.'
                        }
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={handleSubmit} className="space-y-6 mt-6 px-6 pb-6">
                    {/* Student Number */}
                    <div className="space-y-2">
                        <Label htmlFor="student_number">Student Number *</Label>
                        <Input
                            id="student_number"
                            value={formData.student_number}
                            onChange={(e) => handleInputChange('student_number', e.target.value)}
                            placeholder="e.g., 2024-12345"
                            className={errors.student_number ? 'border-red-500' : ''}
                        />
                        {errors.student_number && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.student_number}
                            </p>
                        )}
                        <p className="text-sm text-gray-500">
                            This will be used as the voter's unique identifier
                        </p>
                    </div>

                    {/* Course */}
                    <div className="space-y-2">
                        <Label htmlFor="course_id">Course *</Label>
                        <Select
                            value={formData.course_id.toString()}
                            onValueChange={(value) => handleInputChange('course_id', parseInt(value))}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a course..." />
                            </SelectTrigger>
                            <SelectContent>
                                {courses.map((course) => (
                                    <SelectItem key={course.id} value={course.id.toString()}>
                                        {course.description}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.course_id && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.course_id}
                            </p>
                        )}
                    </div>

                    {/* Form Actions */}
                    <div className="flex justify-end gap-3 pt-4 border-t">
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
                            {loading ? 'Saving...' : (voter ? 'Update Voter' : 'Add Voter')}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
