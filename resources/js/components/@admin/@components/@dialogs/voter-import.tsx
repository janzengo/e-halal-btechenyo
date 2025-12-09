import React, { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dropzone, DropzoneContent } from '@/components/ui/dropzone';
import { AlertCircle, Upload, X, Download, FileSpreadsheet } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';
import { router } from '@inertiajs/react';
import { toast } from 'sonner';

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
    const [downloadingTemplate, setDownloadingTemplate] = useState(false);

    const handleFileDrop = (acceptedFiles: File[]) => {
        // Validate accepted files
        if (acceptedFiles.length > 0) {
            const file = acceptedFiles[0];

            // Additional validation for CSV
            if (!file.name.toLowerCase().endsWith('.csv')) {
                toast.error('Invalid file type', {
                    description: 'Please upload a CSV file (.csv)'
                });
                return;
            }

            // Check file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                toast.error('File too large', {
                    description: 'Maximum file size is 10MB'
                });
                return;
            }

            setUploadedFiles([file]);
            toast.success('File uploaded successfully', {
                description: file.name
            });
        }
    };

    const handleFileRejected = (rejectedFiles: any[]) => {
        if (rejectedFiles.length > 0) {
            const rejection = rejectedFiles[0];
            const errors = rejection.errors || [];

            if (errors.some((e: any) => e.code === 'file-too-large')) {
                toast.error('File too large', {
                    description: 'Maximum file size is 10MB'
                });
            } else if (errors.some((e: any) => e.code === 'file-invalid-type')) {
                toast.error('Invalid file type', {
                    description: 'Please upload a CSV file (.csv)'
                });
            } else {
                toast.error('File upload failed', {
                    description: 'Please check your file and try again'
                });
            }
        }
    };

    const handleFileRemove = () => {
        setUploadedFiles([]);
    };

    const handleDownloadTemplate = async () => {
        setDownloadingTemplate(true);

        try {
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = '/head/voters/template/download';
            link.download = 'voters_template.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Add a small delay to show the spinner
            setTimeout(() => {
                setDownloadingTemplate(false);
            }, 1500);
        } catch (error) {
            console.error('Download error:', error);
            setDownloadingTemplate(false);
        }
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
                toast.success('Voters imported successfully!');
            },
            onError: (errors) => {
                console.error('Import errors:', errors);
                
                // Show specific error messages
                if (errors.csv_file) {
                    toast.error('File validation failed', {
                        description: errors.csv_file
                    });
                } else if (Object.keys(errors).length > 0) {
                    const firstError = Object.values(errors)[0];
                    toast.error('Import failed', {
                        description: typeof firstError === 'string' ? firstError : 'Please check your file and try again'
                    });
                } else {
                    toast.error('Import failed', {
                        description: 'An unexpected error occurred'
                    });
                }
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
                        <DialogTitle>Import Voters</DialogTitle>
                    </div>
                    <DialogDescription>
                        Import voters using our Excel template with course validation dropdowns.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Template Download Info */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div className="flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 text-blue-600 mt-0.5" />
                            <div className="flex-1">
                                <h4 className="font-semibold text-blue-900 mb-1">Step-by-Step Instructions</h4>
                                <ol className="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                                    <li><strong>Download</strong> the Excel template below</li>
                                    <li><strong>Fill in</strong> student numbers (9 digits, e.g., 202020020)</li>
                                    <li><strong>Select course codes</strong> from the dropdown in column B (e.g., BSIT, BSHM)</li>
                                    <li><strong>Export/Save</strong> the Excel file as CSV format</li>
                                    <li><strong>Upload</strong> the CSV file using the form below</li>
                                </ol>
                                <div className="mt-3 p-2 bg-blue-100 rounded text-xs text-blue-700">
                                    <strong>Note:</strong> Student numbers become emails: [number]@btech.ph.education
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={handleDownloadTemplate}
                                    disabled={downloadingTemplate}
                                    className="mt-3 border-blue-300 text-blue-700 hover:bg-blue-100 disabled:opacity-50"
                                >
                                    {downloadingTemplate ? (
                                        <>
                                            <Spinner className="h-4 w-4 mr-2" />
                                            Downloading...
                                        </>
                                    ) : (
                                        <>
                                            <Download className="h-4 w-4 mr-2" />
                                            Download Excel Template
                                        </>
                                    )}
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
                            onDropRejected={handleFileRejected}
                            accept={{
                                'text/csv': ['.csv']
                            }}
                            maxSize={10 * 1024 * 1024}
                            maxFiles={1}
                        >
                            {uploadedFiles.length > 0 ? (
                                <DropzoneContent
                                    files={uploadedFiles}
                                    onRemove={handleFileRemove}
                                />
                            ) : (
                                <div className="flex flex-col items-center gap-3">
                                    <Upload className="h-10 w-10 text-gray-400" />
                                    <div className="text-sm text-gray-600 space-y-1">
                                        <p className="font-medium">Drag & drop a CSV file here</p>
                                        <p className="text-xs text-gray-500">or click to browse files</p>
                                        <p className="text-xs text-gray-400 mt-2">CSV files up to 10MB</p>
                                    </div>
                                </div>
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



