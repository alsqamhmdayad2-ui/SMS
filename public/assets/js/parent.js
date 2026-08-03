/**
 * =============================================
 * نظام إدارة المدرسة — سكريبت لوحة تحكم ولي الأمر
 * =============================================
 */

/* =============================================
   1. تبديل عرض الأبناء (Child Switcher)
   ============================================= */
function switchChild(childId) {
  var sections = document.querySelectorAll('.child-section');
  if (sections.length === 0) return;

  sections.forEach(function(section) {
    section.style.display = 'none';
  });

  var target = document.getElementById(childId);
  if (target) {
    target.style.display = 'block';
    document.querySelectorAll('.child-tab').forEach(function(tab) {
      tab.classList.remove('active');
    });
    var activeTab = document.querySelector('[data-child="' + childId + '"]');
    if (activeTab) activeTab.classList.add('active');
  }
}

/* =============================================
   2. حساب نسبة الدفع
   ============================================= */
function calculatePaymentPercentage(paid, total) {
  if (total === 0) return 0;
  return Math.round((paid / total) * 100);
}

/**
 * تحديث أشرطة التقدم.
 *
 * FIX: كانت الدالة تبحث عن data-percentage على العنصر الأب (.progress-bar أو .progress-track)
 * لكن صفحات ولي الأمر تستخدم .progress-fill داخلهما بـ style مباشر.
 * الآن تعمل بطريقتين:
 *   أ) إذا وُجد data-percentage على العنصر الأب → تطبّقه على .progress-fill الداخلي
 *   ب) إذا وُجد style="width:X%" على .progress-fill مباشرة → إضافة transition فقط
 */
function updateProgressBars() {
  // الطريقة أ: عناصر تحمل data-percentage
  document.querySelectorAll('[data-percentage]').forEach(function(bar) {
    var fill = bar.querySelector('.progress-fill');
    var percentage = parseInt(bar.getAttribute('data-percentage')) || 0;
    if (fill) {
      fill.style.transition = 'width 0.5s ease-in-out';
      fill.style.width = percentage + '%';
    }
  });

  // الطريقة ب: أضف transition على أي progress-fill موجودة مسبقاً بـ style
  document.querySelectorAll('.progress-fill').forEach(function(fill) {
    if (!fill.style.transition) {
      fill.style.transition = 'width 0.5s ease-in-out';
    }
  });
}

/* --- تهيئة تلقائية عند تحميل الصفحة --- */
document.addEventListener('DOMContentLoaded', function() {
  updateProgressBars();
});