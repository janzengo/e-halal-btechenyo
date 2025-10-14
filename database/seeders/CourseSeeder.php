<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            // Education
            ['code' => 'BEEd', 'description' => 'Bachelor of Elementary Education'],
            ['code' => 'BSEd', 'description' => 'Bachelor of Secondary Education'],

            // Information Technology
            ['code' => 'BSIT', 'description' => 'Bachelor of Science in Information Technology'],

            // Hospitality and Tourism
            ['code' => 'BSHM', 'description' => 'Bachelor of Science in Hospitality Management'],
            ['code' => 'BSTM', 'description' => 'Bachelor of Science in Tourism Management'],

            // Accountancy
            ['code' => 'BSA', 'description' => 'Bachelor of Science in Accountancy'],

            // Business Administration (with majors)
            ['code' => 'BSBA-FM', 'description' => 'Bachelor of Science in Business Administration Major in Financial Management'],
            ['code' => 'BSBA-MM', 'description' => 'Bachelor of Science in Business Administration Major in Marketing Management'],
            ['code' => 'BSBA-BE', 'description' => 'Bachelor of Science in Business Administration Major in Business Economics'],
            ['code' => 'BSBA-ENT', 'description' => 'Bachelor of Science in Business Administration Major in Entrepreneurship'],

            // Accounting Related
            ['code' => 'BSMA', 'description' => 'Bachelor of Science in Management Accounting'],
            ['code' => 'BSIA', 'description' => 'Bachelor of Science in Internal Auditing'],

            // Arts and Sciences
            ['code' => 'AB-HIS', 'description' => 'Bachelor of Arts in History'],
            ['code' => 'BS-MATH', 'description' => 'Bachelor of Science in Mathematics'],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['code' => $course['code']],
                ['description' => $course['description']]
            );
        }

        $this->command->info('Successfully seeded '.count($courses).' courses.');
    }
}
