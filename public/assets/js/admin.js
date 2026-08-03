// Admin System JavaScript Logic
// This file handles the data and functions for managing students and promotions.
// We use simple JS arrays to act as a database.
// 
// Academic Structure:
// - Elementary Stage: Grades 1-6 (stage_id: '1')
// - Preparatory Stage: Grades 7-9 (stage_id: '2')
//
// Auto Student ID Format: Year + Stage Code + Sequence Number (e.g. 20261001)
// Student Statuses: 'نشط' (Active), 'منقول' (Transferred), 'أنهى المرحلة' (Finished)

// Mock data for school stages
var STAGES = [
  { stage_id: '1', name: 'المرحلة الابتدائية', shortName: 'الابتدائية', code: '1' },
  { stage_id: '2', name: 'المرحلة الإعدادية',  shortName: 'الإعدادية',  code: '2' }
];

// Mock data for grades
var GRADES = [
  { grade_id: '1', stage_id: '1', name: 'الصف الأول',   gradeNum: 1 },
  { grade_id: '2', stage_id: '1', name: 'الصف الثاني',  gradeNum: 2 },
  { grade_id: '3', stage_id: '1', name: 'الصف الثالث',  gradeNum: 3 },
  { grade_id: '4', stage_id: '1', name: 'الصف الرابع',  gradeNum: 4 },
  { grade_id: '5', stage_id: '1', name: 'الصف الخامس',  gradeNum: 5 },
  { grade_id: '6', stage_id: '1', name: 'الصف السادس',  gradeNum: 6 },
  { grade_id: '7', stage_id: '2', name: 'الصف السابع',  gradeNum: 7 },
  { grade_id: '8', stage_id: '2', name: 'الصف الثامن',  gradeNum: 8 },
  { grade_id: '9', stage_id: '2', name: 'الصف التاسع',  gradeNum: 9 }
];

// Mock data for sections
var SECTIONS = [
  { section_id: 's1',  grade_id: '1', stage_id: '1', name: 'أ' },
  { section_id: 's2',  grade_id: '1', stage_id: '1', name: 'ب' },
  { section_id: 's3',  grade_id: '1', stage_id: '1', name: 'ج' },
  { section_id: 's4',  grade_id: '2', stage_id: '1', name: 'أ' },
  { section_id: 's5',  grade_id: '2', stage_id: '1', name: 'ب' },
  { section_id: 's6',  grade_id: '2', stage_id: '1', name: 'ج' },
  { section_id: 's7',  grade_id: '3', stage_id: '1', name: 'أ' },
  { section_id: 's8',  grade_id: '3', stage_id: '1', name: 'ب' },
  { section_id: 's9',  grade_id: '3', stage_id: '1', name: 'ج' },
  { section_id: 's10', grade_id: '4', stage_id: '1', name: 'أ' },
  { section_id: 's11', grade_id: '4', stage_id: '1', name: 'ب' },
  { section_id: 's12', grade_id: '4', stage_id: '1', name: 'ج' },
  { section_id: 's13', grade_id: '5', stage_id: '1', name: 'أ' },
  { section_id: 's14', grade_id: '5', stage_id: '1', name: 'ب' },
  { section_id: 's15', grade_id: '5', stage_id: '1', name: 'ج' },
  { section_id: 's16', grade_id: '6', stage_id: '1', name: 'أ' },
  { section_id: 's17', grade_id: '6', stage_id: '1', name: 'ب' },
  { section_id: 's18', grade_id: '6', stage_id: '1', name: 'ج' },
  { section_id: 's19', grade_id: '7', stage_id: '2', name: 'أ' },
  { section_id: 's20', grade_id: '7', stage_id: '2', name: 'ب' },
  { section_id: 's21', grade_id: '7', stage_id: '2', name: 'ج' },
  { section_id: 's22', grade_id: '8', stage_id: '2', name: 'أ' },
  { section_id: 's23', grade_id: '8', stage_id: '2', name: 'ب' },
  { section_id: 's24', grade_id: '8', stage_id: '2', name: 'ج' },
  { section_id: 's25', grade_id: '9', stage_id: '2', name: 'أ' },
  { section_id: 's26', grade_id: '9', stage_id: '2', name: 'ب' },
  { section_id: 's27', grade_id: '9', stage_id: '2', name: 'ج' }
];

