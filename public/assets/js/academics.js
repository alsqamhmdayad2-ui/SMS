/**
 * Academics Data Module (Teachers, Subjects, Schedules, Exams, Attendance)
 */

var ACADEMIC_STRUCTURE = {
  stages: [
     { id: 1, name: 'الابتدائية' },
     { id: 2, name: 'الإعدادية' }
  ],
  grades: [
     { id: 1, stage_id: 1, name: 'الصف الأول' },
     { id: 2, stage_id: 1, name: 'الصف الرابع' },
     { id: 3, stage_id: 2, name: 'الصف السابع' }
  ],
  sections: [
     { id: 1, grade_id: 1, name: 'أ' },
     { id: 2, grade_id: 1, name: 'ب' },
     { id: 3, grade_id: 2, name: 'أ' },
     { id: 4, grade_id: 2, name: 'ب' }
  ]
};

function getStages() { return ACADEMIC_STRUCTURE.stages; }
function getGradesByStage(stageId) { return ACADEMIC_STRUCTURE.grades.filter(g => g.stage_id === parseInt(stageId)); }
function getSectionsByGrade(gradeId) { return ACADEMIC_STRUCTURE.sections.filter(s => s.grade_id === parseInt(gradeId)); }
function getSectionById(id) { return ACADEMIC_STRUCTURE.sections.find(s => s.id === parseInt(id)); }
function getGradeById(id) { return ACADEMIC_STRUCTURE.grades.find(g => g.id === parseInt(id)); }

var SUBJECTS = [
  { id: 1, name: 'رياضيات', stage: 'الابتدائية', grade: 'الصف الأول', periods: 5 },
  { id: 2, name: 'علوم', stage: 'الابتدائية', grade: 'الصف الرابع', periods: 4 },
  { id: 3, name: 'لغة عربية', stage: 'الابتدائية', grade: 'جميع الصفوف', periods: 6 },
  { id: 4, name: 'لغة إنجليزية', stage: 'الابتدائية', grade: 'جميع الصفوف', periods: 5 }
];

var TEACHERS = [
  { id: 1, name: 'د. أحمد سعيد', email: 'ahmed.s@school.com', gender: 'ذكر', subject_id: 1, sections: ['أ', 'ب'], hireDate: '2020-09-01', phone: '0591234567' },
  { id: 2, name: 'أ. نورة حسن', email: 'noura@school.com', gender: 'أنثى', subject_id: 2, sections: ['أ'], hireDate: '2021-01-10', phone: '0597654321' },
  { id: 3, name: 'أ. محمد العلي', email: 'mali@school.com', gender: 'ذكر', subject_id: 3, sections: ['أ', 'ب', 'ج'], hireDate: '2019-09-15', phone: '0591112233' },
  { id: 4, name: 'أ. سمية أحمد', email: 'somaya@school.com', gender: 'أنثى', subject_id: 4, sections: ['ب', 'ج'], hireDate: '2022-02-01', phone: '0594445566' }
];

// =============== SUBJECTS LOGIC ===============

function getAllSubjects() {
  return SUBJECTS;
}

function getSubjectById(id) {
  return SUBJECTS.find(s => s.id === parseInt(id));
}

function addSubject(data) {
  if (!data.name || !data.stage) return { success: false, message: 'اسم المادة والمرحلة مطلوبان' };
  data.id = Date.now();
  SUBJECTS.push(data);
  return { success: true, message: 'تم إضافة المادة بنجاح' };
}

function deleteSubject(id) {
  // Check if subject is linked to any teacher
  const isLinked = TEACHERS.some(t => t.subject_id === parseInt(id));
  if (isLinked) {
    return { success: false, message: 'لا يمكن حذف المادة لأنها مرتبطة بمعلمين' };
  }
  SUBJECTS = SUBJECTS.filter(s => s.id !== parseInt(id));
  return { success: true, message: 'تم الحذف بنجاح' };
}

// =============== TEACHERS LOGIC ===============

function getAllTeachers() {
  return TEACHERS;
}

function getTeacherById(id) {
  return TEACHERS.find(t => t.id === parseInt(id));
}

function addTeacher(data) {
  if (!data.name?.trim())
    return { success: false, message: 'اسم المعلم مطلوب' };
  if (!data.email?.trim())
    return { success: false, message: 'البريد الإلكتروني مطلوب' };

  // التحقق من تكرار البريد (Fix #6)
  var dup = TEACHERS.find(function(t) {
    return t.email.toLowerCase() === data.email.toLowerCase().trim();
  });
  if (dup)
    return { success: false, message: 'البريد مستخدم بالفعل: ' + dup.name };

  data.id       = Date.now();
  data.name     = data.name.trim();
  data.email    = data.email.trim().toLowerCase();
  data.hireDate = data.hireDate || new Date().toISOString().split('T')[0];
  TEACHERS.push(data);
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم إضافة المعلم بنجاح', id: data.id };
}

