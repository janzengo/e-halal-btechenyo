<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../init.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class ExcelGenerator {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = config();
        try {
            $this->pdo = new PDO(
                "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']}",
                $config['DB_USERNAME'],
                $config['DB_PASSWORD'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ExcelGenerator();
        }
        return self::$instance;
    }

    public function generateVotersTemplate() {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        
        // Create the courses sheet
        $coursesSheet = $spreadsheet->createSheet();
        $coursesSheet->setTitle('CoursesList');

        // Get courses for dropdown
        $stmt = $this->pdo->prepare("SELECT id, description FROM courses ORDER BY description");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add courses to the hidden sheet
        $coursesSheet->setCellValue('A1', 'Course Name');
        $coursesSheet->setCellValue('B1', 'Course ID');
        $row = 2;
        foreach ($courses as $course) {
            $coursesSheet->setCellValue('A' . $row, $course['description']);
            $coursesSheet->setCellValue('B' . $row, $course['id']);
            $row++;
        }

        // Name the range for courses
        $lastRow = count($courses) + 1;
        $spreadsheet->addNamedRange(
            new \PhpOffice\PhpSpreadsheet\NamedRange(
                'CourseNames',
                $coursesSheet,
                '$A$2:$A$' . $lastRow
            )
        );

        // Hide the courses sheet
        $coursesSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // Set up the main sheet
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Voters Template');

        // Set headers
        $headers = ['Student Number', 'Course'];
        foreach (range('A', 'B') as $index => $column) {
            $sheet->setCellValue($column . '1', $headers[$index]);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
        }

        // Add data validation for course column (column B)
        $validation = $sheet->getCell('B2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Invalid Course')
            ->setError('Please select a course from the list')
            ->setPromptTitle('Select Course')
            ->setPrompt('Choose a course from the dropdown list')
            ->setFormula1('=CourseNames');

        // Copy validation to other cells in column B (up to 100 rows)
        for ($i = 3; $i <= 100; $i++) {
            $sheet->getCell('B' . $i)->setDataValidation(clone $validation);
        }

        // Auto-size columns
        foreach (range('A', 'B') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Protect the workbook structure
        $spreadsheet->getSecurity()->setLockStructure(true);

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'voters_template_' . date('Ymd_His') . '.xlsx';
        
        // Create uploads/templates directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/templates';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filepath = $uploadDir . '/' . $filename;
        $writer->save($filepath);
        return $filename;
    }

    public function processCourseMapping() {
        $stmt = $this->pdo->prepare("SELECT id, description FROM courses ORDER BY description");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $mapping = [];
        foreach ($courses as $course) {
            $mapping[$course['description']] = $course['id'];
        }
        
        return $mapping;
    }
} 