import React, { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dropzone, DropzoneContent } from '@/components/ui/dropzone';
import { AlertCircle, Upload, X, Download, FileSpreadsheet } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';
import { router } from '@inertiajs/react';

interface VoterImportDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function VoterImportDialog({ 
    open, 
    onOpenChange
}: VoterImportDialogProps) {
    const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
    const [uploading, setUploading] = useState(false);

    const handleFileDrop = (acceptedFiles: File[]) => {
        if (acceptedFiles.length > 0) {
            setUploadedFiles([acceptedFiles[0]]);
        }
    };

    const handleFileRemove = () => {
        setUploadedFiles([]);
    };

    const handleDownloadTemplate = () => {
        window.location.href = '/head/voters/template/download';
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (uploadedFiles.length === 0) {
            return;
        }

        setUploading(true);

        const formData = new FormData();
        formData.append('csv_file', uploadedFiles[0]);

        router.post('/head/voters/import', formData, {
            preserveScroll: true,
            onSuccess: () => {
                setUploadedFiles([]);
                onOpenChange(false);
            },
            onError: (errors) => {
                console.error('Import errors:', errors);
            },
            onFinish: () => {
                setUploading(false);
            }
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <div className="flex items-center gap-2">
                        <FileSpreadsheet className="h-5 w-5 text-green-600" />
                        <DialogTitle>Import Voters from CSV</DialogTitle>
                    </div>
                    <DialogDescription>
                        Upload a CSV file containing student numbers and courses to bulk register voters.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Template Download Info */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div className="flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 text-blue-600 mt-0.5" />
                            <div className="flex-1">
                                <h4 className="font-semibold text-blue-900 mb-1">Important Instructions</h4>
                                <ul className="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                    <li>Student numbers must be exactly 9 digits (e.g., 202320023)</li>
                                    <li>This will become the student's email: [student_number]@btech.ph.education</li>
                                    <li>Course codes must match exactly</li>
                                    <li>Download the template below to ensure proper formatting</li>
                                </ul>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={handleDownloadTemplate}
                                    className="mt-3 border-blue-300 text-blue-700 hover:bg-blue-100"
                                >
                                    <Download className="h-4 w-4 mr-2" />
                                    Download Template
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* File Upload */}
                    <div className="space-y-2">
                        <Label htmlFor="csv_file">
                            CSV File <span className="text-red-600">*</span>
                        </Label>
                        <Dropzone
                            onDrop={handleFileDrop}
                            accept={{
                                'text/csv': ['.csv'],
                                'text/plain': ['.txt']
                            }}
                            maxSize={10 * 1024 * 1024}
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
                            Upload a CSV file (max 10MB)
                        </p>
                    </div>

                    {/* Form Actions */}
                    <div className="flex justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={uploading}
                        >
                            <X className="h-4 w-4 mr-2" />
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            variant="outlinePrimary"
                            disabled={uploading || uploadedFiles.length === 0}
                        >
                            {uploading ? (
                                <>
                                    <Spinner className="h-4 w-4 mr-2" />
                                    Importing...
                                </>
                            ) : (
                                <>
                                    <Upload className="h-4 w-4 mr-2" />
                                    Import Voters
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}



