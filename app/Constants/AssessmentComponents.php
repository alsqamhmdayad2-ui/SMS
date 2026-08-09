<?php

namespace App\Constants;

class AssessmentComponents
{
    const ACTIVITY = 'activity';
    const ATTENDANCE = 'attendance';
    const ASSIGNMENTS = 'assignments';
    const MONTHLY_1 = 'monthly_1';
    const MIDTERM = 'midterm';
    const MONTHLY_2 = 'monthly_2';
    const FINAL = 'final';

    public static function getAll(): array
    {
        return [
            self::ACTIVITY => [
                'code' => self::ACTIVITY,
                'name' => 'النشاط',
                'name_ar' => 'النشاط',
            ],
            self::ATTENDANCE => [
                'code' => self::ATTENDANCE,
                'name' => 'الحضور',
                'name_ar' => 'الحضور',
            ],
            self::ASSIGNMENTS => [
                'code' => self::ASSIGNMENTS,
                'name' => 'الواجبات',
                'name_ar' => 'الواجبات',
            ],
            self::MONTHLY_1 => [
                'code' => self::MONTHLY_1,
                'name' => 'الاختبار الشهري الأول',
                'name_ar' => 'الاختبار الشهري الأول',
            ],
            self::MIDTERM => [
                'code' => self::MIDTERM,
                'name' => 'الاختبار النصفي',
                'name_ar' => 'الاختبار النصفي',
            ],
            self::MONTHLY_2 => [
                'code' => self::MONTHLY_2,
                'name' => 'الاختبار الشهري الثاني',
                'name_ar' => 'الاختبار الشهري الثاني',
            ],
            self::FINAL => [
                'code' => self::FINAL,
                'name' => 'الاختبار النهائي',
                'name_ar' => 'الاختبار النهائي',
            ],
        ];
    }
}
