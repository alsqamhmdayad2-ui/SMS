// =============================================
// ERD Data - School Management System (40 Tables)
// =============================================

const TABLE_GROUPS = [
  { id: 'users', label: 'إدارة المستخدمين', icon: 'fa-users-cog', color: '#6366f1' },
  { id: 'academic', label: 'الهيكل الأكاديمي', icon: 'fa-graduation-cap', color: '#0ea5e9' },
  { id: 'pivot', label: 'الجداول الوسيطة', icon: 'fa-link', color: '#10b981' },
  { id: 'schedule', label: 'الجدول الزمني', icon: 'fa-calendar-alt', color: '#f59e0b' },
  { id: 'attendance', label: 'الحضور والسلوك', icon: 'fa-clipboard-check', color: '#ef4444' },
  { id: 'exams', label: 'الامتحانات والشهادات', icon: 'fa-file-alt', color: '#8b5cf6' },
  { id: 'assignments', label: 'الواجبات', icon: 'fa-tasks', color: '#ec4899' },
  { id: 'finance', label: 'النظام المالي', icon: 'fa-money-bill-wave', color: '#14b8a6' },
  { id: 'transport', label: 'النقل المدرسي', icon: 'fa-bus', color: '#f97316' },
  { id: 'services', label: 'خدمات إضافية', icon: 'fa-concierge-bell', color: '#64748b' },
];