// The STUDENTS array acts as our database table. Contains all student records.
var STUDENTS = [
  { id: 1,  student_id: '20261001', name: 'محمد أحمد العلي',      email: 'mohamed.ali@school.com',      gender: 'ذكر',  stage_id: '1', grade_id: '1', section_id: 's1', status: 'نشط', registration_year: 2026 },
  { id: 2,  student_id: '20261002', name: 'فاطمة خالد الحسن',     email: 'fatma.hasan@school.com',      gender: 'أنثى', stage_id: '1', grade_id: '1', section_id: 's2', status: 'نشط', registration_year: 2026 },
  { id: 3,  student_id: '20261003', name: 'عمر يوسف سالم',        email: 'omar.salem@school.com',       gender: 'ذكر',  stage_id: '1', grade_id: '1', section_id: 's1', status: 'نشط', registration_year: 2026 },
  { id: 4,  student_id: '20261004', name: 'نورة سعيد المنصور',     email: 'noura.mansour@school.com',    gender: 'أنثى', stage_id: '1', grade_id: '2', section_id: 's4', status: 'نشط', registration_year: 2026 },
  { id: 5,  student_id: '20261005', name: 'أحمد فهد الراشد',       email: 'ahmed.rashed@school.com',     gender: 'ذكر',  stage_id: '1', grade_id: '2', section_id: 's5', status: 'نشط', registration_year: 2026 },
  { id: 6,  student_id: '20261006', name: 'سارة عبدالله النعيم',    email: 'sara.naeem@school.com',       gender: 'أنثى', stage_id: '1', grade_id: '3', section_id: 's7', status: 'نشط', registration_year: 2026 },
  { id: 7,  student_id: '20261007', name: 'خالد محمد العتيبي',     email: 'khaled.otaibi@school.com',    gender: 'ذكر',  stage_id: '1', grade_id: '3', section_id: 's8', status: 'نشط', registration_year: 2026 },
  { id: 8,  student_id: '20261008', name: 'ريم إبراهيم الشهري',    email: 'reem.shahri@school.com',      gender: 'أنثى', stage_id: '1', grade_id: '4', section_id: 's10', status: 'نشط', registration_year: 2026 },
  { id: 9,  student_id: '20261009', name: 'يوسف عبدالرحمن الحربي', email: 'youssef.harbi@school.com',    gender: 'ذكر',  stage_id: '1', grade_id: '4', section_id: 's11', status: 'نشط', registration_year: 2026 },
  { id: 10, student_id: '20261010', name: 'لمى سلطان القحطاني',    email: 'lama.qahtani@school.com',     gender: 'أنثى', stage_id: '1', grade_id: '5', section_id: 's13', status: 'نشط', registration_year: 2026 },
  { id: 11, student_id: '20261011', name: 'عبدالعزيز ناصر الدوسري', email: 'abdulaziz.dosari@school.com', gender: 'ذكر',  stage_id: '1', grade_id: '5', section_id: 's14', status: 'نشط', registration_year: 2026 },
  { id: 12, student_id: '20261012', name: 'هيفاء عادل المطيري',    email: 'haifa.mutairi@school.com',    gender: 'أنثى', stage_id: '1', grade_id: '6', section_id: 's16', status: 'نشط', registration_year: 2026 },
  { id: 13, student_id: '20261013', name: 'تركي ماجد الغامدي',     email: 'turki.ghamdi@school.com',     gender: 'ذكر',  stage_id: '1', grade_id: '6', section_id: 's17', status: 'نشط', registration_year: 2026 },
  { id: 14, student_id: '20262001', name: 'عبدالله مشعل الزهراني',  email: 'abdullah.zahrani@school.com', gender: 'ذكر',  stage_id: '2', grade_id: '7', section_id: 's19', status: 'نشط', registration_year: 2026 },
  { id: 15, student_id: '20262002', name: 'مريم حسين البلوشي',     email: 'maryam.balushi@school.com',   gender: 'أنثى', stage_id: '2', grade_id: '7', section_id: 's20', status: 'نشط', registration_year: 2026 },
  { id: 16, student_id: '20262003', name: 'سلمان أحمد العمري',     email: 'salman.amri@school.com',      gender: 'ذكر',  stage_id: '2', grade_id: '7', section_id: 's19', status: 'نشط', registration_year: 2026 },
  { id: 17, student_id: '20262004', name: 'دانة فيصل الجهني',      email: 'dana.jahni@school.com',       gender: 'أنثى', stage_id: '2', grade_id: '8', section_id: 's22', status: 'نشط', registration_year: 2026 },
  { id: 18, student_id: '20262005', name: 'فيصل سعود الشمري',      email: 'faisal.shamri@school.com',    gender: 'ذكر',  stage_id: '2', grade_id: '8', section_id: 's23', status: 'نشط', registration_year: 2026 },
  { id: 19, student_id: '20262006', name: 'لينا محمد الحمد',       email: 'lina.hamad@school.com',       gender: 'أنثى', stage_id: '2', grade_id: '9', section_id: 's25', status: 'نشط', registration_year: 2026 },
  { id: 20, student_id: '20262007', name: 'راشد سالم المري',       email: 'rashed.murri@school.com',     gender: 'ذكر',  stage_id: '2', grade_id: '9', section_id: 's26', status: 'نشط', registration_year: 2026 }
];

var PROMOTION_LOG = [];

// Data access helpers
function getGradesByStage(stageId) {
  return GRADES.filter(function (g) { return g.stage_id === stageId; });
}

