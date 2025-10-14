import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, Save, X, Users, Check, Plus    } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';

interface PartylistDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    partylist?: any | null;
    onSubmit: (data: PartylistFormData) => Promise<void>;
    loading?: boolean;
}

export interface PartylistFormData {
    name: string;
    color: string;
    platform: string;
}

export function PartylistDialog({ 
    open, 
    onOpenChange, 
    partylist, 
    onSubmit, 
    loading = false 
}: PartylistDialogProps) {
    const presetColors = [
        { name: 'Blue', value: '#3B82F6' },
        { name: 'Red', value: '#EF4444' },
        { name: 'Green', value: '#10B981' },
        { name: 'Yellow', value: '#F59E0B' },
        { name: 'Purple', value: '#8B5CF6' },
    ];

    const [formData, setFormData] = useState<PartylistFormData>({
        name: '',
        color: '#3B82F6',
        platform: ''
    });
    const [customColor, setCustomColor] = useState(false);

    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when opening/closing or when partylist changes
    useEffect(() => {
        if (open) {
            setErrors({});
            
            if (partylist) {
                // Edit mode - populate with existing data
                setFormData({
                    name: partylist.name || '',
                    color: partylist.color || '#3B82F6',
                    platform: partylist.platform || ''
                });
            } else {
                // Create mode - reset to defaults
                setFormData({
                    name: '',
                    color: '#3B82F6',
                    platform: ''
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
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Users className="h-5 w-5" />
                        {partylist ? 'Edit Partylist' : 'Add New Partylist'}
                    </DialogTitle>
                    <DialogDescription>
                        {partylist ?
                            'Update the partylist details below.' :
                            'Fill in the details to create a new partylist for the election.'
                        }
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
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
                        <p className="text-xs text-gray-500">
                            This will be the official name of the partylist that appears on the ballot
                        </p>
                    </div>

                    {/* Color Selection */}
                    <div className="space-y-3">
                        <Label>Partylist Color *</Label>
                        
                        {/* Preset Colors */}
                        <div className="flex gap-2">
                            {presetColors.map((color) => (
                                <Button
                                    key={color.value}
                                    type="button"
                                    onClick={() => {
                                        handleInputChange('color', color.value);
                                        setCustomColor(false);
                                    }}
                                    className={`relative w-10 h-10 rounded-md border-2 transition-all ${
                                        formData.color === color.value && !customColor
                                            ? 'border-gray-900 scale-110'
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}
                                    style={{ backgroundColor: color.value }}
                                    title={color.name}
                                >
                                    {formData.color === color.value && !customColor && (
                                        <Check className="h-5 w-5 text-white absolute inset-0 m-auto drop-shadow-md" />
                                    )}
                                </Button>
                            ))}
                            
                            {/* Custom Color Button */}
                            <Button
                                type="button"
                                onClick={() => setCustomColor(true)}
                                className={`relative w-10 h-10 rounded-md border-2 transition-all flex items-center justify-center ${
                                    customColor
                                        ? 'border-gray-900 scale-110'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}
                                style={{
                                    background: 'transparent'
                                }}
                                title="Custom Color"
                            >
                                {customColor ? (
                                    <Check className="h-5 w-5 text-white absolute inset-0 m-auto drop-shadow-md" />
                                ) : (
                                    <span className="flex items-center justify-center w-full h-full">
                                        <Plus className="h-5 w-5 text-black drop-shadow-md" />
                                    </span>
                                )}
                            </Button>
                        </div>

                        {/* Custom Color Input */}
                        {customColor && (
                            <div className="flex gap-2 items-center">
                                <Input
                                    type="color"
                                    value={formData.color}
                                    onChange={(e) => handleInputChange('color', e.target.value)}
                                    className="w-20 h-10 cursor-pointer"
                                />
                                <Input
                                    type="text"
                                    value={formData.color}
                                    onChange={(e) => handleInputChange('color', e.target.value)}
                                    placeholder="#000000"
                                    className="flex-1 font-mono"
                                    maxLength={7}
                                />
                            </div>
                        )}

                        <p className="text-xs text-gray-500">
                            This color will be used to represent the partylist throughout the system
                        </p>
                    </div>

                    {/* Platform */}
                    <div className="space-y-2">
                        <Label htmlFor="platform">Platform, Mission & Vision</Label>
                        <textarea
                            id="platform"
                            value={formData.platform}
                            onChange={(e) => handleInputChange('platform', e.target.value)}
                            placeholder="Describe the partylist's platform, mission, and vision..."
                            rows={4}
                            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-vertical placeholder:text-sm"
                        />
                        {errors.platform && (
                            <p className="text-sm text-red-600 flex items-center gap-1">
                                <AlertCircle className="h-4 w-4" />
                                {errors.platform}
                            </p>
                        )}
                        <p className="text-xs text-gray-500">
                            Optional: Describe the partylist's goals, mission, and vision (max 1000 characters)
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
                                    {partylist ? 'Update Partylist' : 'Create Partylist'}
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