const TABLES = [
  // ── Group: Users ──
  { id: 'users', name: 'users', label: 'الحسابات', group: 'users', x: 420, y: 40, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(255)' }, { n: 'email', t: 'Varchar(255)', k: 'UQ' },
    { n: 'password', t: 'Varchar(255)' }, { n: 'role', t: 'Enum', d: 'admin,teacher,student,parent' }
  ]},
  { id: 'admins', name: 'admins', label: 'الإداريين', group: 'users', x: 160, y: 230, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'user_id', t: 'BigInt', k: 'FK' }, { n: 'position', t: 'Varchar(100)' }
  ]},
  { id: 'teachers', name: 'teachers', label: 'المعلمين', group: 'users', x: 420, y: 230, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'user_id', t: 'BigInt', k: 'FK' }, { n: 'national_id', t: 'Varchar(50)', k: 'UQ' }
  ]},
  { id: 'parents', name: 'parents', label: 'أولياء الأمور', group: 'users', x: 680, y: 230, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'user_id', t: 'BigInt', k: 'FK' }, { n: 'phone', t: 'Varchar(20)' }
  ]},
  { id: 'students', name: 'students', label: 'الطلاب', group: 'users', x: 420, y: 420, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'user_id', t: 'BigInt', k: 'FK' }, { n: 'parent_id', t: 'BigInt', k: 'FK' },
    { n: 'section_id', t: 'BigInt', k: 'FK' }, { n: 'grade_id', t: 'BigInt', k: 'FK' },
    { n: 'academic_year_id', t: 'BigInt', k: 'FK' }, { n: 'deleted_at', t: 'Timestamp', k: 'NL' }
  ]},

  // ── Group: Academic ──
  { id: 'academic_years', name: 'academic_years', label: 'الأعوام الدراسية', group: 'academic', x: 40, y: 500, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(50)', k: 'UQ' }
  ]},
  { id: 'semesters', name: 'semesters', label: 'الفصول الدراسية', group: 'academic', x: 40, y: 650, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'academic_year_id', t: 'BigInt', k: 'FK' }
  ]},
  { id: 'stages', name: 'stages', label: 'المراحل', group: 'academic', x: 40, y: 810, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)', k: 'UQ' }
  ]},
  { id: 'grades', name: 'grades', label: 'الصفوف', group: 'academic', x: 40, y: 950, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'stage_id', t: 'BigInt', k: 'FK' }
  ]},
  { id: 'sections', name: 'sections', label: 'الشُعب', group: 'academic', x: 40, y: 1100, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(50)' }, { n: 'grade_id', t: 'BigInt', k: 'FK' }, { n: 'deleted_at', t: 'Timestamp', k: 'NL' }
  ]},

  // ── Group: Pivot ──
  { id: 'section_teacher', name: 'section_teacher', label: 'تعيين المعلمين للشعب', group: 'pivot', x: 290, y: 680, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'section_id', t: 'BigInt', k: 'FK' }, { n: 'teacher_id', t: 'BigInt', k: 'FK' },
    { n: 'subject_id', t: 'BigInt', k: 'FK-NL' }, { n: 'is_supervisor', t: 'Boolean' }
  ]},
  { id: 'subjects', name: 'subjects', label: 'المواد الدراسية', group: 'pivot', x: 540, y: 680, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'grade_id', t: 'BigInt', k: 'FK' }
  ]},
  { id: 'subject_teacher', name: 'subject_teacher', label: 'تعيين المواد للمعلمين', group: 'pivot', x: 540, y: 850, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'teacher_id', t: 'BigInt', k: 'FK' }, { n: 'subject_id', t: 'BigInt', k: 'FK' }
  ]},
  { id: 'subject_semester', name: 'subject_semester', label: 'توزيع المواد على الفصول', group: 'pivot', x: 290, y: 850, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'subject_id', t: 'BigInt', k: 'FK' }, { n: 'semester_id', t: 'BigInt', k: 'FK' }, { n: 'hours', t: 'Int', k: 'NL' }
  ]},

  // ── Group: Schedule ──
  { id: 'classrooms', name: 'classrooms', label: 'القاعات', group: 'schedule', x: 800, y: 680, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'capacity', t: 'Int', k: 'NL' }
  ]},
  { id: 'periods', name: 'periods', label: 'الحصص الزمنية', group: 'schedule', x: 800, y: 830, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(50)' }, { n: 'start_time', t: 'Time' }, { n: 'end_time', t: 'Time' }
  ]},
  { id: 'schedules', name: 'schedules', label: 'جدول الحصص', group: 'schedule', x: 800, y: 1000, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'section_id', t: 'BigInt', k: 'FK' }, { n: 'subject_teacher_id', t: 'BigInt', k: 'FK' },
    { n: 'period_id', t: 'BigInt', k: 'FK' }, { n: 'classroom_id', t: 'BigInt', k: 'FK' }, { n: 'day', t: 'Enum' }
  ]},

  // ── Group: Attendance ──
  { id: 'attendance', name: 'attendance', label: 'حضور الطلاب', group: 'attendance', x: 290, y: 1060, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'date', t: 'Date' },
    { n: 'status', t: 'Enum', d: 'present,absent,late' }, { n: 'notes', t: 'Text', k: 'NL' }
  ]},
  { id: 'staff_attendance', name: 'staff_attendance', label: 'حضور الموظفين', group: 'attendance', x: 290, y: 1250, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'teacher_id', t: 'BigInt', k: 'FK' }, { n: 'date', t: 'Date' },
    { n: 'status', t: 'Enum' }, { n: 'check_in', t: 'Time', k: 'NL' }, { n: 'check_out', t: 'Time', k: 'NL' }
  ]},
  { id: 'behavior_records', name: 'behavior_records', label: 'سجل السلوك', group: 'attendance', x: 540, y: 1250, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'type', t: 'Enum', d: 'positive,negative' },
    { n: 'description', t: 'Text' }, { n: 'date', t: 'Date' }
  ]},

  // ── Group: Exams ──
  { id: 'exams', name: 'exams', label: 'الامتحانات', group: 'exams', x: 540, y: 1060, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'subject_id', t: 'BigInt', k: 'FK' }, { n: 'name', t: 'Varchar(150)' },
    { n: 'exam_date', t: 'Date' }, { n: 'total_mark', t: 'Decimal(5,2)' }
  ]},
  { id: 'exam_records', name: 'exam_records', label: 'نتائج الامتحانات', group: 'exams', x: 680, y: 1250, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'exam_id', t: 'BigInt', k: 'FK' }, { n: 'student_id', t: 'BigInt', k: 'FK' },
    { n: 'mark', t: 'Decimal(5,2)' }, { n: 'percentage', t: 'Decimal(5,2)' }, { n: 'grade_letter', t: 'Varchar(5)', k: 'NL' }
  ]},
  { id: 'certificates', name: 'certificates', label: 'الشهادات', group: 'exams', x: 820, y: 1250, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'type', t: 'Varchar(100)' },
    { n: 'issued_at', t: 'Date' }, { n: 'file_path', t: 'Varchar(255)', k: 'NL' }
  ]},

  // ── Group: Assignments ──
  { id: 'assignments', name: 'assignments', label: 'الواجبات', group: 'assignments', x: 40, y: 1280, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'subject_id', t: 'BigInt', k: 'FK' }, { n: 'title', t: 'Varchar(200)' },
    { n: 'due_date', t: 'Date' }, { n: 'description', t: 'Text', k: 'NL' }
  ]},
  { id: 'assignment_submissions', name: 'assignment_submissions', label: 'تسليمات الواجبات', group: 'assignments', x: 40, y: 1470, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'assignment_id', t: 'BigInt', k: 'FK' }, { n: 'student_id', t: 'BigInt', k: 'FK' },
    { n: 'file_path', t: 'Varchar(255)', k: 'NL' }, { n: 'grade', t: 'Decimal(5,2)', k: 'NL' }, { n: 'submitted_at', t: 'Timestamp' }
  ]},

  // ── Group: Finance ──
  { id: 'fee_types', name: 'fee_types', label: 'فئات الرسوم', group: 'finance', x: 1050, y: 40, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'amount', t: 'Decimal(10,2)' }
  ]},
  { id: 'invoices', name: 'invoices', label: 'الفواتير', group: 'finance', x: 1050, y: 200, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'fee_type_id', t: 'BigInt', k: 'FK' },
    { n: 'amount', t: 'Decimal(10,2)' }, { n: 'status', t: 'Enum', d: 'pending,paid,partial' }, { n: 'deleted_at', t: 'Timestamp', k: 'NL' }
  ]},
  { id: 'receipts', name: 'receipts', label: 'سندات القبض', group: 'finance', x: 1050, y: 420, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'invoice_id', t: 'BigInt', k: 'FK' }, { n: 'amount', t: 'Decimal(10,2)' },
    { n: 'receipt_number', t: 'Varchar(50)', k: 'UQ' }, { n: 'issued_at', t: 'Timestamp' }
  ]},
  { id: 'payments', name: 'payments', label: 'المدفوعات', group: 'finance', x: 1050, y: 620, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'invoice_id', t: 'BigInt', k: 'FK' }, { n: 'amount', t: 'Decimal(10,2)' },
    { n: 'method', t: 'Enum', d: 'cash,bank,online' }, { n: 'transaction_ref', t: 'Varchar(100)', k: 'NL' }, { n: 'paid_at', t: 'Timestamp' }
  ]},

  // ── Group: Transport ──
  { id: 'transport_routes', name: 'transport_routes', label: 'مسارات النقل', group: 'transport', x: 1050, y: 830, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'name', t: 'Varchar(100)' }, { n: 'description', t: 'Text', k: 'NL' }
  ]},
  { id: 'school_buses', name: 'school_buses', label: 'حافلات المدرسة', group: 'transport', x: 1050, y: 990, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'bus_number', t: 'Varchar(20)', k: 'UQ' }, { n: 'capacity', t: 'Int' },
    { n: 'driver_name', t: 'Varchar(100)' }
  ]},
  { id: 'student_transport', name: 'student_transport', label: 'اشتراكات النقل', group: 'transport', x: 1050, y: 1170, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'route_id', t: 'BigInt', k: 'FK' },
    { n: 'bus_id', t: 'BigInt', k: 'FK' }
  ]},

  // ── Group: Services ──
  { id: 'medical_visits', name: 'medical_visits', label: 'الزيارات الطبية', group: 'services', x: 290, y: 1470, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'student_id', t: 'BigInt', k: 'FK' }, { n: 'doctor_name', t: 'Varchar(150)', k: 'NL' },
    { n: 'diagnosis', t: 'Text' }, { n: 'treatment', t: 'Text', k: 'NL' }, { n: 'visit_date', t: 'Date' }
  ]},
  { id: 'conversations', name: 'conversations', label: 'المحادثات', group: 'services', x: 540, y: 1470, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'title', t: 'Varchar(255)', k: 'NL' }, { n: 'created_at', t: 'Timestamp' }
  ]},
  { id: 'messages', name: 'messages', label: 'الرسائل', group: 'services', x: 680, y: 1470, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'conversation_id', t: 'BigInt', k: 'FK' }, { n: 'sender_id', t: 'BigInt', k: 'FK' },
    { n: 'message', t: 'Text' }, { n: 'read_at', t: 'Timestamp', k: 'NL' }
  ]},
  { id: 'books', name: 'books', label: 'الكتب', group: 'services', x: 820, y: 1470, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'title', t: 'Varchar(200)' }, { n: 'author', t: 'Varchar(150)' },
    { n: 'isbn', t: 'Varchar(20)', k: 'UQ' }, { n: 'quantity', t: 'Int' }
  ]},
  { id: 'book_loans', name: 'book_loans', label: 'إعارة الكتب', group: 'services', x: 1050, y: 1380, fields: [
    { n: 'id', t: 'BigInt', k: 'PK' }, { n: 'book_id', t: 'BigInt', k: 'FK' }, { n: 'student_id', t: 'BigInt', k: 'FK' },
    { n: 'borrowed_at', t: 'Date' }, { n: 'returned_at', t: 'Date', k: 'NL' }
  ]},
];