function getSectionsByGrade(stageId, gradeId) {
  return SECTIONS.filter(function (s) {
    return s.stage_id === stageId && s.grade_id === gradeId;
  });
}

function getStageName(stageId) {
  var s = STAGES.find(function (x) { return x.stage_id === stageId; });
  return s ? s.shortName : '';
}

function getStageFullName(stageId) {
  var s = STAGES.find(function (x) { return x.stage_id === stageId; });
  return s ? s.name : '';
}

function getGradeName(gradeId) {
  var g = GRADES.find(function (x) { return x.grade_id === gradeId; });
  return g ? g.name : '';
}

function getSectionName(sectionId) {
  var s = SECTIONS.find(function (x) { return x.section_id === sectionId; });
  return s ? s.name : '';
}

function getStageCode(stageId) {
  var s = STAGES.find(function (x) { return x.stage_id === stageId; });
  return s ? s.code : '0';
}

// Automatically generate a unique ID for a new student based on their year and stage.
function generateStudentId(stageId, year) {
  var stageCode = getStageCode(stageId);
  var prefix = String(year) + stageCode;

  // Find the highest sequence for this prefix
  var maxSeq = 0;
  STUDENTS.forEach(function (s) {
    if (s.student_id && s.student_id.indexOf(prefix) === 0) {
      var seq = parseInt(s.student_id.substring(prefix.length), 10);
      if (seq > maxSeq) maxSeq = seq;
    }
  });

  var nextSeq = maxSeq + 1;
  return prefix + String(nextSeq).padStart(3, '0');
}

// Function to validate inputs and add a new student object to our STUDENTS array.
function addStudent(data) {
  if (!data.name || !data.name.trim()) {
    return { success: false, message: 'يرجى إدخال اسم الطالب' };
  }
  if (!data.stage_id) {
    return { success: false, message: 'يرجى اختيار المرحلة الدراسية' };
  }
  if (!data.grade_id) {
    return { success: false, message: 'يرجى اختيار الصف' };
  }
  if (!data.section_id) {
    return { success: false, message: 'يرجى اختيار الشعبة' };
  }

  // Check for duplicate name
  var duplicate = STUDENTS.find(function (s) {
    return s.name.trim() === data.name.trim() && s.status === 'نشط';
  });
  if (duplicate) {
    return { success: false, message: 'هذا الطالب موجود بالفعل: ' + duplicate.student_id };
  }

  // Generate student ID
  var regYear = data.registration_year || new Date().getFullYear();
  var studentId = generateStudentId(data.stage_id, regYear);

  // Create new student object
  var newStudent = {
    id: STUDENTS.length > 0 ? Math.max.apply(null, STUDENTS.map(function(s){ return s.id; })) + 1 : 1,
    student_id: studentId,
    name: data.name.trim(),
    email: data.email || '',
    gender: data.gender || '',
    stage_id: data.stage_id,
    grade_id: data.grade_id,
    section_id: data.section_id,
    status: 'نشط',
    registration_year: regYear
  };

  STUDENTS.push(newStudent);

  return { success: true, student: newStudent, message: 'تم إضافة الطالب بنجاح — رقم الطالب: ' + studentId };
}


// Handle updating student information
function updateStudent(studentInternalId, updates) {
  var student = STUDENTS.find(function (s) { return s.id === studentInternalId; });
  if (!student) return { success: false, message: 'الطالب غير موجود' };

  // If stage changed, regenerate student_id
  if (updates.stage_id && updates.stage_id !== student.stage_id) {
    var year = updates.registration_year || student.registration_year;
    student.student_id = generateStudentId(updates.stage_id, year);
  }

  // Apply updates
  for (var key in updates) {
    if (updates.hasOwnProperty(key) && key !== 'id' && key !== 'student_id') {
      student[key] = updates[key];
    }
  }

  return { success: true, student: student, message: 'تم تحديث بيانات الطالب بنجاح' };
}

// Handle deleting a student
function deleteStudent(studentInternalId) {
  var index = STUDENTS.findIndex(function (s) { return s.id === studentInternalId; });
  if (index === -1) return { success: false, message: 'الطالب غير موجود' };

  var removed = STUDENTS.splice(index, 1)[0];
  return { success: true, message: 'تم حذف الطالب: ' + removed.name };
}


// Manage moving a student out of the school
function transferStudentOut(studentInternalId) {
  var student = STUDENTS.find(function (s) { return s.id === studentInternalId; });
  if (!student) return { success: false, message: 'الطالب غير موجود' };
  if (student.status === 'منقول') return { success: false, message: 'الطالب منقول بالفعل' };

  student.status = 'منقول';
  return { success: true, student: student, message: 'تم نقل الطالب: ' + student.name };
}

