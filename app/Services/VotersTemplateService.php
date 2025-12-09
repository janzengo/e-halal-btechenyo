<?php

namespace App\Services;

use App\Exports\VotersTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class VotersTemplateService
{
    /**
     * Generate Excel template with course dropdown validation
     */
    public function generateTemplate(): string
    {
        // Generate filename
        $filename = 'voters_template_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Generate Excel file using Laravel Excel 3.1 API
        Excel::store(new VotersTemplateExport, 'temp/' . $filename);

        return $filename;
    }

    /**
     * Get the file path for a generated template
     */
    public function getTemplatePath(string $filename): string
    {
        return storage_path('app/temp/' . $filename);
    }

    /**
     * Clean up old template files
     */
    public function cleanupOldTemplates(): void
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/voters_template_*.xlsx');
        $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}