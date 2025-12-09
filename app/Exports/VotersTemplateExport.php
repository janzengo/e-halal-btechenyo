<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VotersTemplateExport implements WithMultipleSheets, WithEvents
{
    public function sheets(): array
    {
        $courses = Course::orderBy('code')->get();
        
        return [
            new CoursesListSheet($courses),
            new VotersTemplateSheet($courses),
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $spreadsheet = $event->writer->getDelegate();
                
                // Get the courses sheet and main sheet
                $coursesSheet = $spreadsheet->getSheetByName('CoursesList');
                $mainSheet = $spreadsheet->getSheetByName('Voters Template');
                
                if ($coursesSheet && $mainSheet) {
                    // Get course count from the courses sheet
                    $highestRow = $coursesSheet->getHighestRow();
                    
                    if ($highestRow > 1) {
                        // Create Named Range for course codes (column A)
                        $spreadsheet->addNamedRange(
                            new NamedRange(
                                'CourseCodes',
                                $coursesSheet,
                                '$A$2:$A$' . $highestRow
                            )
                        );
                        
                        // Create validation for first cell
                        $validation = $mainSheet->getCell('B2')->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST)
                            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                            ->setAllowBlank(false)
                            ->setShowInputMessage(true)
                            ->setShowErrorMessage(true)
                            ->setShowDropDown(true)
                            ->setErrorTitle('Invalid Course')
                            ->setError('Please select a course code from the list')
                            ->setPromptTitle('Select Course Code')
                            ->setPrompt('Choose a course code from the dropdown list (e.g., BSIT, BSHM)')
                            ->setFormula1('=CourseCodes');
                        
                        // Copy validation to other cells in column B (up to 100 rows)
                        for ($row = 3; $row <= 100; $row++) {
                            $mainSheet->getCell('B' . $row)->setDataValidation(clone $validation);
                        }
                    }
                }
                
                // Set the main sheet as active
                $spreadsheet->setActiveSheetIndexByName('Voters Template');
            },
        ];
    }
}

class CoursesListSheet implements FromCollection, WithHeadings, WithEvents
{
    protected $courses;

    public function __construct($courses)
    {
        $this->courses = $courses;
    }

    public function headings(): array
    {
        return [
            'Course Code',
            'Course Name',
        ];
    }

    public function collection()
    {
        // Get courses with code and description for reference
        return $this->courses->map(function ($course) {
            return [
                $course->code,
                $course->description,
            ];
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set sheet title
                $sheet->setTitle('CoursesList');
                
                // Style headers for better readability
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'E3F2FD']
                    ],
                ]);
                
                // Auto-size columns
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                
                // Hide this sheet
                $sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}

class VotersTemplateSheet implements FromCollection, WithHeadings, WithEvents
{
    protected $courses;

    public function __construct($courses)
    {
        $this->courses = $courses;
    }

    public function collection()
    {
        // Return empty collection - no sample data
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'Student Number',
            'Course Code',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set worksheet name
                $sheet->setTitle('Voters Template');
                
                // Style headers
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'E3F2FD']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
            },
        ];
    }
}