// Reactivate a transferred or graduated student
function reactivateStudent(studentInternalId, newPlacement) {
  var student = STUDENTS.find(function (s) { return s.id === studentInternalId; });
  if (!student) return { success: false, message: 'الطالب غير موجود' };

  student.status = 'نشط';
  if (newPlacement) {
    if (newPlacement.grade_id) student.grade_id = newPlacement.grade_id;
    if (newPlacement.section_id) student.section_id = newPlacement.section_id;
    if (newPlacement.stage_id) student.stage_id = newPlacement.stage_id;
  }

  return { success: true, student: student, message: 'تم إعادة تنشيط الطالب: ' + student.name };
}


// Figure out which grade a student goes to when promoted
function getPromotionTarget(gradeId) {
  var grade = GRADES.find(function (g) { return g.grade_id === gradeId; });
  if (!grade) return null;

  var num = grade.gradeNum;

  // Grade 9 = end of basic education
  if (num === 9) {
    return { newGradeId: null, newStageId: null, isGraduation: true };
  }

  // Grade 6 → Grade 7 (Elementary → Preparatory transition)
  if (num === 6) {
    return { newGradeId: '7', newStageId: '2', isGraduation: false };
  }

  // Normal increment within same stage
  var nextNum = num + 1;
  var nextGrade = GRADES.find(function (g) { return g.gradeNum === nextNum; });
  if (!nextGrade) return null;

  return { newGradeId: nextGrade.grade_id, newStageId: nextGrade.stage_id, isGraduation: false };
}

// Promote a single student
function promoteStudent(studentInternalId, newSectionId) {
  var student = STUDENTS.find(function (s) { return s.id === studentInternalId; });
  if (!student) return { success: false, message: 'الطالب غير موجود' };
  if (student.status !== 'نشط') return { success: false, message: 'لا يمكن ترقية طالب غير نشط' };

  var target = getPromotionTarget(student.grade_id);
  if (!target) return { success: false, message: 'خطأ في تحديد الصف التالي' };

  var oldGradeId = student.grade_id;
  var oldStageId = student.stage_id;
  var oldSectionId = student.section_id;

  if (target.isGraduation) {
    // Grade 9 → graduation
    student.status = 'أنهى المرحلة';
    PROMOTION_LOG.push({
      student_id: student.student_id,
      student_name: student.name,
      from_stage: oldStageId,
      from_grade: oldGradeId,
      to_stage: '-',
      to_grade: '-',
      type: 'تخرج',
      date: new Date().toISOString().split('T')[0]
    });
    return { success: true, student: student, message: student.name + ' — أنهى المرحلة الأساسية', isGraduation: true };
  }

  // Normal promotion or stage transition
  student.grade_id = target.newGradeId;
  student.stage_id = target.newStageId;

  // Handle section assignment
  if (newSectionId) {
    student.section_id = newSectionId;
  } else {
    // Try to find matching section letter in new grade
    var oldSectionName = getSectionName(oldSectionId);
    var newSections = getSectionsByGrade(target.newStageId, target.newGradeId);
    var matchingSection = newSections.find(function (s) { return s.name === oldSectionName; });
    student.section_id = matchingSection ? matchingSection.section_id : (newSections.length > 0 ? newSections[0].section_id : oldSectionId);
  }

  PROMOTION_LOG.push({
    student_id: student.student_id,
    student_name: student.name,
    from_stage: oldStageId,
    from_grade: oldGradeId,
    to_stage: target.newStageId,
    to_grade: target.newGradeId,
    type: (oldStageId !== target.newStageId) ? 'انتقال مرحلة' : 'ترقية',
    date: new Date().toISOString().split('T')[0]
  });

  return {
    success: true,
    student: student,
    message: student.name + ' — تمت الترقية إلى ' + getGradeName(target.newGradeId),
    isGraduation: false
  };
}

// Promote all active students in a given grade
function promoteGrade(gradeId, keepSection) {
  var studentsInGrade = STUDENTS.filter(function (s) {
    return s.grade_id === gradeId && s.status === 'نشط';
  });

  if (studentsInGrade.length === 0) {
    return { success: false, message: 'لا يوجد طلاب نشطين في هذا الصف', promoted: 0, graduated: 0 };
  }

  var promotedCount = 0;
  var graduatedCount = 0;
  var errors = [];

  studentsInGrade.forEach(function (student) {
    var result = promoteStudent(student.id, keepSection ? undefined : null);
    if (result.success) {
      if (result.isGraduation) {
        graduatedCount++;
      } else {
        promotedCount++;
      }
    } else {
      errors.push(result.message);
    }
  });

  var msg = 'تم ترقية ' + promotedCount + ' طالب';
  if (graduatedCount > 0) msg += ' وأنهى ' + graduatedCount + ' طالب المرحلة الأساسية';

  return { success: true, message: msg, promoted: promotedCount, graduated: graduatedCount, errors: errors };
}


// --- 8. SECTIONS MANAGEMENT ---

