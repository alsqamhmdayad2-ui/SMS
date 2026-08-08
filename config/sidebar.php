<?php

/**
 * =============================================
 * Sidebar Configuration — School Management System
 * Dynamic role-based navigation menu
 * =============================================
 *
 * Structure for each item:
 * [
 *   'title'      => 'عنوان العنصر',
 *   'icon'       => 'font-awesome-icon-name',   // بدون fa-
 *   'route'      => 'route.name',               // للروابط المباشرة
 *   'roles'      => ['admin','teacher',...],     // الأدوار المسموح لها (اختياري)
 *   'items'      => [ [...], [...] ],            // للقائمة الفرعية (submenu)
 * ]
 */

return [

    /* =============================================
       ADMIN SIDEBAR ITEMS
       ============================================= */

    // لوحة التحكم
    [
        'title'  => 'لوحة التحكم',
        'icon'   => 'tachometer-alt',
        'route'  => 'admin.dashboard',
        'roles'  => ['admin'],
    ],

    // إدارة المستخدمين
    [
        'title'  => 'إدارة المستخدمين',
        'icon'   => 'users',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'الطلاب',
                'route'  => 'admin.students.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'المعلمون',
                'route'  => 'admin.teachers.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'أولياء الأمور',
                'route'  => 'admin.parents.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // شؤون الطلاب
    [
        'title'  => 'شؤون الطلاب',
        'icon'   => 'user-graduate',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'تسجيل الطلاب',
                'route'  => 'admin.students.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'نقل الطلاب',
                'route'  => 'admin.transfers.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'ترقية الطلاب',
                'route'  => 'admin.promotions.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'إدارة الترقيات (التراجع)',
                'route'  => 'admin.promotions.management',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'توزيع الطلاب على الشعب',
                'route'  => 'admin.section-assignments.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // الهيكل الأكاديمي
    [
        'title'  => 'الهيكل الأكاديمي',
        'icon'   => 'sitemap',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'السنوات الدراسية',
                'route'  => 'admin.academic-years.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'الفصول الدراسية',
                'route'  => 'admin.semesters.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'المراحل الدراسية',
                'route'  => 'admin.grades.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'الصفوف',
                'route'  => 'admin.classes.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'الشعب',
                'route'  => 'admin.sections.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // المناهج والجداول
    [
        'title'  => 'المناهج والجداول',
        'icon'   => 'book-open',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'الخطة الدراسية',
                'route'  => 'admin.study-plans.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'المواد الدراسية',
                'route'  => 'admin.subjects.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'توزيع المعلمين',
                'route'  => 'admin.teacher-distributions.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'الجداول الدراسية',
                'route'  => 'admin.timetables.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // الاختبارات والتقييم
    [
        'title'  => 'الاختبارات والتقييم',
        'icon'   => 'clipboard-list',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'الاختبارات',
                'route'  => 'admin.exams.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'مكونات التقييم',
                'route'  => 'admin.assessment-components.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'سلم الدرجات',
                'route'  => 'admin.grade-scales.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'إدخال الدرجات',
                'route'  => 'admin.marks-entry.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // النتائج وكشوف الدرجات
    [
        'title'  => 'النتائج والكشوف',
        'icon'   => 'chart-bar',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'سجل الدرجات',
                'route'  => 'admin.gradebook.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'نتائج الطلاب',
                'route'  => 'admin.students.result.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'نشر النتائج',
                'route'  => 'admin.result-publications.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'كشوف الدرجات',
                'route'  => 'admin.report-cards.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // الحضور والغياب
    [
        'title'  => 'الحضور والغياب',
        'icon'   => 'calendar-check',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'جلسات الحضور',
                'route'  => 'admin.attendance-sessions.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'تقارير الحضور',
                'route'  => 'admin.attendance-reports.dashboard',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // التقارير
    [
        'title'  => 'التقارير',
        'icon'   => 'file-alt',
        'route'  => 'admin.reports.index',
        'roles'  => ['admin'],
    ],

    // إدارة حسابات النظام
    [
        'title'  => 'حسابات النظام',
        'icon'   => 'user-shield',
        'roles'  => ['admin'],
        'items'  => [
            [
                'name'   => 'المستخدمون والصلاحيات',
                'route'  => 'admin.users.index',
                'roles'  => ['admin'],
            ],
            [
                'name'   => 'إعدادات النظام',
                'route'  => 'admin.settings.index',
                'roles'  => ['admin'],
            ],
        ],
    ],

    // سلة المهملات / الأرشيف
    [
        'title'  => 'سلة المهملات',
        'icon'   => 'trash-restore',
        'route'  => 'admin.archive.index',
        'roles'  => ['admin'],
    ],

    /* =============================================
       TEACHER SIDEBAR ITEMS
       ============================================= */

    // لوحة التحكم
    [
        'title'  => 'لوحة التحكم',
        'icon'   => 'home',
        'route'  => 'teacher.dashboard',
        'roles'  => ['teacher'],
    ],

    // طلابي
    [
        'title'  => 'طلابي',
        'icon'   => 'user-graduate',
        'route'  => 'teacher.students',
        'roles'  => ['teacher'],
    ],

    // حضور الطلاب
    [
        'title'  => 'حضور الطلاب',
        'icon'   => 'clipboard-check',
        'route'  => 'teacher.attendance.today',
        'roles'  => ['teacher'],
    ],

    // رصد الدرجات
    [
        'title'  => 'رصد الدرجات',
        'icon'   => 'chart-bar',
        'route'  => 'teacher.grades',
        'roles'  => ['teacher'],
    ],

    // جدول الحصص
    [
        'title'  => 'جدول الحصص',
        'icon'   => 'calendar-alt',
        'route'  => 'teacher.schedule',
        'roles'  => ['teacher'],
    ],

    // إدارة الاختبارات
    [
        'title'  => 'إدارة الاختبارات',
        'icon'   => 'clipboard-list',
        'roles'  => ['teacher'],
        'items'  => [
            [
                'name'   => 'قائمة الاختبارات',
                'route'  => 'teacher.exams.index',
                'roles'  => ['teacher'],
            ],
            [
                'name'   => 'إنشاء اختبار',
                'route'  => 'teacher.exams.create',
                'roles'  => ['teacher'],
            ]
        ],
    ],

    // الملف الشخصي
    [
        'title'  => 'الملف الشخصي',
        'icon'   => 'user-circle',
        'route'  => 'teacher.profile',
        'roles'  => ['teacher'],
    ],

    /* =============================================
       STUDENT SIDEBAR ITEMS
       ============================================= */

    // لوحة الطالب
    [
        'title'  => 'لوحة التحكم',
        'icon'   => 'tachometer-alt',
        'route'  => 'dashboard',
        'roles'  => ['student'],
    ],

    /* =============================================
       PARENT SIDEBAR ITEMS
       ============================================= */

    // لوحة ولي الأمر
    [
        'title'  => 'لوحة التحكم',
        'icon'   => 'tachometer-alt',
        'route'  => 'dashboard',
        'roles'  => ['parent'],
    ],

];
