<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * DemoDataSeeder
 * يحذف بيانات الطلاب والأولياء والمعلمين القديمة ويزرع
 * بيانات تجريبية بأسماء عائلات غزاوية حقيقية.
 *
 * الهيكل الأكاديمي المُعتمد:
 *  - المرحلة الابتدائية: الصف الأول → السادس
 *  - المرحلة الإعدادية: الصف السابع → التاسع
 *
 * كلمة المرور لكل مستخدم = رقم هويته الوطنية.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Student::truncate();
        ParentModel::truncate();
        Teacher::truncate();
        DB::table('student_enrollments')->truncate();
        // حذف المستخدمين غير الأدمن
        User::whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── الأسماء ──────────────────────────────────────────
        $families = [
            'أبو تيلخ', 'البشيتي', 'أبو حصيرة', 'أبو عودة', 'أبو سنينة',
            'أبو شمالة', 'أبو شرخ', 'أبو طه', 'أبو دقة', 'أبو معمر',
            'أبو هولي', 'أبو جزر', 'أبو حمد', 'أبو خوصة',
            'الشوا', 'النخالة', 'العشي', 'الكحلوت', 'الغول',
            'القدرة', 'المصري', 'الغصين', 'حمودة', 'حلس',
            'مرتجى', 'دغمش', 'البطش', 'حرارة', 'ماضي',
            'عابد', 'البيك', 'زقوت', 'صيام', 'برهوم',
            'النمنم', 'رضوان', 'قاسم', 'ساق الله', 'دلول',
            'مشتهى', 'السقا', 'عفانة', 'العطار', 'النجار',
            'السموني', 'الزعانين', 'الحلو', 'الكرد', 'حسنين',
        ];

        $boyNames  = ['محمد','أحمد','محمود','علي','عمر','خالد','سعيد','طارق','يوسف','إبراهيم','حسن','حسين','عبد الله','عبد الرحمن','مصطفى','ياسر','رامي','سامي','فادي','شادي'];
        $girlNames = ['فاطمة','عائشة','خديجة','مريم','سارة','نور','ياسمين','ليلى','منى','ندى','هند','رشا','سعاد','سمية','شيماء','صفاء','غدير','ميساء','هدى','يارا'];
        $neighborhoods = ['الرمال','الشجاعية','الشيخ رضوان','تل الهوا','النصر','التفاح','الزيتون'];

        // ── بناء خريطة المراحل/الصفوف/الشعب ──────────────────
        $academicYear = AcademicYear::where('status', true)->first()
                     ?? AcademicYear::create(['name'=>'2025/2026','start_date'=>'2025-08-01','end_date'=>'2026-06-01','status'=>true]);

        $classMap = []; // ['الصف الأول' => ['grade'=>..,'class'=>..,'sections'=>[..,..]]]

        // ابتدائية: الصف 1-6
        $primary = Grade::where('name', 'المرحلة الابتدائية')->first();
        if ($primary) {
            $primaryNames = ['الصف الأول','الصف الثاني','الصف الثالث','الصف الرابع','الصف الخامس','الصف السادس'];
            foreach ($primaryNames as $cn) {
                $class = SchoolClass::firstOrCreate(['name'=>$cn,'grade_id'=>$primary->id,'academic_year_id'=>$academicYear->id],['status'=>1]);
                $sA = Section::firstOrCreate(['name'=>'الشعبة أ','class_id'=>$class->id],['status'=>1]);
                $sB = Section::firstOrCreate(['name'=>'الشعبة ب','class_id'=>$class->id],['status'=>1]);
                $classMap[$cn] = ['grade'=>$primary,'class'=>$class,'sections'=>[$sA,$sB]];
            }
        }

        // إعدادية: الصف 7-9
        $prep = Grade::where('name', 'المرحلة الإعدادية')->first();
        if ($prep) {
            $prepNames = ['الصف السابع','الصف الثامن','الصف التاسع'];
            foreach ($prepNames as $cn) {
                $class = SchoolClass::firstOrCreate(['name'=>$cn,'grade_id'=>$prep->id,'academic_year_id'=>$academicYear->id],['status'=>1]);
                $sA = Section::firstOrCreate(['name'=>'الشعبة أ','class_id'=>$class->id],['status'=>1]);
                $sB = Section::firstOrCreate(['name'=>'الشعبة ب','class_id'=>$class->id],['status'=>1]);
                $classMap[$cn] = ['grade'=>$prep,'class'=>$class,'sections'=>[$sA,$sB]];
            }
        }

        $allSlots = array_values($classMap);
        if (empty($allSlots)) {
            $this->command->error('لا توجد مراحل دراسية! شغّل GradeSeeder أولاً.');
            return;
        }

        // ── مولّد أرقام هوية فريدة ────────────────────────────
        $usedIDs = [];
        $nextId  = function() use (&$usedIDs) {
            do { $id = '80' . rand(1000000,9999999); } while (in_array($id,$usedIDs));
            $usedIDs[] = $id;
            return $id;
        };

        // ── 1. معلمون ─────────────────────────────────────────
        $this->command->info('🏫  توليد المعلمين...');
        $subjects = ['لغة عربية','رياضيات','لغة إنجليزية','علوم','تربية إسلامية','اجتماعيات','تربية فنية'];
        for ($i = 0; $i < 18; $i++) {
            $isMale   = rand(0,1);
            $firstName = $isMale ? $boyNames[array_rand($boyNames)] : $girlNames[array_rand($girlNames)];
            $fName    = $boyNames[array_rand($boyNames)];
            $gName    = $boyNames[array_rand($boyNames)];
            $family   = $families[array_rand($families)];
            $fullName = "$firstName $fName $gName $family";
            $nid      = $nextId();

            $user = User::create([
                'name'        => $fullName,
                'national_id' => $nid,
                'email'       => 'teacher' . ($i+1) . '@school.local',
                'password'    => Hash::make($nid),
            ]);
            $user->assignRole('teacher');

            Teacher::create([
                'user_id'        => $user->id,
                'first_name'     => $firstName,
                'father_name'    => $fName,
                'grandfather_name'=> $gName,
                'family_name'    => $family,
                'national_id'    => $nid,
                'phone'          => '059' . rand(1000000,9999999),
                'specialization' => 'معلم ' . $subjects[array_rand($subjects)],
            ]);
        }

        // ── 2. أولياء الأمور والطلاب ─────────────────────────
        $this->command->info('👨‍👦  توليد أولياء الأمور والطلاب...');
        $totalStudents = 0;

        for ($i = 0; $i < 65; $i++) {
            $family    = $families[array_rand($families)];
            $fName     = $boyNames[array_rand($boyNames)];
            $gName     = $boyNames[array_rand($boyNames)];
            $ggName    = $boyNames[array_rand($boyNames)];
            $parentFull = "$fName $gName $ggName $family";
            $pNid      = $nextId();

            $pUser = User::create([
                'name'        => $parentFull,
                'national_id' => $pNid,
                'email'       => 'parent' . ($i+1) . '@school.local',
                'password'    => Hash::make($pNid),
            ]);
            $pUser->assignRole('parent');

            $parent = ParentModel::create([
                'user_id'          => $pUser->id,
                'guardian_type'    => 'Father',
                'full_name'        => $parentFull,
                'first_name'       => $fName,
                'father_name'      => $gName,
                'grandfather_name' => $ggName,
                'family_name'      => $family,
                'national_id'      => $pNid,
                'phone_1'          => '059' . rand(1000000,9999999),
                'address'          => 'غزة - ' . $neighborhoods[array_rand($neighborhoods)],
            ]);

            // أبناء: 1 إلى 3
            $numChildren = rand(1,3);
            for ($c = 0; $c < $numChildren; $c++) {
                $isMale    = rand(0,1);
                $sFirst    = $isMale ? $boyNames[array_rand($boyNames)] : $girlNames[array_rand($girlNames)];
                $sNid      = $nextId();
                $slot      = $allSlots[array_rand($allSlots)];
                $section   = $slot['sections'][array_rand($slot['sections'])];

                $sUser = User::create([
                    'name'        => "$sFirst $fName $gName $family",
                    'national_id' => $sNid,
                    'email'       => $sNid . '@student.local',
                    'password'    => Hash::make($sNid),
                ]);
                $sUser->assignRole('student');

                $student = Student::create([
                    'user_id'          => $sUser->id,
                    'parent_id'        => $parent->id,
                    'national_id'      => $sNid,
                    'student_number'   => 'ST26' . str_pad($totalStudents+1, 4, '0', STR_PAD_LEFT),
                    'first_name'       => $sFirst,
                    'father_name'      => $fName,
                    'grandfather_name' => $gName,
                    'family_name'      => $family,
                    'gender'           => $isMale ? 'Male' : 'Female',
                    'birth_date'       => Carbon::now()->subYears(rand(7,15))->subMonths(rand(1,11)),
                    'nationality'      => 'فلسطيني',
                    'religion'         => 'Muslim',
                    'governorate'      => 'غزة',
                    'city'             => 'غزة',
                    'grade_id'         => $slot['grade']->id,
                    'class_id'         => $slot['class']->id,
                    'section_id'       => $section->id,
                    'status'           => 'active',
                ]);

                DB::table('student_enrollments')->insert([
                    'student_id'        => $student->id,
                    'academic_year_id'  => $academicYear->id,
                    'grade_id'          => $slot['grade']->id,
                    'class_id'          => $slot['class']->id,
                    'section_id'        => $section->id,
                    'registration_date' => now()->toDateString(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $totalStudents++;
            }
        }

        $this->command->info("✅  تمّ بنجاح: {$totalStudents} طالب، 65 ولي أمر، 18 معلم.");
    }
}