function renderSections(stageId, gradeId, container, titleEl) {
  var stageName = getStageName(stageId);
  var gradeName = getGradeName(gradeId);
  var sections = getSectionsByGrade(stageId, gradeId);

  if (titleEl) titleEl.textContent = 'إدارة شُعب ' + gradeName + ' — ' + stageName;

  var html = '<div class="table-container mt-3">';
  html += '  <table class="data-table" id="sectionsTable" data-sort-asc="true">';
  html += '    <thead>';
  html += '      <tr>';
  html += '        <th onclick="sortTable(\'sectionsTable\', 0, \'number\')" style="cursor:pointer;"># <i class="fas fa-sort-numeric-down"></i></th>';
  html += '        <th onclick="sortTable(\'sectionsTable\', 1, \'string\')" style="cursor:pointer;">الشعبة <i class="fas fa-sort-alpha-down"></i></th>';
  html += '        <th onclick="sortTable(\'sectionsTable\', 2, \'string\')" style="cursor:pointer;">توزيع الطلاب <i class="fas fa-sort"></i></th>';
  html += '        <th>الإجراءات</th>';
  html += '      </tr>';
  html += '    </thead>';
  html += '    <tbody>';

  if (sections.length === 0) {
    html += '<tr><td colspan="4" class="text-center p-4 color-muted">لا توجد شُعب مضافة لهذا الصف</td></tr>';
  } else {
    sections.forEach(function (sec, index) {
      var secStudents = STUDENTS.filter(function(s){ return s.section_id === sec.section_id; });
      var activeCount = secStudents.filter(function(s){ return s.status === 'نشط'; }).length;
      var totalCount = secStudents.length;
      
      // Calculate progress percentages
      var activePerc = totalCount > 0 ? Math.round((activeCount / totalCount) * 100) : 0;
      var transCount = secStudents.filter(function(s){ return s.status === 'منقول'; }).length;
      var transPerc = totalCount > 0 ? Math.round((transCount / totalCount) * 100) : 0;
      var gradCount = secStudents.filter(function(s){ return s.status === 'أنهى المرحلة'; }).length;
      var gradPerc = totalCount > 0 ? (100 - activePerc - transPerc) : 0; // Remainder to total 100%

      html += '<tr>';
      html += '  <td>' + (index + 1) + '</td>';
      html += '  <td><strong>شعبة ' + sec.name + '</strong></td>';
      html += '  <td>';
      html += '    <div class="d-flex justify-content-between align-items-center mb-1">';
      html += '      <span class="badge badge-success">' + activeCount + ' نشط</span>';
      html += '      <span style="font-size:0.75rem; color:var(--text-muted);">' + totalCount + ' إجمالي</span>';
      html += '    </div>';
      html += '    <div class="progress-container" title="نشط: ' + activeCount + ' | منقول: ' + transCount + ' | متخرج: ' + gradCount + '">';
      html += '      <div class="progress-bar-fill bg-active" style="width:' + activePerc + '%"></div>';
      html += '      <div class="progress-bar-fill bg-transferred" style="width:' + transPerc + '%"></div>';
      html += '      <div class="progress-bar-fill bg-graduated" style="width:' + gradPerc + '%"></div>';
      html += '    </div>';
      html += '  </td>';
      html += '  <td>';
      html += '    <div class="action-btns">';
      html += '      <button class="action-btn view" onclick="viewStudentsBySection(\'' + sec.section_id + '\')" title="عرض الطلاب"><i class="fas fa-users"></i></button>';
      html += '      <button class="action-btn edit" onclick="showToast(\'ميزة التعديل قيد التطوير\', \'warning\')" title="تعديل"><i class="fas fa-edit"></i></button>';
      html += '      <button class="action-btn delete" onclick="deleteSection(\'' + sec.section_id + '\', \'' + stageId + '\', \'' + gradeId + '\')" title="حذف شُعبة">';
      html += '        <i class="fas fa-trash"></i>';
      html += '      </button>';
      html += '    </div>';
      html += '  </td>';
      html += '</tr>';
    });
  }

  html += '    </tbody>';
  html += '  </table>';
  html += '</div>';

  html += '<div class="add-section-group mt-4 p-3 border-top bg-light-subtle rounded">';
  html += '  <span class="fw-bold mb-2 d-block" style="font-size:0.9rem;">إضافة شعبة جديدة في ' + gradeName + ':</span>';
  html += '  <div class="d-flex gap-2">';
  html += '    <input type="text" class="form-control form-control-sm" style="max-width:220px;" placeholder="اسم الشعبة (مثال: د)" id="newSectionName" maxlength="5" />';
  html += '    <button class="btn btn-secondary btn-sm" onclick="addSection(\'' + stageId + '\', \'' + gradeId + '\')">';
  html += '      <i class="fas fa-plus"></i> إضافة شعبة';
  html += '    </button>';
  html += '  </div>';
  html += '</div>';

  container.innerHTML = html;

  // Initialize table search for the new table
  if (typeof initTableSearch === 'function') {
    initTableSearch();
  }
}