// Relationships: [from_table, from_field, to_table, to_field, cardinality_label, type]
// type: '1:1', '1:N', 'N:M'
const RELATIONSHIPS = [
  ['users', 'id', 'admins', 'user_id', '1:1'],
  ['users', 'id', 'teachers', 'user_id', '1:1'],
  ['users', 'id', 'parents', 'user_id', '1:1'],
  ['users', 'id', 'students', 'user_id', '1:1'],
  ['parents', 'id', 'students', 'parent_id', '1:N'],
  ['sections', 'id', 'students', 'section_id', '1:N'],
  ['grades', 'id', 'students', 'grade_id', '1:N'],
  ['academic_years', 'id', 'students', 'academic_year_id', '1:N'],
  ['academic_years', 'id', 'semesters', 'academic_year_id', '1:N'],
  ['stages', 'id', 'grades', 'stage_id', '1:N'],
  ['grades', 'id', 'sections', 'grade_id', '1:N'],
  ['grades', 'id', 'subjects', 'grade_id', '1:N'],
  ['sections', 'id', 'section_teacher', 'section_id', '1:N'],
  ['teachers', 'id', 'section_teacher', 'teacher_id', '1:N'],
  ['teachers', 'id', 'subject_teacher', 'teacher_id', '1:N'],
  ['subjects', 'id', 'subject_teacher', 'subject_id', '1:N'],
  ['subjects', 'id', 'subject_semester', 'subject_id', '1:N'],
  ['semesters', 'id', 'subject_semester', 'semester_id', '1:N'],
  ['sections', 'id', 'schedules', 'section_id', '1:N'],
  ['subject_teacher', 'id', 'schedules', 'subject_teacher_id', '1:N'],
  ['periods', 'id', 'schedules', 'period_id', '1:N'],
  ['classrooms', 'id', 'schedules', 'classroom_id', '1:N'],
  ['students', 'id', 'attendance', 'student_id', '1:N'],
  ['teachers', 'id', 'staff_attendance', 'teacher_id', '1:N'],
  ['students', 'id', 'behavior_records', 'student_id', '1:N'],
  ['subjects', 'id', 'exams', 'subject_id', '1:N'],
  ['exams', 'id', 'exam_records', 'exam_id', '1:N'],
  ['students', 'id', 'exam_records', 'student_id', '1:N'],
  ['students', 'id', 'certificates', 'student_id', '1:N'],
  ['subjects', 'id', 'assignments', 'subject_id', '1:N'],
  ['assignments', 'id', 'assignment_submissions', 'assignment_id', '1:N'],
  ['students', 'id', 'assignment_submissions', 'student_id', '1:N'],
  ['fee_types', 'id', 'invoices', 'fee_type_id', '1:N'],
  ['students', 'id', 'invoices', 'student_id', '1:N'],
  ['invoices', 'id', 'receipts', 'invoice_id', '1:N'],
  ['invoices', 'id', 'payments', 'invoice_id', '1:N'],
  ['transport_routes', 'id', 'student_transport', 'route_id', '1:N'],
  ['school_buses', 'id', 'student_transport', 'bus_id', '1:N'],
  ['students', 'id', 'student_transport', 'student_id', '1:N'],
  ['students', 'id', 'medical_visits', 'student_id', '1:N'],
  ['conversations', 'id', 'messages', 'conversation_id', '1:N'],
  ['users', 'id', 'messages', 'sender_id', '1:N'],
  ['books', 'id', 'book_loans', 'book_id', '1:N'],
  ['students', 'id', 'book_loans', 'student_id', '1:N'],
];
