<?php

namespace App\Enums;

enum ReportType: string
{
    case Student = 'student';
    case Section = 'section';
    case Subject = 'subject';
    case Teacher = 'teacher';
    case Grade = 'grade';
    case Annual = 'annual';
    case FailedStudents = 'failed_students';
    case HonorStudents = 'honor_students';
    case GPA = 'gpa';
    case PassRate = 'pass_rate';
    case Statistics = 'statistics';

    public function label(): string
    {
        return match($this) {
            self::Student       => 'Student Report Card',
            self::Section       => 'Section Results',
            self::Subject       => 'Subject Results',
            self::Teacher       => 'Teacher Results',
            self::Grade         => 'Grade Results',
            self::Annual        => 'التقرير السنوي',
            self::FailedStudents => 'Failed Students',
            self::HonorStudents  => 'Honor Students',
            self::GPA           => 'GPA Ranking',
            self::PassRate      => 'Pass Rate Statistics',
            self::Statistics    => 'General Statistics',
        };
    }
}