function updateTeacher(id, data) {
  const t = getTeacherById(id);
  if (!t) return { success: false, message: 'المعلم غير موجود' };
  
  t.name = data.name || t.name;
  t.email = data.email || t.email;
  t.phone = data.phone || t.phone;
  t.gender = data.gender || t.gender;
  t.subject_id = data.subject_id || t.subject_id;
  t.sections = data.sections || t.sections;
  
  return { success: true, message: 'تم تحديث بيانات المعلم' };
}

function deleteTeacher(id) {
  // Can add constraints later (e.g. if teacher has schedule)
  TEACHERS = TEACHERS.filter(t => t.id !== parseInt(id));
  return { success: true, message: 'تم إزالة المعلم من النظام' };
}

// =============== SCHEDULES LOGIC ===============
/**
 * Schedule structure:
 * {
 *   id: 1,
 *   section_id: 101, // Linked to a specific section
 *   day: 'الأحد',
 *   periods: [
 *     { subject_id: 1, teacher_id: 1, start_time: '08:00', end_time: '08:45' },
 *     ...
 *   ]
 * }
 */
var SCHEDULES = [
  { 
    id: 1, 
    section_id: 1, // Example ID for 'الصف الأول - أ'
    day: 'الأحد', 
    periods: [
      { subject_id: 1, teacher_id: 1, start_time: '08:00', end_time: '08:45' },
      { subject_id: 3, teacher_id: 3, start_time: '08:45', end_time: '09:30' }
    ]
  }
];

function getSchedulesBySection(sectionId) {
  return SCHEDULES.filter(s => s.section_id === parseInt(sectionId));
}

function checkScheduleConflict(teacherId, day, startTime, endTime, sectionId) {
  // Check if teacher is busy in another section at the same time
  const conflict = SCHEDULES.find(s => {
    if (s.day !== day) return false;
    return s.periods.some(p => {
      // If same teacher and overlap time
      if (p.teacher_id === parseInt(teacherId) && s.section_id !== parseInt(sectionId)) {
        return (startTime < p.end_time && endTime > p.start_time);
      }
      return false;
    });
  });
  
  if (conflict) {
    return { hasConflict: true, message: `تعارض: المعلم مشغول في شعبة أخرى في هذا الوقت.` };
  }
  return { hasConflict: false };
}

function saveSchedule(sectionId, day, periods) {
  // Clear existing for this day/section and save new
  SCHEDULES = SCHEDULES.filter(s => !(s.section_id === parseInt(sectionId) && s.day === day));
  
  const newEntry = {
    id: Date.now(),
    section_id: parseInt(sectionId),
    day: day,
    periods: periods
  };
  
  SCHEDULES.push(newEntry);
  return { success: true, message: 'تم حفظ الجدول لهذا اليوم بنجاح' };
}

function cloneSchedule(fromSectionId, toSectionId) {
  const source = getSchedulesBySection(fromSectionId);
  if (source.length === 0) return { success: false, message: 'لا يوجد جدول لنسخه من هذه الشعبة' };
  
  // Clear target
  SCHEDULES = SCHEDULES.filter(s => s.section_id !== parseInt(toSectionId));
  
  // Clone
  source.forEach(s => {
    SCHEDULES.push({
      ...s,
      id: Date.now() + Math.random(),
      section_id: parseInt(toSectionId)
    });
  });
  
  return { success: true, message: 'تم نسخ الجدول بنجاح' };
}

// =============== ATTENDANCE LOGIC ===============
var ATTENDANCE_LOGS = [
  { student_name: 'محمد أحمد', date: '2023-10-10', status: 'حاضر', note: '' },
  { student_name: 'علي حسن', date: '2023-10-10', status: 'غائب', note: 'عذر طبي' }
];

function getAttendanceByDateAndSection(date, className, sectionName) {
  // Mock logic - in reality you filter by section students
  return ATTENDANCE_LOGS.filter(a => a.date === date);
}

function saveAttendance(records) {
  // records: array of {name, status, note}
  // Simplified mock save
  records.forEach(r => {
    ATTENDANCE_LOGS.push({
      student_name: r.name,
      date: r.date || new Date().toISOString().split('T')[0],
      status: r.status,
      note: r.note
    });
  });
  return { success: true, message: 'تم حفظ سجل الحضور' };
}

// =============== EXAMS LOGIC ===============
var EXAMS = [
  { id: 1, title: 'اختبار نصف الفصل - رياضيات', subject_id: 1, class_name: 'الصف الأول', date: '2023-11-15', duration: 45, max_mark: 20 },
  { id: 2, title: 'امتحان نهائي - لغة إنجليزية', subject_id: 4, class_name: 'جميع الصفوف', date: '2023-12-25', duration: 90, max_mark: 100 }
];

function getAllExams() {
  return EXAMS;
}

function addExam(data) {
  if(!data.title || !data.subject_id) return { success: false, message: 'عنوان الاختبار والمادة مطلوبان' };
  data.id = Date.now();
  EXAMS.push(data);
  return { success: true, message: 'تم إنشاء الاختبار بنجاح' };
}

function deleteExam(id) {
  EXAMS = EXAMS.filter(e => e.id !== parseInt(id));
  return { success: true, message: 'تم حذف الاختبار' };
}


