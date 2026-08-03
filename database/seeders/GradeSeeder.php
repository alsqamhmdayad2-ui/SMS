<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            [
                'name' => 'المرحلة الابتدائية', 
                'description' => 'تضم الفئة العمرية التأسيسية، وتشمل الصفوف من الأول إلى السادس الابتدائي ببرامج تعليمية تفاعلية ممتازة.', 
                'status' => 1,
            ],
            [
                'name' => 'المرحلة الإعدادية', 
                'description' => 'تضم الفئة العمرية المتوسطة، وتشمل الصفوف من السابع إلى التاسع وتوفر بيئة تعليمية أكاديمية متقدمة.', 
                'status' => 1,
            ],
            [
                'name' => 'المرحلة الثانوية', 
                'description' => 'تضم الفئة العمرية المتقدمة، وتشمل الصفوف من العاشر إلى الثاني عشر وتؤهل الطلاب للمرحلة الجامعية.', 
                'status' => 1,
            ],
        ];

        foreach ($grades as $grade) {
            Grade::firstOrCreate(['name' => $grade['name']], $grade);
        }
    }
}
