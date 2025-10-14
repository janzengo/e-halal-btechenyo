import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, Save, X, UserCheck } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface VoterDialogProps {
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

export function VoterDialog({ 
    open, 
    onOpenChange, 
    voter, 
    courses,
    onSubmit, 
    loading = false 
}: VoterDialogProps) {
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
        } else if (!/^\d{9}$/.test(formData.student_number)) {
            newErrors.student_number = 'Student number must be exactly 9 digits';
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
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <UserCheck className="h-5 w-5" />
                        {voter ? 'Edit Voter' : 'Add New Voter'}
                    </DialogTitle>
                    <DialogDescription>
                        {voter ?
                            'Update the voter details below.' :
                            'Fill in the details to register a new voter for the election.'
                        }
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Student Number */}
                    <div className="space-y-2">
                        <Label htmlFor="student_number">
                            Student Number <span className="text-red-600">*</span>
                        </Label>
                        <Input
                            id="student_number"
                            value={formData.student_number}
                            onChange={(e) => {
                                // Only allow digits
                                const value = e.target.value.replace(/\D/g, '');
                                handleInputChange('student_number', value);
                            }}
                            placeholder="202320023"
                            maxLength={9}
                            className={errors.student_number ? 'border-red-500' : ''}
                            required
                        />
                        {errors.student_number && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.student_number}
                            </p>
                        )}
                        <p className="text-xs text-gray-500">
                            Must be exactly 9 digits. Email will be: <span className="font-mono font-medium">{formData.student_number || 'XXXXXXXXX'}@btech.ph.education</span>
                        </p>
                    </div>

                    {/* Course */}
                    <div className="space-y-2">
                        <Label htmlFor="course_id">
                            Course <span className="text-red-600">*</span>
                        </Label>
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
                                    {voter ? 'Update Voter' : 'Add Voter'}
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
