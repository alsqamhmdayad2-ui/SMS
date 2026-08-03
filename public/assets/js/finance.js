// Financial System Javascript Logic
// We use simple arrays like a database to manage the school's money.

var FEES = [
  { id: 1, name: 'رسوم دراسية الفصل الأول', amount: 5000, stage_id: '1', year: '2025-2026', notes: 'رسوم أساسية' },
  { id: 2, name: 'رسوم باص (ذهاب وإياب)', amount: 1200, stage_id: '', year: '2025-2026', notes: 'اختياري' },
];

var INVOICES = [
  { id: 101, student_id: 1, fee_id: 1, total_amount: 5000, date: '2025-09-01' }
];

var RECEIPTS = [
  { id: 201, student_id: 1, amount: 2000, method: 'نقدي', date: '2025-09-02', notes: 'دفعة أولى' }
];

var PAYMENTS = [
  { id: 301, reason: 'صيانة مبنى المختبرات', amount: 500, date: '2025-09-15', method: 'تحويل بنكي' }
];

// --- FINANCIAL CALCULATIONS ---

function getStudentTotalInvoiced(studentId) {
  var total = 0;
  INVOICES.forEach(function(inv) {
    if (inv.student_id === studentId) total += inv.total_amount;
  });
  return total;
}

function getStudentTotalPaid(studentId) {
  var total = 0;
  RECEIPTS.forEach(function(rec) {
    if (rec.student_id === studentId) total += parseFloat(rec.amount);
  });
  return total;
}

// Positive = debt owed by student. Negative = credit (overpaid).
function getStudentBalance(studentId) {
  return getStudentTotalInvoiced(studentId) - getStudentTotalPaid(studentId);
}

function formatCurrency(amount) {
  return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' شيكل';
}

// --- FEES MANAGEMENT ---

function addFee(data) {
  if (!data.name || data.amount <= 0)
    return { success: false, message: 'تأكد من إدخال اسم وقيمة صحيحة للرسوم' };
  data.id = Date.now();
  FEES.push(data);
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم إضافة نوع الرسوم بنجاح' };
}

function updateFee(id, data) {
  var fee = FEES.find(function(f) { return f.id === id; });
  if (!fee) return { success: false, message: 'الرسوم غير موجودة' };
  fee.name    = data.name;
  fee.amount  = data.amount;
  fee.stage_id = data.stage_id;
  fee.year    = data.year;
  fee.notes   = data.notes;
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم تحديث بيانات الرسوم بنجاح' };
}

function deleteFee(id) {
  var inUse = INVOICES.some(function(inv) { return inv.fee_id === id; });
  if (inUse)
    return { success: false, message: 'إجراء مرفوض: لا يمكن حذف هذه الرسوم لارتباطها بفواتير مصدرة ومسجلة' };
  FEES = FEES.filter(function(f) { return f.id !== id; });
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم حذف الرسوم' };
}

// --- INVOICE MANAGEMENT ---

function addInvoice(data) {
  if (!data.student_id || !data.fee_id || data.total_amount <= 0)
    return { success: false, message: 'يجب تعبئة حقول الطالب والرسوم وتحديد مبلغ صحيح' };
  data.id   = Date.now();
  data.date = new Date().toISOString().split('T')[0];
  INVOICES.push(data);
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم إصدار الفاتورة وتوثيق مستحقات على الطالب أصولاً' };
}

/**
 * FIX: Previous logic had a subtle bug.
 *
 * Old check: if (bal - inv.total_amount < 0) → incomplete.
 * Scenario: invoiced=10000, paid=2000, this_invoice=3000
 *   bal = 8000, bal - 3000 = 5000 > 0 → incorrectly allowed deletion
 *   even though the student already made payments.
 *
 * Correct rule: if ANY payment exists for this student, block deletion
 * to preserve accounting integrity. The admin must first delete/reverse
 * receipts before removing invoices.
 */
function deleteInvoice(id) {
  var inv = INVOICES.find(function(i) { return i.id === id; });
  if (!inv) return { success: false, message: 'الفاتورة غير موجودة' };

  var studentPaid = getStudentTotalPaid(inv.student_id);
  if (studentPaid > 0) {
    return {
      success: false,
      message: 'عملية مرفوضة: لا يمكن حذف هذه الفاتورة لوجود دفعات مسددة مسبقاً من الطالب. يُرجى إلغاء سندات القبض أولاً.'
    };
  }

  INVOICES = INVOICES.filter(function(i) { return i.id !== id; });
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم حذف وإسقاط الفاتورة' };
}

// --- RECEIPTS MANAGEMENT ---

function addReceipt(data) {
  if (!data.student_id || data.amount <= 0)
    return { success: false, message: 'يرجى تحديد الطالب وقيمة المبلغ الدفوع' };
  data.id   = Date.now();
  data.date = new Date().toISOString().split('T')[0];
  RECEIPTS.push(data);
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم تسجيل سند القبض وخصم المبلغ من المستحقات بنجاح' };
}

function deleteReceipt(id) {
  RECEIPTS = RECEIPTS.filter(function(r) { return r.id !== id; });
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم إلغاء سند القبض' };
}

// --- PAYMENTS MANAGEMENT ---

function addPayment(data) {
  if (!data.reason || data.amount <= 0)
    return { success: false, message: 'يرجى إدخال سبب الدفع أو الاسترجاع مع تحديد المبلغ' };
  data.id   = Date.now();
  data.date = new Date().toISOString().split('T')[0];
  PAYMENTS.push(data);
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم تسجيل المصروف بنجاح' };
}

function deletePayment(id) {
  PAYMENTS = PAYMENTS.filter(function(p) { return p.id !== id; });
  if (typeof saveDB === 'function') saveDB();
  return { success: true, message: 'تم حذف تسجيل المصروف' };
}