// View students registered in a specific section by navigating to the new page
function viewStudentsBySection(sectionId) {
  var section = SECTIONS.find(function(s) { return s.section_id === sectionId; });
  if (!section) return;
  // Redirect to the dedicated section students page
  window.location.href = 'section-students.html?section_id=' + sectionId;
}

function addSection(stageId, gradeId) {
  var nameInput = document.getElementById('newSectionName');
  var newName = nameInput.value.trim();
  if (!newName) { showToast('يرجى إدخال اسم الشعبة', 'error'); return; }

  var existing = SECTIONS.filter(function (s) { return s.stage_id === stageId && s.grade_id === gradeId && s.name === newName; });
  if (existing.length > 0) { showToast('الشعبة "' + newName + '" موجودة مسبقاً', 'error'); return; }

  SECTIONS.push({ section_id: 's' + Date.now(), grade_id: gradeId, stage_id: stageId, name: newName });

  var container = document.getElementById('sectionsContainer');
  var title = document.getElementById('sectionsTitle');
  renderSections(stageId, gradeId, container, title);
  showToast('تم إضافة الشعبة "' + newName + '" بنجاح', 'success');
}

function deleteSection(sectionId, stageId, gradeId) {
  var sectionName = getSectionName(sectionId);
  if (!confirm('هل أنت متأكد من حذف الشعبة "' + sectionName + '"؟')) return;

  SECTIONS = SECTIONS.filter(function (s) { return s.section_id !== sectionId; });
  var container = document.getElementById('sectionsContainer');
  var title = document.getElementById('sectionsTitle');
  renderSections(stageId, gradeId, container, title);
  showToast('تم حذف الشعبة "' + sectionName + '"', 'error');
}


// --- 9. GRADES PAGE — Render Cards ---

function renderGrades(stageFilter) {
  var container = document.getElementById('gradesGrid');
  if (!container) return;

  var filtered = (stageFilter === 'all') ? GRADES : GRADES.filter(function (g) { return g.stage_id === stageFilter; });

  var html = '';
  filtered.forEach(function (grade) {
    var stageClass = (grade.stage_id === '1') ? 'elementary' : 'preparatory';
    var stageName = getStageName(grade.stage_id);
    var sections = getSectionsByGrade(grade.stage_id, grade.grade_id);
    var studentCount = STUDENTS.filter(function (s) { return s.grade_id === grade.grade_id && s.status === 'نشط'; }).length;

    html += '<div class="grade-card ' + stageClass + '" id="grade_' + grade.grade_id + '">';
    html += '  <div class="grade-num">' + grade.gradeNum + '</div>';
    html += '  <h4>' + grade.name + '</h4>';
    html += '  <p class="grade-stage">' + stageName + '</p>';
    html += '  <div class="grade-meta">';
    html += '    <span><i class="fas fa-sitemap"></i> ' + sections.length + ' شعبة</span>';
    html += '    <span><i class="fas fa-users"></i> ' + studentCount + ' طالب</span>';
    html += '  </div>';
    html += '  <a href="sections.html?stage_id=' + grade.stage_id + '&grade_id=' + grade.grade_id + '" class="btn-sections">';
    html += '    <i class="fas fa-eye"></i> عرض الشُعب';
    html += '  </a>';
    html += '</div>';
  });

  container.innerHTML = html;
}


// --- 10. STUDENTS TABLE — Filter & Render ---

