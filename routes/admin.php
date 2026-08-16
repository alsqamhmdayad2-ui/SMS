<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\MarksEntryController;
use App\Http\Controllers\Admin\StudentResultController;
use App\Http\Controllers\Admin\ResultPublicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Student Transfers
        Route::get('transfers', [App\Http\Controllers\Admin\TransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers/internal', [App\Http\Controllers\Admin\TransferController::class, 'internalTransfer'])->name('transfers.internal');
        Route::post('transfers/external', [App\Http\Controllers\Admin\TransferController::class, 'externalTransfer'])->name('transfers.external');
        
        Route::post('students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer');
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('parents', ParentController::class);
        Route::resource('grades', GradeController::class);
        Route::resource('classes', SchoolClassController::class);
        Route::post('sections/{section}/assign-student', [SectionController::class, 'assignStudent'])->name('sections.assignStudent');
        Route::resource('sections', SectionController::class);

        // المناهج والجداول
        Route::resource('subjects', SubjectController::class);
        Route::post('subjects/{subject}/assign-teacher', [SubjectController::class, 'assignTeacher'])->name('subjects.assignTeacher');
        Route::post('subjects/{subject}/sync-classes', [SubjectController::class, 'syncClassSections'])->name('subjects.syncClasses');
        
        // Teacher Distribution System
        Route::get('teacher-distributions', [App\Http\Controllers\Admin\TeacherDistributionController::class, 'index'])->name('teacher-distributions.index');
        Route::post('teacher-distributions', [App\Http\Controllers\Admin\TeacherDistributionController::class, 'store'])->name('teacher-distributions.store');

        // Timetables
        Route::get('timetables', [TimetableController::class, 'index'])->name('timetables.index');
        Route::get('timetables/build', [TimetableController::class, 'build'])->name('timetables.build');
        Route::get('timetables/teacher/{teacher}', [TimetableController::class, 'teacherSchedule'])->name('timetables.teacher_schedule');
        Route::post('timetables/check-conflict', [TimetableController::class, 'checkConflict'])->name('timetables.check_conflict');
        Route::post('timetables/save', [TimetableController::class, 'save'])->name('timetables.save');
        Route::post('timetables/generate', [TimetableController::class, 'generate'])->name('timetables.generate');
        Route::post('timetables/regenerate', [TimetableController::class, 'regenerate'])->name('timetables.regenerate');
        
        // Study Plans
        Route::get('study-plans', [App\Http\Controllers\Admin\StudyPlanController::class, 'index'])->name('study-plans.index');
        Route::post('study-plans/save', [App\Http\Controllers\Admin\StudyPlanController::class, 'save'])->name('study-plans.save');
        Route::get('study-plans/by-subject', [App\Http\Controllers\Admin\StudyPlanController::class, 'bySubject'])->name('study-plans.by-subject');
        Route::post('study-plans/by-subject/save', [App\Http\Controllers\Admin\StudyPlanController::class, 'saveBySubject'])->name('study-plans.save-by-subject');
        
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('semesters', SemesterController::class);


        Route::get('exams/{exam}/print', [ExamController::class, 'print'])->name('exams.print');
        Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
        Route::post('exams/{exam}/unlock', [ExamController::class, 'unlock'])->name('exams.unlock');
        Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate');
        Route::resource('exams', ExamController::class);
        Route::post('exams/{exam}/questions', [App\Http\Controllers\Admin\QuestionController::class, 'store'])->name('questions.store');
        Route::put('exams/{exam}/questions/{question}', [App\Http\Controllers\Admin\QuestionController::class, 'update'])->name('questions.update');
        Route::post('exams/{exam}/questions/import', [App\Http\Controllers\Admin\QuestionController::class, 'import'])->name('questions.import');
        Route::post('exams/{exam}/questions/reorder', [App\Http\Controllers\Admin\QuestionController::class, 'reorder'])->name('questions.reorder');
        Route::get('exams/{exam}/questions/bank', [App\Http\Controllers\Admin\QuestionController::class, 'getBank'])->name('questions.bank');
        Route::post('exams/{exam}/questions/{question}/duplicate', [App\Http\Controllers\Admin\QuestionController::class, 'duplicate'])->name('questions.duplicate');
        Route::delete('exams/{exam}/questions/{question}', [App\Http\Controllers\Admin\QuestionController::class, 'destroy'])->name('questions.destroy');

        // Marks Entry Routes
        Route::get('marks-entry', [MarksEntryController::class, 'index'])->name('marks-entry.index');
        Route::post('marks-entry/save-mark', [MarksEntryController::class, 'saveMark'])->name('marks-entry.save-mark');
        Route::delete('marks-entry/delete-mark', [MarksEntryController::class, 'deleteMark'])->name('marks-entry.delete-mark');
        Route::post('marks-entry/save-all', [MarksEntryController::class, 'saveAll'])->name('marks-entry.save-all');
        Route::get('marks-entry/get-classes', [MarksEntryController::class, 'getClasses'])->name('marks-entry.get-classes');
        Route::get('marks-entry/get-sections', [MarksEntryController::class, 'getSections'])->name('marks-entry.get-sections');
        Route::get('marks-entry/get-subjects', [MarksEntryController::class, 'getSubjects'])->name('marks-entry.get-subjects');
        Route::get('marks-entry/get-exams', [MarksEntryController::class, 'getExams'])->name('marks-entry.get-exams');

        // الـ Gradebook القديم — إعادة توجيه للنظام الجديد
        Route::get('gradebook', fn() => redirect()->route('admin.marks.index'))->name('gradebook.index');
        Route::get('gradebook/student-breakdown', fn() => redirect()->route('admin.marks.index'))->name('gradebook.student-breakdown');

        // ── نظام الرصد الجديد بالبطاقات (Gradebook Wizard) ──
        Route::prefix('marks')->name('marks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'index'])->name('index');
            Route::get('/sections/{schoolClass}', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'sections'])->name('sections');
            Route::get('/subjects/{section}', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'subjects'])->name('subjects');
            Route::get('/enter/{section}/{subject}', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'enter'])->name('enter');
            Route::post('/save-all', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'saveAll'])->name('save-all');
            Route::post('/toggle-lock', [\App\Http\Controllers\Admin\GradebookWizardController::class, 'toggleLock'])->name('toggle-lock');
        });

        // Student Result Routes
        Route::get('students/{student}/result', [StudentResultController::class, 'show'])->name('students.result.show');
        Route::get('students/{student}/result/print', [StudentResultController::class, 'printResult'])->name('students.result.print');
        Route::get('student-results', [StudentResultController::class, 'index'])->name('students.result.index');

        // Result Publications
        Route::get('result-publications', [ResultPublicationController::class, 'index'])->name('result-publications.index');
        Route::post('result-publications', [ResultPublicationController::class, 'store'])->name('result-publications.store');
        Route::post('result-publications/{publication}/unpublish', [ResultPublicationController::class, 'unpublish'])->name('result-publications.unpublish');
        Route::delete('result-publications/{publication}', [ResultPublicationController::class, 'destroy'])->name('result-publications.destroy');

        // Reports
        Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/generate/{type}', [App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('reports.generate');

        // Official Report Cards — PDF Generation
        Route::get('report-cards', [App\Http\Controllers\Admin\ReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('report-cards/generate', [App\Http\Controllers\Admin\ReportCardController::class, 'generate'])->name('report-cards.generate');
        // Phase 8.6.3 — Admin Attendance Management
        Route::get('attendance-sessions', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'index'])->name('attendance-sessions.index');
        Route::get('attendance-sessions/create', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'create'])->name('attendance-sessions.create');
        Route::post('attendance-sessions/store', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'store'])->name('attendance-sessions.store');
        Route::get('attendance-sessions/{session}', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'show'])->name('attendance-sessions.show');
        Route::post('attendance-sessions/{session}/override', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'override'])->name('attendance-sessions.override');
        Route::post('attendance-sessions/{session}/unlock', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'unlock'])->name('attendance-sessions.unlock');
        Route::post('attendance-sessions/{session}/lock', [App\Http\Controllers\Admin\AttendanceAdminController::class, 'lock'])->name('attendance-sessions.lock');

        // Phase 8.6.4 — Attendance Reports
        Route::prefix('attendance-reports')->name('attendance-reports.')->group(function () {
            Route::get('dashboard', [App\Http\Controllers\Admin\AttendanceReportController::class, 'dashboard'])->name('dashboard');
            Route::get('student', [App\Http\Controllers\Admin\AttendanceReportController::class, 'studentReport'])->name('student');
            Route::get('section', [App\Http\Controllers\Admin\AttendanceReportController::class, 'sectionReport'])->name('section');
            Route::get('teacher', [App\Http\Controllers\Admin\AttendanceReportController::class, 'teacherReport'])->name('teacher');
            Route::get('daily', [App\Http\Controllers\Admin\AttendanceReportController::class, 'dailyReport'])->name('daily');
            Route::get('monthly', [App\Http\Controllers\Admin\AttendanceReportController::class, 'monthlyReport'])->name('monthly');
        });

        // System Management — Users & Settings
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        // Student Promotion System
        Route::get('promotions/management', [App\Http\Controllers\Admin\PromotionController::class, 'management'])->name('promotions.management');
        Route::delete('promotions/undo-all', [App\Http\Controllers\Admin\PromotionController::class, 'undoAll'])->name('promotions.undo-all');
        Route::delete('promotions/{enrollment}/undo', [App\Http\Controllers\Admin\PromotionController::class, 'undo'])->name('promotions.undo');
        Route::get('promotions', [App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotions.index');
        Route::post('promotions', [App\Http\Controllers\Admin\PromotionController::class, 'store'])->name('promotions.store');
        
        // Section Assignments (Phase 2 of Promotion)
        Route::get('section-assignments', [App\Http\Controllers\Admin\SectionAssignmentController::class, 'index'])->name('section-assignments.index');
        Route::post('section-assignments', [App\Http\Controllers\Admin\SectionAssignmentController::class, 'store'])->name('section-assignments.store');

        // Archive / Trash
        Route::get('archive', [App\Http\Controllers\Admin\ArchiveController::class, 'index'])->name('archive.index');
        Route::post('archive/{type}/{id}/restore', [App\Http\Controllers\Admin\ArchiveController::class, 'restore'])->name('archive.restore');
        Route::delete('archive/{type}/{id}/force-delete', [App\Http\Controllers\Admin\ArchiveController::class, 'forceDelete'])->name('archive.force-delete');
        Route::delete('archive/{type}/empty', [App\Http\Controllers\Admin\ArchiveController::class, 'emptyTrash'])->name('archive.empty');
    });

