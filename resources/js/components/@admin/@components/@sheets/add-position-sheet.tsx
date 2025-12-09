import React, { useState, useEffect } from 'react';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, Save, X, ListChecks } from 'lucide-react';

interface AddPositionSheetProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    position?: any | null;
    onSubmit: (data: PositionFormData) => Promise<void>;
    loading?: boolean;
}

export interface PositionFormData {
    title: string;
    max_winners: number;
    priority: number;
}

export function AddPositionSheet({ 
    open, 
    onOpenChange, 
    position, 
    onSubmit, 
    loading = false 
}: AddPositionSheetProps) {
    const [formData, setFormData] = useState<PositionFormData>({
        title: '',
        max_winners: 1,
        priority: 1
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when position changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (position) {
                // Edit mode - populate with existing data
                setFormData({
                    title: position.title || '',
                    max_winners: position.max_winners || 1,
                    priority: position.priority || 1
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    title: '',
                    max_winners: 1,
                    priority: 1
                });
            }
        } else {
            setErrors({});
        }
    }, [open, position]);

    const handleInputChange = (field: keyof PositionFormData, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: '' }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.title.trim()) {
            newErrors.title = 'Position title is required';
        }

        if (formData.max_winners < 1) {
            newErrors.max_winners = 'Maximum winners must be at least 1';
        }

        if (formData.priority < 1) {
            newErrors.priority = 'Priority must be at least 1';
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
            key="add-position-sheet"
        >
            <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
                <SheetHeader className="px-6 pt-6">
                    <SheetTitle className="text-gray-900 flex items-center gap-2">
                        <ListChecks className="h-5 w-5" />
                        {position ? 'Edit Position' : 'Add New Position'}
                    </SheetTitle>
                    <SheetDescription>
                        {position ?
                            'Update the position details below.' :
                            'Fill in the details to create a new position for the election.'
                        }
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={handleSubmit} className="space-y-6 mt-6 px-6 pb-6">
                    {/* Position Title */}
                    <div className="space-y-2">
                        <Label htmlFor="title">Position Title *</Label>
                        <Input
                            id="title"
                            value={formData.title}
                            onChange={(e) => handleInputChange('title', e.target.value)}
                            placeholder="e.g., President, Vice President, Secretary..."
                            className={errors.title ? 'border-red-500' : ''}
                        />
                        {errors.title && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.title}
                            </p>
                        )}
                    </div>

                    {/* Maximum Winners */}
                    <div className="space-y-2">
                        <Label htmlFor="max_winners">Maximum Winners *</Label>
                        <Select
                            value={formData.max_winners.toString()}
                            onValueChange={(value) => handleInputChange('max_winners', parseInt(value))}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(num => (
                                    <SelectItem key={num} value={num.toString()}>
                                        {num} {num === 1 ? 'Winner' : 'Winners'}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.max_winners && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.max_winners}
                            </p>
                        )}
                    </div>

                    {/* Priority */}
                    <div className="space-y-2">
                        <Label htmlFor="priority">Priority Order *</Label>
                        <Input
                            id="priority"
                            type="number"
                            min="1"
                            value={formData.priority}
                            onChange={(e) => handleInputChange('priority', parseInt(e.target.value) || 1)}
                            placeholder="1"
                            className={errors.priority ? 'border-red-500' : ''}
                        />
                        <p className="text-sm text-gray-500">
                            Lower numbers appear first on the ballot (1 = highest priority)
                        </p>
                        {errors.priority && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.priority}
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
                            {loading ? 'Saving...' : (position ? 'Update Position' : 'Create Position')}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