// Render students table with all filters
function renderStudentsTable(stageId, gradeId, sectionId, searchTerm, statusFilter) {
  var tbody = document.getElementById('studentsTableBody');
  var countEl = document.getElementById('studentsCount');
  if (!tbody) return;

  statusFilter = statusFilter || '';

  var filtered = STUDENTS.filter(function (s) {
    if (stageId && s.stage_id !== stageId) return false;
    if (gradeId && s.grade_id !== gradeId) return false;
    if (sectionId && s.section_id !== sectionId) return false;
    if (statusFilter && s.status !== statusFilter) return false;
    if (searchTerm) {
      var term = searchTerm.toLowerCase();
      if (s.name.toLowerCase().indexOf(term) === -1 && s.student_id.indexOf(term) === -1) return false;
    }
    return true;
  });

  var html = '';
  if (filtered.length === 0) {
    html = '<tr><td colspan="10" style="text-align:center; padding:40px; color:var(--text-muted);">';
    html += '<i class="fas fa-search" style="font-size:2rem; display:block; margin-bottom:12px; opacity:0.3;"></i>';
    html += 'لا توجد نتائج مطابقة';
    html += '</td></tr>';
  } else {
    filtered.forEach(function (student, index) {
      var genderClass = (student.gender === 'ذكر') ? 'badge-info' : 'badge-danger';
      var statusBadge = getStatusBadge(student.status);

      html += '<tr>';
      html += '  <td>' + (index + 1) + '</td>';
      html += '  <td><strong style="direction:ltr;display:inline-block;">' + escapeHtml(student.student_id) + '</strong></td>';
      html += '  <td>' + escapeHtml(student.name) + '</td>';
      html += '  <td><span class="badge ' + genderClass + '">' + student.gender + '</span></td>';
      html += '  <td>' + getStageName(student.stage_id) + '</td>';
      html += '  <td>' + getGradeName(student.grade_id) + '</td>';
      html += '  <td>' + getSectionName(student.section_id) + '</td>';
      html += '  <td>' + statusBadge + '</td>';
      html += '  <td>';
      html += '    <div class="action-btns">';
      html += '      <a href="student-show.html?id=' + student.id + '" class="action-btn view" title="عرض"><i class="fas fa-eye"></i></a>';

      if (student.status === 'نشط') {
        html += '      <button class="action-btn edit" onclick="window.location=\'student-add.html?edit=' + student.id + '\'" title="تعديل"><i class="fas fa-edit"></i></button>';
        html += '      <button class="action-btn delete" onclick="handleTransferOut(' + student.id + ')" title="نقل"><i class="fas fa-exchange-alt"></i></button>';
      } else {
        html += '      <button class="action-btn edit" onclick="handleReactivate(' + student.id + ')" title="إعادة تنشيط"><i class="fas fa-undo"></i></button>';
      }

      html += '      <button class="action-btn delete" onclick="handleDeleteStudent(' + student.id + ')" title="حذف"><i class="fas fa-trash"></i></button>';
      html += '    </div>';
      html += '  </td>';
      html += '</tr>';
    });
  }

  tbody.innerHTML = html;

  // Update counts
  if (countEl) {
    var activeCount = STUDENTS.filter(function(s){ return s.status === 'نشط'; }).length;
    countEl.textContent = 'عرض ' + filtered.length + ' طالب — الإجمالي: ' + STUDENTS.length + ' — النشطين: ' + activeCount;
  }
}


function getStatusBadge(status) {
  var cls = 'badge-success';
  if (status === 'منقول') cls = 'badge-warning';
  if (status === 'أنهى المرحلة') cls = 'badge-secondary';
  return '<span class="badge ' + cls + '">' + status + '</span>';
}


function handleTransferOut(studentId) {
  var student = STUDENTS.find(function(s){ return s.id === studentId; });
  if (!student) return;
  if (!confirm('هل تريد نقل الطالب "' + student.name + '" خارج المدرسة؟')) return;

  var result = transferStudentOut(studentId);
  if (result.success) {
    showToast(result.message, 'success');
    if (typeof saveDB === 'function') saveDB();
    if (typeof refreshStudentsTable === 'function') refreshStudentsTable();
  } else {
    showToast(result.message, 'error');
  }
}


function handleReactivate(studentId) {
  var student = STUDENTS.find(function(s){ return s.id === studentId; });
  if (!student) return;
  if (!confirm('هل تريد إعادة تنشيط الطالب "' + student.name + '"؟')) return;

  var result = reactivateStudent(studentId);
  if (result.success) {
    showToast(result.message, 'success');
    if (typeof saveDB === 'function') saveDB();
    if (typeof refreshStudentsTable === 'function') refreshStudentsTable();
  } else {
    showToast(result.message, 'error');
  }
}


function handleDeleteStudent(studentId) {
  var student = STUDENTS.find(function(s){ return s.id === studentId; });
  if (!student) return;

  // تأكيد مزدوج مع معلومات الطالب (Fix #5)
  var msg =
    'تحذير: حذف نهائي لا يمكن التراجع عنه!\n\n' +
    'الطالب: ' + student.name + '\n' +
    'الرقم: '  + student.student_id + '\n\n' +
    'هل تؤكد الحذف؟';

  if (!confirm(msg)) return;

  var result = deleteStudent(studentId);
  // نوع الـ toast صحيح الآن
  showToast(result.message, result.success ? 'success' : 'error');
  if (result.success) {
    if (typeof saveDB === 'function') saveDB();
    if (typeof refreshStudentsTable === 'function') refreshStudentsTable();
  }
}


// --- 11. PROMOTIONS PAGE HELPERS ---

