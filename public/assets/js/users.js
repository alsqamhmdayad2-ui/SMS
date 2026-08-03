// Users & Roles Management Logic
// Mock database for User accounts

var USERS = [
  { id: 1, name: 'أحمد محمد', email: 'admin@school.com', role: 'admin', status: 'نشط', pwd: 'password123' },
  { id: 2, name: 'محمد أحمد العلي', email: 'mohamed.ali@school.com', role: 'student', ref_id: 1, status: 'نشط', pwd: 'password123' },
  { id: 3, name: 'د. أحمد سعيد', email: 'ahmed.saeed@school.com', role: 'teacher', ref_id: 1, status: 'نشط', pwd: 'password123' },
  { id: 4, name: 'أحمد حسن', email: 'ahmed.h@mail.com', role: 'parent', ref_id: 1, status: 'نشط', pwd: 'password123' }
];

function getAllUsers() {
  return USERS;
}

function getUserByEmail(email) {
  return USERS.find(u => u.email === email);
}

function addUser(data) {
  if (!data.name || !data.email || !data.role) {
    return { success: false, message: 'يرجى إكمال الحقول الأساسية (الاسم، البريد، النوع)' };
  }
  // Check email uniqueness
  const exists = USERS.find(u => u.email === data.email);
  if (exists) {
    return { success: false, message: 'البريد الإلكتروني مستخدم مسبقاً' };
  }
  
  data.id = Date.now();
  data.status = data.status || 'نشط';
  data.pwd = data.pwd || '123456';
  
  const newUser = { ...data };
  USERS.push(newUser);
  return { success: true, message: 'تم إنشاء الحساب بنجاح', user: newUser };
}

function updateUser(id, data) {
  const parsedId = parseInt(id) || id;
  const user = USERS.find(u => u.id == parsedId);
  if (!user) return { success: false, message: 'المستخدم غير موجود' };
  
  user.name = data.name || user.name;
  user.role = data.role || user.role;
  user.status = data.status || user.status;
  
  if (data.pwd) {
    user.pwd = data.pwd;
  }
  
  return { success: true, message: 'تم تحديث بيانات الحساب' };
}

function toggleUserStatus(id) {
  const parsedId = parseInt(id) || id;
  const user = USERS.find(u => u.id == parsedId);
  if (!user) return { success: false, message: 'المستخدم غير موجود' };
  
  user.status = (user.status === 'نشط') ? 'معطل' : 'نشط';
  return { success: true, message: 'تم تغيير حالة الحساب بنجاح' };
}

function deleteUser(id) {
  const parsedId = parseInt(id) || id;
  const user = USERS.find(u => u.id == parsedId);
  if (!user) return { success: false, message: 'المستخدم غير موجود' };
  if (user.role === 'admin') return { success: false, message: 'لا يمكن حذف حساب مدير النظام الأساسي!' };

  USERS = USERS.filter(u => u.id != parsedId);
  return { success: true, message: 'تم إزالة الحساب نهائياً' };
}
