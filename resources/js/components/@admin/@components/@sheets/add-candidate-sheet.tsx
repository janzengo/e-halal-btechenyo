import React, { useState, useEffect } from 'react';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, Save, X, ContactRound, Upload } from 'lucide-react';

interface AddCandidateSheetProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    candidate?: any | null;
    positions: any[];
    partylists: any[];
    onSubmit: (data: CandidateFormData) => Promise<void>;
    loading?: boolean;
}

export interface CandidateFormData {
    firstname: string;
    lastname: string;
    position_id: number;
    partylist_id: number;
    photo?: string;
    platform: string;
}

export function AddCandidateSheet({ 
    open, 
    onOpenChange, 
    candidate, 
    positions,
    partylists,
    onSubmit, 
    loading = false 
}: AddCandidateSheetProps) {
    const [formData, setFormData] = useState<CandidateFormData>({
        firstname: '',
        lastname: '',
        position_id: 0,
        partylist_id: 0,
        photo: '',
        platform: ''
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when candidate changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (candidate) {
                // Edit mode - populate with existing data
                setFormData({
                    firstname: candidate.firstname || '',
                    lastname: candidate.lastname || '',
                    position_id: candidate.position_id || 0,
                    partylist_id: candidate.partylist_id || 0,
                    photo: candidate.photo || '',
                    platform: candidate.platform || ''
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    firstname: '',
                    lastname: '',
                    position_id: 0,
                    partylist_id: 0,
                    photo: '',
                    platform: ''
                });
            }
        } else {
            setErrors({});
        }
    }, [open, candidate]);

    const handleInputChange = (field: keyof CandidateFormData, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: '' }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.firstname.trim()) {
            newErrors.firstname = 'First name is required';
        }

        if (!formData.lastname.trim()) {
            newErrors.lastname = 'Last name is required';
        }

        if (!formData.position_id) {
            newErrors.position_id = 'Please select a position';
        }

        if (!formData.partylist_id) {
            newErrors.partylist_id = 'Please select a partylist';
        }

        if (!formData.platform.trim()) {
            newErrors.platform = 'Platform is required';
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
            key="add-candidate-sheet"
        >
            <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
                <SheetHeader className="px-6 pt-6">
                    <SheetTitle className="text-gray-900 flex items-center gap-2">
                        <ContactRound className="h-5 w-5" />
                        {candidate ? 'Edit Candidate' : 'Add New Candidate'}
                    </SheetTitle>
                    <SheetDescription>
                        {candidate ?
                            'Update the candidate details below.' :
                            'Fill in the details to add a new candidate for the election.'
                        }
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={handleSubmit} className="space-y-6 mt-6 px-6 pb-6">
                    {/* Name Fields */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="firstname">First Name *</Label>
                            <Input
                                id="firstname"
                                value={formData.firstname}
                                onChange={(e) => handleInputChange('firstname', e.target.value)}
                                placeholder="Enter first name..."
                                className={errors.firstname ? 'border-red-500' : ''}
                            />
                            {errors.firstname && (
                                <p className="text-sm text-red-600 flex items-center gap-1">
                                    <AlertCircle className="h-4 w-4" />
                                    {errors.firstname}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="lastname">Last Name *</Label>
                            <Input
                                id="lastname"
                                value={formData.lastname}
                                onChange={(e) => handleInputChange('lastname', e.target.value)}
                                placeholder="Enter last name..."
                                className={errors.lastname ? 'border-red-500' : ''}
                            />
                            {errors.lastname && (
                                <p className="text-sm text-red-600 flex items-center gap-1">
                                    <AlertCircle className="h-4 w-4" />
                                    {errors.lastname}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Position */}
                    <div className="space-y-2">
                        <Label htmlFor="position_id">Position *</Label>
                        <Select
                            value={formData.position_id.toString()}
                            onValueChange={(value) => handleInputChange('position_id', parseInt(value))}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a position..." />
                            </SelectTrigger>
                            <SelectContent>
                                {positions.map((position) => (
                                    <SelectItem key={position.id} value={position.id.toString()}>
                                        {position.description}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.position_id && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.position_id}
                            </p>
                        )}
                    </div>

                    {/* Partylist */}
                    <div className="space-y-2">
                        <Label htmlFor="partylist_id">Partylist *</Label>
                        <Select
                            value={formData.partylist_id.toString()}
                            onValueChange={(value) => handleInputChange('partylist_id', parseInt(value))}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a partylist..." />
                            </SelectTrigger>
                            <SelectContent>
                                {partylists.map((partylist) => (
                                    <SelectItem key={partylist.id} value={partylist.id.toString()}>
                                        {partylist.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.partylist_id && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.partylist_id}
                            </p>
                        )}
                    </div>

                    {/* Photo URL */}
                    <div className="space-y-2">
                        <Label htmlFor="photo">Photo URL (Optional)</Label>
                        <div className="flex gap-2">
                            <Input
                                id="photo"
                                value={formData.photo}
                                onChange={(e) => handleInputChange('photo', e.target.value)}
                                placeholder="https://example.com/photo.jpg"
                            />
                            <Button type="button" variant="outline" size="sm">
                                <Upload className="h-4 w-4" />
                            </Button>
                        </div>
                        <p className="text-sm text-gray-500">
                            Leave empty to use default profile image
                        </p>
                    </div>

                    {/* Platform */}
                    <div className="space-y-2">
                        <Label htmlFor="platform">Platform *</Label>
                        <Textarea
                            id="platform"
                            value={formData.platform}
                            onChange={(e) => handleInputChange('platform', e.target.value)}
                            placeholder="Describe the candidate's platform and goals..."
                            rows={4}
                            className={errors.platform ? 'border-red-500' : ''}
                        />
                        {errors.platform && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.platform}
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
                            {loading ? 'Saving...' : (candidate ? 'Update Candidate' : 'Add Candidate')}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
