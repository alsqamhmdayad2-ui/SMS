<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeachersSeeder extends Seeder
{
    public function run(): void
    {
        // Subject IDs from DB:
        // 1=العربية, 2=رياضيات, 3=إنجليزي, 4=تربية إسلامية, 5=علوم وحياة,
        // 6=علوم عامة, 7=دراسات اجتماعية, 8=تربية وطنية, 9=تكنولوجيا, 10=فنية, 11=رياضية

        $teachers = [
            // ======= الدفعة الأولى (مضافة مسبقاً) =======
            [
                'first_name' => 'فاطمة', 'father_name' => 'محمد', 'grandfather_name' => 'علي',
                'family_name' => 'أبو شمالة', 'national_id' => '1001001001', 'phone' => '0501234567',
                'specialization' => 'تعليم أساسي', 'salary' => 5500, 'address' => 'غزة',
                'subjects' => [1, 2, 4, 5, 7, 8, 10, 11],
            ],
            [
                'first_name' => 'نورة', 'father_name' => 'أحمد', 'grandfather_name' => 'خالد',
                'family_name' => 'الشوبكي', 'national_id' => '1002002002', 'phone' => '0502345678',
                'specialization' => 'لغة عربية', 'salary' => 6000, 'address' => 'خان يونس',
                'subjects' => [1],
            ],
            [
                'first_name' => 'محمد', 'father_name' => 'سعد', 'grandfather_name' => 'عبدالله',
                'family_name' => 'حمدان', 'national_id' => '1003003003', 'phone' => '0503456789',
                'specialization' => 'رياضيات', 'salary' => 6200, 'address' => 'رفح',
                'subjects' => [2],
            ],
            [
                'first_name' => 'عبدالرحمن', 'father_name' => 'يوسف', 'grandfather_name' => 'إبراهيم',
                'family_name' => 'المصري', 'national_id' => '1004004004', 'phone' => '0504567890',
                'specialization' => 'لغة إنجليزية', 'salary' => 6500, 'address' => 'جباليا',
                'subjects' => [3],
            ],
            [
                'first_name' => 'عمر', 'father_name' => 'حمد', 'grandfather_name' => 'سليمان',
                'family_name' => 'برهوم', 'national_id' => '1005005005', 'phone' => '0505678901',
                'specialization' => 'تربية إسلامية', 'salary' => 5800, 'address' => 'بيت لاهيا',
                'subjects' => [4],
            ],
            [
                'first_name' => 'سارة', 'father_name' => 'فهد', 'grandfather_name' => 'ناصر',
                'family_name' => 'أبو عودة', 'national_id' => '1006006006', 'phone' => '0506789012',
                'specialization' => 'علوم', 'salary' => 6100, 'address' => 'دير البلح',
                'subjects' => [5, 6],
            ],
            [
                'first_name' => 'خالد', 'father_name' => 'عبدالعزيز', 'grandfather_name' => 'محمد',
                'family_name' => 'الكرد', 'national_id' => '1007007007', 'phone' => '0507890123',
                'specialization' => 'دراسات اجتماعية', 'salary' => 5700, 'address' => 'بيت حانون',
                'subjects' => [7, 8],
            ],
            [
                'first_name' => 'أنس', 'father_name' => 'عمر', 'grandfather_name' => 'علي',
                'family_name' => 'السوسي', 'national_id' => '1008008008', 'phone' => '0508901234',
                'specialization' => 'تكنولوجيا وحوسبة', 'salary' => 6300, 'address' => 'الشجاعية',
                'subjects' => [9],
            ],
            [
                'first_name' => 'رنا', 'father_name' => 'وليد', 'grandfather_name' => 'كريم',
                'family_name' => 'أبو صفية', 'national_id' => '1009009009', 'phone' => '0509012345',
                'specialization' => 'تربية فنية', 'salary' => 5600, 'address' => 'النصيرات',
                'subjects' => [10],
            ],
            [
                'first_name' => 'بدر', 'father_name' => 'ماجد', 'grandfather_name' => 'سالم',
                'family_name' => 'الدحدوح', 'national_id' => '1010010010', 'phone' => '0510123456',
                'specialization' => 'تربية رياضية', 'salary' => 5500, 'address' => 'المغازي',
                'subjects' => [11],
            ],

            // ======= الدفعة الثانية: لإكمال 3 معلمين لكل مادة =======

            // عربية (1) + إسلامية (4)
            [
                'first_name' => 'هناء', 'father_name' => 'جمال', 'grandfather_name' => 'حسن',
                'family_name' => 'أبو جازر', 'national_id' => '2001001001', 'phone' => '0521001001',
                'specialization' => 'لغة عربية وتربية إسلامية', 'salary' => 5900, 'address' => 'غزة',
                'subjects' => [1, 4],
            ],
            // عربية (1) ثالث
            [
                'first_name' => 'رغد', 'father_name' => 'سامر', 'grandfather_name' => 'فارس',
                'family_name' => 'أبو نعيم', 'national_id' => '2002002002', 'phone' => '0521002002',
                'specialization' => 'لغة عربية', 'salary' => 5800, 'address' => 'خان يونس',
                'subjects' => [1],
            ],
            // رياضيات (2) ثانٍ + علوم عامة (6)
            [
                'first_name' => 'يحيى', 'father_name' => 'نضال', 'grandfather_name' => 'توفيق',
                'family_name' => 'الطلاع', 'national_id' => '2003003003', 'phone' => '0521003003',
                'specialization' => 'رياضيات وعلوم', 'salary' => 6400, 'address' => 'رفح',
                'subjects' => [2, 6],
            ],
            // رياضيات (2) ثالث
            [
                'first_name' => 'صالح', 'father_name' => 'إياد', 'grandfather_name' => 'محمود',
                'family_name' => 'الزق', 'national_id' => '2004004004', 'phone' => '0521004004',
                'specialization' => 'رياضيات', 'salary' => 6100, 'address' => 'جباليا',
                'subjects' => [2],
            ],
            // إنجليزي (3) ثانٍ
            [
                'first_name' => 'أيمن', 'father_name' => 'رامي', 'grandfather_name' => 'زياد',
                'family_name' => 'حنية', 'national_id' => '2005005005', 'phone' => '0521005005',
                'specialization' => 'لغة إنجليزية', 'salary' => 6600, 'address' => 'دير البلح',
                'subjects' => [3],
            ],
            // إنجليزي (3) ثالث
            [
                'first_name' => 'لمى', 'father_name' => 'طارق', 'grandfather_name' => 'وليد',
                'family_name' => 'الرنتيسي', 'national_id' => '2006006006', 'phone' => '0521006006',
                'specialization' => 'لغة إنجليزية', 'salary' => 6400, 'address' => 'بيت لاهيا',
                'subjects' => [3],
            ],
            // إسلامية (4) ثالث
            [
                'first_name' => 'إسماعيل', 'father_name' => 'حسام', 'grandfather_name' => 'عدنان',
                'family_name' => 'أبو ظاهر', 'national_id' => '2007007007', 'phone' => '0521007007',
                'specialization' => 'تربية إسلامية', 'salary' => 5700, 'address' => 'بيت حانون',
                'subjects' => [4],
            ],
            // علوم وحياة (5) + علوم عامة (6) ثالث
            [
                'first_name' => 'إيمان', 'father_name' => 'باسم', 'grandfather_name' => 'رفيق',
                'family_name' => 'أبو طير', 'national_id' => '2008008008', 'phone' => '0521008008',
                'specialization' => 'علوم طبيعية', 'salary' => 6200, 'address' => 'الشجاعية',
                'subjects' => [5, 6],
            ],
            // علوم وحياة (5) رابع - لضمان 3 على الأقل
            [
                'first_name' => 'هبة', 'father_name' => 'وسام', 'grandfather_name' => 'أشرف',
                'family_name' => 'النمر', 'national_id' => '2009009009', 'phone' => '0521009009',
                'specialization' => 'علوم وأحياء', 'salary' => 6000, 'address' => 'النصيرات',
                'subjects' => [5],
            ],
            // دراسات (7) + وطنية (8) ثالث
            [
                'first_name' => 'زياد', 'father_name' => 'معتز', 'grandfather_name' => 'حسني',
                'family_name' => 'الحلو', 'national_id' => '2010010010', 'phone' => '0521010010',
                'specialization' => 'دراسات اجتماعية وتربية وطنية', 'salary' => 5800, 'address' => 'المغازي',
                'subjects' => [7, 8],
            ],
            // دراسات (7) رابع
            [
                'first_name' => 'غدير', 'father_name' => 'سلام', 'grandfather_name' => 'عاطف',
                'family_name' => 'عوض', 'national_id' => '2011011011', 'phone' => '0521011011',
                'specialization' => 'دراسات اجتماعية', 'salary' => 5600, 'address' => 'البريج',
                'subjects' => [7, 8],
            ],
            // تكنولوجيا (9) ثانٍ
            [
                'first_name' => 'وسام', 'father_name' => 'نزار', 'grandfather_name' => 'محمد',
                'family_name' => 'أبو حمدة', 'national_id' => '2012012012', 'phone' => '0521012012',
                'specialization' => 'تكنولوجيا المعلومات', 'salary' => 6200, 'address' => 'الزوايدة',
                'subjects' => [9],
            ],
            // تكنولوجيا (9) ثالث
            [
                'first_name' => 'ديما', 'father_name' => 'عصام', 'grandfather_name' => 'كمال',
                'family_name' => 'الهمص', 'national_id' => '2013013013', 'phone' => '0521013013',
                'specialization' => 'تكنولوجيا وحوسبة', 'salary' => 6100, 'address' => 'الشابورة',
                'subjects' => [9],
            ],
            // تربية فنية (10) ثانٍ
            [
                'first_name' => 'طارق', 'father_name' => 'أحمد', 'grandfather_name' => 'شريف',
                'family_name' => 'الأسطل', 'national_id' => '2014014014', 'phone' => '0521014014',
                'specialization' => 'تربية فنية', 'salary' => 5700, 'address' => 'خان يونس',
                'subjects' => [10],
            ],
            // تربية فنية (10) ثالث
            [
                'first_name' => 'ريم', 'father_name' => 'جهاد', 'grandfather_name' => 'فتحي',
                'family_name' => 'القدرة', 'national_id' => '2015015015', 'phone' => '0521015015',
                'specialization' => 'فنون تشكيلية', 'salary' => 5500, 'address' => 'دير البلح',
                'subjects' => [10],
            ],
            // تربية رياضية (11) ثانٍ
            [
                'first_name' => 'حسام', 'father_name' => 'ياسر', 'grandfather_name' => 'عزيز',
                'family_name' => 'أبو عمرة', 'national_id' => '2016016016', 'phone' => '0521016016',
                'specialization' => 'تربية رياضية', 'salary' => 5600, 'address' => 'جباليا',
                'subjects' => [11],
            ],
            // تربية رياضية (11) ثالث
            [
                'first_name' => 'رامي', 'father_name' => 'خليل', 'grandfather_name' => 'عبد',
                'family_name' => 'أبو ريدة', 'national_id' => '2017017017', 'phone' => '0521017017',
                'specialization' => 'تربية رياضية وبدنية', 'salary' => 5500, 'address' => 'رفح',
                'subjects' => [11],
            ],
        ];

        foreach ($teachers as $data) {
            $subjectIds = $data['subjects'];
            unset($data['subjects']);

            if (Teacher::where('national_id', $data['national_id'])->exists()) {
                $this->command->warn("⚠️  موجود مسبقاً: {$data['first_name']} {$data['family_name']}");
                continue;
            }

            $email = strtolower(Str::ascii($data['first_name'] . '.' . $data['family_name'])) . mt_rand(100, 999) . '@school.internal';
            $user = User::create([
                'name'        => $data['first_name'] . ' ' . $data['family_name'],
                'email'       => $email,
                'national_id' => $data['national_id'],
                'password'    => Hash::make($data['national_id']),
            ]);
            $user->assignRole('teacher');

            $data['user_id'] = $user->id;
            $teacher = Teacher::create($data);
            $teacher->qualifiedSubjects()->sync($subjectIds);

            $this->command->info("✅ {$teacher->first_name} {$teacher->family_name} — {$teacher->specialization}");
        }
    }
}
