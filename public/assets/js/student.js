/**
 * =============================================
 * نظام إدارة المدرسة — سكريبت لوحة تحكم الطالب
 * Student-Specific JavaScript
 * يحتوي على: مؤقت الاختبار، تفاعلات الاختبار، التنقل، الإشعارات، الإعدادات
 * =============================================
 */

/* =============================================
   1. مؤقت الاختبار (Exam Timer) — Fix #4: guard ضد الاستدعاء المزدوج
   ============================================= */
let _activeTimer = null;

function startExamTimer(durationMinutes, displayId, onTimeUp) {
  // إلغاء أي مؤقت سابق
  if (_activeTimer) { clearInterval(_activeTimer); _activeTimer = null; }

  let timeLeft  = durationMinutes * 60;
  let submitted = false;
  const display = document.getElementById(displayId);
  if (!display) return null;

  _activeTimer = setInterval(function() {
    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    display.textContent =
      String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

    if (timeLeft <= 300) {
      display.closest('.exam-timer')?.classList.add('warning');
    }

    if (timeLeft <= 0 && !submitted) {
      submitted = true;
      clearInterval(_activeTimer);
      _activeTimer = null;
      if (onTimeUp) onTimeUp();
      showToast('انتهى وقت الاختبار!', 'warning');
    }
    timeLeft--;
  }, 1000);
  return _activeTimer;
}

// استدعِ هذه الدالة عند التقديم اليدوي
function stopExamTimer() {
  if (_activeTimer) { clearInterval(_activeTimer); _activeTimer = null; }
}

/* =============================================
   2. تفاعلات الاختبار — اختيار الإجابات
   ============================================= */
let currentQuestion = 1;
let totalQuestions = document.querySelectorAll('.question-card').length || 10;

/** تحديث إحصائيات الاختبار بناءً على العناصر الموجودة */
function refreshExamStats() {
  totalQuestions = document.querySelectorAll('.question-card').length || 10;
}

function initAnswerSelection() {
  document.querySelectorAll('.question-card').forEach(card => {
    const options = card.querySelectorAll('.answer-option');
    options.forEach(option => {
      option.addEventListener('click', () => {
        options.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
      });
    });
  });
}

/** اختيار إجابة — يستخدم في exam-take */
function selectAnswer(el) {
  const card = el.closest('.question-card');
  card.querySelectorAll('.answer-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  updateNavigator();
  updateProgress();
}

/* =============================================
   3. التنقل بين الأسئلة
   ============================================= */
function navigateQuestion(dir) {
  const next = currentQuestion + dir;
  if (next < 1 || next > totalQuestions) return;
  goToQuestion(next);
}

function goToQuestion(num) {
  // إخفاء السؤال الحالي وإظهار الجديد
  document.querySelectorAll('.question-card').forEach(c => c.style.display = 'none');
  const target = document.querySelector(`.question-card[data-question="${num}"]`);
  if (target) target.style.display = 'block';

  currentQuestion = num;
  document.getElementById('currentQuestionNum') && (document.getElementById('currentQuestionNum').textContent = num);

  // تحديث أزرار التنقل
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitExamBtn');
  if (prevBtn) prevBtn.disabled = (num === 1);
  if (nextBtn) nextBtn.style.display = (num === totalQuestions) ? 'none' : '';
  if (submitBtn) submitBtn.style.display = (num === totalQuestions) ? '' : 'none';

  updateNavigator();
}

function updateNavigator() {
  const btns = document.querySelectorAll('.q-nav-btn');
  btns.forEach((btn, i) => {
    btn.classList.remove('current');
    if (i + 1 === currentQuestion) btn.classList.add('current');
    // التحقق من وجود إجابة
    const card = document.querySelector(`.question-card[data-question="${i + 1}"]`);
    if (card && card.querySelector('.answer-option.selected')) {
      btn.classList.add('answered');
    }
  });
}

function updateProgress() {
  refreshExamStats();
  const answered = document.querySelectorAll('.question-card .answer-option.selected').length;
  const fill = document.getElementById('progressFill');
  const count = document.getElementById('answeredCount');
  const percentage = (answered / totalQuestions) * 100;
  
  if (fill) {
    fill.style.transition = 'width 0.3s ease';
    fill.style.width = `${percentage}%`;
  }
  if (count) count.textContent = `${answered} / ${totalQuestions} تمت الإجابة`;
}

/* =============================================
   4. تقديم الاختبار
   ============================================= */
function collectAnswers() {
  const answers = [];
  document.querySelectorAll('.question-card').forEach((card, index) => {
    const selected = card.querySelector('.answer-option.selected');
    answers.push({ questionIndex: index + 1, selectedOption: selected ? selected.dataset.option : null });
  });
  return answers;
}

function confirmSubmit() {
  const answers = collectAnswers();
  const unanswered = answers.filter(a => !a.selectedOption).length;
  let msg = 'هل تريد تقديم الاختبار؟';
  if (unanswered > 0) msg = `لديك ${unanswered} سؤال بدون إجابة. ${msg}`;
  if (confirm(msg)) {
    stopExamTimer(); // إيقاف المؤقت عند التقديم اليدوي
    showToast('تم تقديم الاختبار بنجاح!', 'success');
    setTimeout(() => { window.location.href = 'results.html'; }, 1500);
  }
}

function submitExam() { confirmSubmit(); }

/* =============================================
   5. الإشعارات
   ============================================= */
function markAllRead() {
  document.querySelectorAll('.notification-item.unread').forEach(item => {
    item.classList.remove('unread');
  });
  showToast('تم تحديد الكل كمقروء', 'success');
}

/* =============================================
   6. إعدادات — تغيير كلمة المرور
   ============================================= */
function changePassword() {
  const current = document.getElementById('currentPass')?.value;
  const newPass = document.getElementById('newPass')?.value;
  const confirm = document.getElementById('confirmPass')?.value;
  if (!current || !newPass || !confirm) { showToast('يرجى ملء جميع الحقول', 'error'); return; }
  if (newPass !== confirm) { showToast('كلمة المرور الجديدة غير متطابقة', 'error'); return; }
  if (newPass.length < 6) { showToast('كلمة المرور يجب أن تكون 6 أحرف على الأقل', 'error'); return; }
  showToast('تم تغيير كلمة المرور بنجاح', 'success');
}

/* --- تهيئة تلقائية عند تحميل الصفحة --- */
document.addEventListener('DOMContentLoaded', () => {
  refreshExamStats();
  initAnswerSelection();
  updateProgress();
  updateNavigator();
});
