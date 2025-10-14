import React, { useState, useEffect } from 'react';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, Save, X, Users } from 'lucide-react';

interface AddPartylistSheetProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    partylist?: any | null;
    onSubmit: (data: PartylistFormData) => Promise<void>;
    loading?: boolean;
}

export interface PartylistFormData {
    name: string;
}

export function AddPartylistSheet({ 
    open, 
    onOpenChange, 
    partylist, 
    onSubmit, 
    loading = false 
}: AddPartylistSheetProps) {
    const [formData, setFormData] = useState<PartylistFormData>({
        name: ''
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when partylist changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (partylist) {
                // Edit mode - populate with existing data
                setFormData({
                    name: partylist.name || ''
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    name: ''
                });
            }
        } else {
            setErrors({});
        }
    }, [open, partylist]);

    const handleInputChange = (field: keyof PartylistFormData, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: '' }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.name.trim()) {
            newErrors.name = 'Partylist name is required';
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
            key="add-partylist-sheet"
        >
            <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
                <SheetHeader className="px-6 pt-6">
                    <SheetTitle className="text-gray-900 flex items-center gap-2">
                        <Users className="h-5 w-5" />
                        {partylist ? 'Edit Partylist' : 'Add New Partylist'}
                    </SheetTitle>
                    <SheetDescription>
                        {partylist ?
                            'Update the partylist details below.' :
                            'Fill in the details to create a new partylist for the election.'
                        }
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={handleSubmit} className="space-y-6 mt-6 px-6 pb-6">
                    {/* Partylist Name */}
                    <div className="space-y-2">
                        <Label htmlFor="name">Partylist Name *</Label>
                        <Input
                            id="name"
                            value={formData.name}
                            onChange={(e) => handleInputChange('name', e.target.value)}
                            placeholder="e.g., Progressive Alliance, Unity Party, Student Coalition..."
                            className={errors.name ? 'border-red-500' : ''}
                        />
                        {errors.name && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.name}
                            </p>
                        )}
                        <p className="text-sm text-gray-500">
                            This will be the official name of the partylist that appears on the ballot
                        </p>
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
                            {loading ? 'Saving...' : (partylist ? 'Update Partylist' : 'Create Partylist')}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