// Render promotion candidates table for a given grade
function renderPromotionCandidates(gradeId) {
  var tbody = document.getElementById('promotionCandidatesBody');
  var countEl = document.getElementById('candidatesCount');
  if (!tbody) return;

  var candidates = STUDENTS.filter(function (s) {
    return s.grade_id === gradeId && s.status === 'نشط';
  });

  var target = getPromotionTarget(gradeId);
  var targetLabel = '';
  if (target) {
    targetLabel = target.isGraduation ? 'إنهاء المرحلة الأساسية' : getGradeName(target.newGradeId) + ' — ' + getStageName(target.newStageId);
  }

  // Update target display
  var targetEl = document.getElementById('promotionTarget');
  if (targetEl) targetEl.textContent = targetLabel;

  var html = '';
  if (candidates.length === 0) {
    html = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">لا يوجد طلاب نشطين في هذا الصف</td></tr>';
  } else {
    candidates.forEach(function (s, i) {
      html += '<tr>';
      html += '  <td><input type="checkbox" class="promote-check" value="' + s.id + '" checked /></td>';
      html += '  <td>' + s.student_id + '</td>';
      html += '  <td>' + s.name + '</td>';
      html += '  <td>' + getStageName(s.stage_id) + '</td>';
      html += '  <td>' + getGradeName(s.grade_id) + '</td>';
      html += '  <td>' + getSectionName(s.section_id) + '</td>';
      html += '  <td>' + targetLabel + '</td>';
      html += '</tr>';
    });
  }

  tbody.innerHTML = html;
  if (countEl) countEl.textContent = candidates.length + ' طالب';
}

// Render promotion log history
function renderPromotionLog() {
  var tbody = document.getElementById('promotionLogBody');
  if (!tbody) return;

  if (PROMOTION_LOG.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted);">لا يوجد سجل ترقيات</td></tr>';
    return;
  }

  var html = '';
  PROMOTION_LOG.forEach(function (log, i) {
    html += '<tr>';
    html += '  <td>' + (i + 1) + '</td>';
    html += '  <td>' + log.student_name + '</td>';
    html += '  <td>' + getStageName(log.from_stage) + ' — ' + getGradeName(log.from_grade) + '</td>';
    html += '  <td>' + (log.to_grade !== '-' ? (getStageName(log.to_stage) + ' — ' + getGradeName(log.to_grade)) : '—') + '</td>';
    html += '  <td>' + getStatusBadge(log.type === 'تخرج' ? 'أنهى المرحلة' : 'نشط') + '</td>';
    html += '  <td>' + log.date + '</td>';
    html += '</tr>';
  });
  tbody.innerHTML = html;
}


// --- 12. GRADUATED PAGE HELPERS ---

function renderGraduatedStudents() {
  var tbody = document.getElementById('graduatedTableBody');
  var countEl = document.getElementById('graduatedCount');
  if (!tbody) return;

  var graduated = STUDENTS.filter(function (s) { return s.status === 'أنهى المرحلة'; });

  var html = '';
  if (graduated.length === 0) {
    html = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">لا يوجد طلاب أنهوا المرحلة</td></tr>';
  } else {
    graduated.forEach(function (s, i) {
      html += '<tr>';
      html += '  <td>' + (i + 1) + '</td>';
      html += '  <td>' + s.student_id + '</td>';
      html += '  <td>' + s.name + '</td>';
      html += '  <td>' + getStageName(s.stage_id) + '</td>';
      html += '  <td>' + getGradeName(s.grade_id) + '</td>';
      html += '  <td><span class="badge badge-info">أنهى المرحلة</span></td>';
      html += '  <td>';
      html += '    <div class="action-btns">';
      html += '      <button class="action-btn edit" onclick="handleReactivateGraduated(' + s.id + ')" title="إعادة للنشطين"><i class="fas fa-undo"></i></button>';
      html += '    </div>';
      html += '  </td>';
      html += '</tr>';
    });
  }

  tbody.innerHTML = html;
  if (countEl) countEl.textContent = graduated.length + ' طالب';
}

function handleReactivateGraduated(studentId) {
  var student = STUDENTS.find(function(s){ return s.id === studentId; });
  if (!student) return;
  if (!confirm('هل تريد إعادة "' + student.name + '" إلى قائمة الطلاب النشطين؟')) return;

  var result = reactivateStudent(studentId);
  if (result.success) {
    showToast('تم إعادة الطالب "' + student.name + '" للقائمة النشطة', 'success');
    renderGraduatedStudents();
  }
}


// --- 13. CHART HELPERS (Dashboard) ---

function initStudentsChart(canvasId) {
  var ctx = document.getElementById(canvasId);
  if (!ctx || typeof Chart === 'undefined') return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
      datasets: [{ label: 'طلاب جدد', data: [65, 78, 90, 81, 56, 95], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#3b82f6' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
}

function initGradesChart(canvasId) {
  var ctx = document.getElementById(canvasId);
  if (!ctx || typeof Chart === 'undefined') return;
  var elemCount = STUDENTS.filter(function(s){ return s.stage_id === '1' && s.status === 'نشط'; }).length;
  var prepCount = STUDENTS.filter(function(s){ return s.stage_id === '2' && s.status === 'نشط'; }).length;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['المرحلة الابتدائية', 'المرحلة الإعدادية'],
      datasets: [{ data: [elemCount, prepCount], backgroundColor: ['#3b82f6', '#8b5cf6'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
  });
}
