/**
 * =============================================
 * School Management System — Teacher JavaScript
 * Contains: Attendance, Quiz Management, Question Bank
 * =============================================
 */

/* =============================================
   1. Attendance Management
   ============================================= */

/** Initialize attendance toggle buttons */
function initAttendanceToggles() {
  document.querySelectorAll('.attendance-toggle').forEach(toggle => {
    const buttons = toggle.querySelectorAll('.toggle-btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });
  });
}

/** Save attendance records */
function saveAttendance() {
  const records = [];
  document.querySelectorAll('.attendance-student').forEach(student => {
    const name = student.querySelector('.student-name')?.textContent;
    const status = student.querySelector('.toggle-btn.active')?.classList.contains('present') ? 'حاضر' : 'غائب';
    records.push({ name, status });
  });
  showToast(`تم حفظ سجل الحضور (${records.length} طالب)`, 'success');
}

/* =============================================
   2. Quiz Management
   ============================================= */

/** Create a new quiz from form data */
function createQuiz() {
  const form = document.getElementById('quizForm');
  if (!form) return;

  const quizData = {
    name: form.querySelector('#quizName')?.value,
    description: form.querySelector('#quizDesc')?.value,
    duration: form.querySelector('#quizDuration')?.value,
    totalScore: form.querySelector('#quizScore')?.value,
    grade: form.querySelector('#quizGrade')?.value,
    classroom: form.querySelector('#quizClassroom')?.value,
    section: form.querySelector('#quizSection')?.value
  };

  if (!quizData.name || !quizData.duration) {
    showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
    return;
  }

  showToast('تم إنشاء الاختبار بنجاح!', 'success');
  if (document.getElementById('createQuizModal')) {
    closeModal('createQuizModal');
  } else {
    setTimeout(() => {
      window.location.href = 'quizzes.html';
    }, 1500);
  }
}

/* =============================================
   3. Question Bank — Moodle-Style
   Full support for 7 question types
   ============================================= */

// --- Type definitions (labels, icons, colors) ---
const QB_TYPES = {
  mcq:          { label: 'اختيار من متعدد', icon: 'fa-list-ul',       cls: 'mcq' },
  tf:           { label: 'صح / خطأ',        icon: 'fa-toggle-on',     cls: 'tf' },
  short:        { label: 'إجابة قصيرة',     icon: 'fa-pen',           cls: 'short' },
  essay:        { label: 'مقالي',           icon: 'fa-file-alt',      cls: 'essay' },
  matching:     { label: 'مطابقة',          icon: 'fa-arrows-alt-h',  cls: 'matching' },
  fill:         { label: 'ملء الفراغ',      icon: 'fa-text-width',    cls: 'fill' },
  multi_select: { label: 'اختيار متعدد',    icon: 'fa-check-double',  cls: 'multi_select' }
};

const QB_DIFFICULTY = {
  easy:   { label: 'سهل',   cls: 'easy'   },
  medium: { label: 'متوسط', cls: 'medium' },
  hard:   { label: 'صعب',   cls: 'hard'   }
};

// --- Sample questions data ---
let questionsData = [
  {
    id: 1, type: 'mcq', category: 'رياضيات', difficulty: 'easy', score: 2,
    text: 'ما هو ناتج 5 × 7 ؟', image: null,
    options: [
      { text: '25', correct: false },
      { text: '35', correct: true },
      { text: '30', correct: false },
      { text: '40', correct: false }
    ]
  },
  {
    id: 2, type: 'mcq', category: 'رياضيات', difficulty: 'medium', score: 3,
    text: 'ما هو مساحة المربع بطول ضلع 4 سم؟', image: null,
    options: [
      { text: '8 سم²', correct: false },
      { text: '12 سم²', correct: false },
      { text: '16 سم²', correct: true },
      { text: '20 سم²', correct: false }
    ]
  },
  {
    id: 3, type: 'tf', category: 'علوم', difficulty: 'easy', score: 1,
    text: 'الماء يتكون من ذرتي هيدروجين وذرة أكسجين.', image: null,
    correctAnswer: true
  },
  {
    id: 4, type: 'tf', category: 'علوم', difficulty: 'easy', score: 1,
    text: 'الشمس تدور حول الأرض.', image: null,
    correctAnswer: false
  },
  {
    id: 5, type: 'short', category: 'لغة عربية', difficulty: 'medium', score: 2,
    text: 'ما هو جمع كلمة "كتاب"؟', image: null,
    acceptedAnswers: ['كتب']
  },
  {
    id: 6, type: 'essay', category: 'لغة عربية', difficulty: 'hard', score: 10,
    text: 'اكتب موضوعاً تعبيرياً عن أهمية القراءة في حياة الإنسان.', image: null,
    minWords: 100, maxWords: 300,
    rubric: 'المحتوى والأفكار (4 درجات) — التنظيم (3 درجات) — اللغة والأسلوب (3 درجات)'
  },
  {
    id: 7, type: 'matching', category: 'لغة إنجليزية', difficulty: 'medium', score: 4,
    text: 'طابق الكلمة الإنجليزية مع معناها بالعربية:', image: null,
    pairs: [
      { left: 'Book', right: 'كتاب' },
      { left: 'School', right: 'مدرسة' },
      { left: 'Teacher', right: 'معلم' },
      { left: 'Student', right: 'طالب' }
    ]
  },
  {
    id: 8, type: 'fill', category: 'علوم', difficulty: 'medium', score: 2,
    text: 'يتكون الماء من عنصري ___ و ___', image: null,
    acceptedAnswers: ['الهيدروجين والأكسجين', 'هيدروجين وأكسجين']
  },
  {
    id: 9, type: 'multi_select', category: 'رياضيات', difficulty: 'hard', score: 3,
    text: 'أي من الأعداد التالية أولية؟', image: null,
    options: [
      { text: '2', correct: true },
      { text: '4', correct: false },
      { text: '7', correct: true },
      { text: '9', correct: false },
      { text: '11', correct: true }
    ]
  },
  {
    id: 10, type: 'mcq', category: 'علوم', difficulty: 'hard', score: 3,
    text: 'ما هو العنصر الأكثر وفرة في القشرة الأرضية؟', image: null,
    options: [
      { text: 'الحديد', correct: false },
      { text: 'الأكسجين', correct: true },
      { text: 'السيليكون', correct: false },
      { text: 'الألومنيوم', correct: false }
    ]
  },
  {
    id: 11, type: 'tf', category: 'رياضيات', difficulty: 'easy', score: 1,
    text: 'مجموع زوايا المثلث يساوي 180 درجة.', image: null,
    correctAnswer: true
  },
  {
    id: 12, type: 'mcq', category: 'لغة إنجليزية', difficulty: 'easy', score: 2,
    text: 'What is the past tense of "go"?', image: null,
    options: [
      { text: 'goed', correct: false },
      { text: 'went', correct: true },
      { text: 'gone', correct: false },
      { text: 'going', correct: false }
    ]
  }
];

// --- Counters for dynamic IDs ---
let mcqOptCounter = 4;
let msOptCounter = 4;
let shortAnsCounter = 1;
let fillAnsCounter = 1;
let matchPairCounter = 3;

// --- Pagination state ---
const ITEMS_PER_PAGE = 8;
let currentPage = 1;

/* =============================================
   Rendering Functions
   ============================================= */

/** Render the full question list based on current filters */
function renderQuestions() {
  const filtered = getFilteredQuestions();
  const totalPages = Math.ceil(filtered.length / ITEMS_PER_PAGE);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  const start = (currentPage - 1) * ITEMS_PER_PAGE;
  const pageItems = filtered.slice(start, start + ITEMS_PER_PAGE);

  const container = document.getElementById('questionsList');
  if (!container) return;

  if (pageItems.length === 0) {
    container.innerHTML = `
      <div style="text-align:center;padding:48px 20px;color:var(--text-muted);">
        <i class="fas fa-inbox" style="font-size:3rem;opacity:0.3;margin-bottom:12px;display:block;"></i>
        <p style="font-size:0.92rem;">لا توجد أسئلة تطابق معايير البحث</p>
      </div>`;
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  let html = '';
  pageItems.forEach((q, idx) => {
    const globalIdx = start + idx + 1;
    const typeInfo = QB_TYPES[q.type] || QB_TYPES.mcq;
    const diffInfo = QB_DIFFICULTY[q.difficulty] || QB_DIFFICULTY.medium;

    html += `
    <div class="qb-card" data-id="${q.id}" id="qCard-${q.id}">
      <div class="qb-card-header" onclick="toggleQuestionDetail(${q.id})">
        <input type="checkbox" class="qb-card-checkbox" onclick="event.stopPropagation();updateBulkUI();" data-qid="${q.id}">
        <div class="qb-card-number">${globalIdx}</div>
        <div class="qb-card-body">
          <div class="qb-card-top">
            <span class="qb-type-badge ${typeInfo.cls}"><i class="fas ${typeInfo.icon}"></i> ${typeInfo.label}</span>
            <span class="qb-diff-badge ${diffInfo.cls}">${diffInfo.label}</span>
            <span class="qb-points"><i class="fas fa-star"></i> ${q.score} درجة</span>
            <span class="qb-category-tag"><i class="fas fa-folder"></i> ${q.category}</span>
          </div>
          <div class="qb-card-text">
            ${q.image ? `<img src="${q.image}" class="qb-q-thumb" alt="صورة السؤال">` : ''}
            ${escapeHtml(q.text)}
          </div>
        </div>
        <div class="qb-card-actions" onclick="event.stopPropagation();">
          <button class="action-btn edit" title="تعديل" onclick="editQuestion(${q.id})"><i class="fas fa-edit"></i></button>
          <button class="action-btn duplicate" title="نسخ" onclick="duplicateQuestion(${q.id})"><i class="fas fa-copy"></i></button>
          <button class="action-btn delete" title="حذف" onclick="deleteQuestion(${q.id})"><i class="fas fa-trash"></i></button>
        </div>
        <button class="qb-expand-btn" id="expandBtn-${q.id}" title="عرض التفاصيل"><i class="fas fa-chevron-down"></i></button>
      </div>
      <div class="qb-card-detail" id="detail-${q.id}">
        ${renderQuestionDetail(q)}
      </div>
    </div>`;
  });

  container.innerHTML = html;
  renderPagination(totalPages);
  updateStats(filtered);
}

/** Render detail section based on question type */
function renderQuestionDetail(q) {
  let html = '';

  // Show question image if exists
  if (q.image) {
    html += `<img src="${q.image}" class="qb-detail-image" alt="صورة السؤال">`;
  }

  switch (q.type) {
    case 'mcq':
    case 'multi_select':
      html += `<div class="qb-detail-label"><i class="fas fa-list-ul" style="margin-left:4px;"></i> الخيارات</div>`;
      html += '<div class="qb-detail-options">';
      (q.options || []).forEach((opt, i) => {
        const letter = String.fromCharCode(1571 + i); // Arabic letter
        html += `
          <div class="qb-detail-option ${opt.correct ? 'correct' : ''}">
            <span class="opt-icon">${opt.correct ? '<i class="fas fa-check"></i>' : ''}</span>
            ${opt.image ? `<img src="${opt.image}" class="opt-thumb" alt="صورة">` : ''}
            <span>${escapeHtml(opt.text)}</span>
          </div>`;
      });
      html += '</div>';
      break;

    case 'tf':
      html += `<div class="qb-detail-label"><i class="fas fa-toggle-on" style="margin-left:4px;"></i> الإجابة الصحيحة</div>`;
      html += `<div class="qb-detail-text">
        ${q.correctAnswer 
          ? '<i class="fas fa-check-circle" style="color:var(--accent);margin-left:6px;"></i> صح' 
          : '<i class="fas fa-times-circle" style="color:var(--danger);margin-left:6px;"></i> خطأ'}
      </div>`;
      break;

    case 'short':
      html += `<div class="qb-detail-label"><i class="fas fa-pen" style="margin-left:4px;"></i> الإجابات المقبولة</div>`;
      html += '<div class="qb-detail-options" style="grid-template-columns:1fr;">';
      (q.acceptedAnswers || []).forEach(ans => {
        html += `<div class="qb-detail-option correct"><span class="opt-icon"><i class="fas fa-check"></i></span><span>${escapeHtml(ans)}</span></div>`;
      });
      html += '</div>';
      break;

    case 'essay':
      html += `<div class="qb-detail-label"><i class="fas fa-file-alt" style="margin-left:4px;"></i> معايير التصحيح</div>`;
      html += `<div class="qb-detail-text">
        <div style="margin-bottom:6px;"><strong>عدد الكلمات:</strong> ${q.minWords || 0} — ${q.maxWords || '∞'}</div>
        ${q.rubric ? `<div>${escapeHtml(q.rubric)}</div>` : '<em style="color:var(--text-muted);">لم يتم تحديد معايير</em>'}
      </div>`;
      break;

    case 'matching':
      html += `<div class="qb-detail-label"><i class="fas fa-arrows-alt-h" style="margin-left:4px;"></i> أزواج المطابقة</div>`;
      html += '<div class="qb-detail-pairs">';
      (q.pairs || []).forEach(pair => {
        html += `
          <div class="qb-detail-pair">
            <span>${escapeHtml(pair.left)}</span>
            <i class="fas fa-long-arrow-alt-left"></i>
            <span>${escapeHtml(pair.right)}</span>
          </div>`;
      });
      html += '</div>';
      break;

    case 'fill':
      html += `<div class="qb-detail-label"><i class="fas fa-text-width" style="margin-left:4px;"></i> الإجابات المقبولة</div>`;
      html += '<div class="qb-detail-options" style="grid-template-columns:1fr;">';
      (q.acceptedAnswers || []).forEach(ans => {
        html += `<div class="qb-detail-option correct"><span class="opt-icon"><i class="fas fa-check"></i></span><span>${escapeHtml(ans)}</span></div>`;
      });
      html += '</div>';
      break;
  }

  return html;
}

/** Render pagination controls */
function renderPagination(totalPages) {
  const container = document.getElementById('pagination');
  if (!container || totalPages <= 1) {
    if (container) container.innerHTML = '';
    return;
  }

  let html = `<button class="qb-page-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;

  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="qb-page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
  }

  html += `<button class="qb-page-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;

  container.innerHTML = html;
}

/** Update stats counters */
function updateStats(filtered) {
  const all = filtered || questionsData;
  document.getElementById('statTotal').textContent = all.length;
  document.getElementById('statMCQ').textContent = all.filter(q => q.type === 'mcq').length;
  document.getElementById('statTF').textContent = all.filter(q => q.type === 'tf').length;
  document.getElementById('statOther').textContent = all.filter(q => !['mcq', 'tf'].includes(q.type)).length;
}

/* =============================================
   Filtering & Search
   ============================================= */

/** Get questions filtered by toolbar selections */
function getFilteredQuestions() {
  const cat = document.getElementById('filterCategory')?.value || '';
  const type = document.getElementById('filterType')?.value || '';
  const diff = document.getElementById('filterDifficulty')?.value || '';
  const search = (document.getElementById('searchInput')?.value || '').toLowerCase();

  return questionsData.filter(q => {
    if (cat && q.category !== cat) return false;
    if (type && q.type !== type) return false;
    if (diff && q.difficulty !== diff) return false;
    if (search && !q.text.toLowerCase().includes(search)) return false;
    return true;
  });
}

/** Filter questions (called by toolbar events) */
function filterQuestions() {
  currentPage = 1;
  renderQuestions();
}

/** Pagination navigation */
function goToPage(page) {
  const totalPages = Math.ceil(getFilteredQuestions().length / ITEMS_PER_PAGE);
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  renderQuestions();
}

/* =============================================
   Question Card Actions
   ============================================= */

/** Toggle expand/collapse on a question card */
function toggleQuestionDetail(id) {
  const detail = document.getElementById(`detail-${id}`);
  const btn = document.getElementById(`expandBtn-${id}`);
  if (detail) {
    detail.classList.toggle('open');
  }
  if (btn) {
    btn.classList.toggle('open');
  }
}

/** Delete a single question */
function deleteQuestion(id) {
  if (!confirm('هل أنت متأكد من حذف هذا السؤال؟')) return;
  questionsData = questionsData.filter(q => q.id !== id);
  renderQuestions(); // This calls updateStats internally
  updateBulkUI();
  showToast('تم حذف السؤال بنجاح', 'success');
}

/** Duplicate a question */
function duplicateQuestion(id) {
  const original = questionsData.find(q => q.id === id);
  if (!original) return;
  
  const maxId = questionsData.length > 0 ? Math.max(...questionsData.map(q => q.id)) : 0;
  const newId = maxId + 1;
  
  const copy = JSON.parse(JSON.stringify(original));
  copy.id = newId;
  copy.text = '(نسخة) ' + copy.text;
  
  questionsData.push(copy);
  renderQuestions();
  showToast('تم نسخ السؤال بنجاح', 'success');
}

/** Edit a question — redirects to question-add page with query param */
function editQuestion(id) {
  window.location.href = `question-add.html?id=${id}`;
}

/* =============================================
   Bulk Operations
   ============================================= */

/** Select / deselect all visible questions */
function selectAllQuestions() {
  const checkboxes = document.querySelectorAll('.qb-card-checkbox');
  const allChecked = [...checkboxes].every(cb => cb.checked);
  checkboxes.forEach(cb => {
    cb.checked = !allChecked;
    const card = cb.closest('.qb-card');
    if (card) card.classList.toggle('selected', cb.checked);
  });
  updateBulkUI();
}

/** Update bulk action button visibility */
function updateBulkUI() {
  const checked = document.querySelectorAll('.qb-card-checkbox:checked');
  const deleteBtn = document.getElementById('deleteSelectedBtn');
  if (deleteBtn) {
    deleteBtn.style.display = checked.length > 0 ? 'inline-flex' : 'none';
  }
  // Update card selection visual
  document.querySelectorAll('.qb-card-checkbox').forEach(cb => {
    const card = cb.closest('.qb-card');
    if (card) card.classList.toggle('selected', cb.checked);
  });
}

/** Delete all selected questions */
function deleteSelected() {
  const checked = document.querySelectorAll('.qb-card-checkbox:checked');
  if (checked.length === 0) return;
  if (!confirm(`هل أنت متأكد من حذف ${checked.length} سؤال؟`)) return;

  const ids = [...checked].map(cb => parseInt(cb.dataset.qid));
  questionsData = questionsData.filter(q => !ids.includes(q.id));
  
  renderQuestions();
  updateBulkUI();
  showToast(`تم حذف ${ids.length} سؤال بنجاح`, 'success');
}

/* =============================================
   Modal — Dynamic Answer Sections
   ============================================= */

/** Toggle answer section based on selected question type */
function toggleQuestionType() {
  const type = document.getElementById('questionType')?.value;
  const sections = ['mcqSection', 'tfSection', 'shortSection', 'essaySection', 'matchingSection', 'fillSection', 'multiSelectSection'];

  // Hide all sections
  sections.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });

  // Show the matching section
  const sectionMap = {
    mcq: 'mcqSection',
    tf: 'tfSection',
    short: 'shortSection',
    essay: 'essaySection',
    matching: 'matchingSection',
    fill: 'fillSection',
    multi_select: 'multiSelectSection'
  };

  const targetSection = document.getElementById(sectionMap[type]);
  if (targetSection) targetSection.style.display = 'block';
}

/* =============================================
   Modal — Dynamic Option/Pair Adding
   ============================================= */

/** Add a new MCQ option row */
function addMCQOption() {
  const container = document.getElementById('mcqOptionsList');
  if (!container) return;
  
  let maxNum = 0;
  container.querySelectorAll('[id^="mcqOpt-"]').forEach(el => {
      let num = parseInt(el.id.replace('mcqOpt-', '')) || 0;
      if (num > maxNum) maxNum = num;
  });
  mcqOptCounter = maxNum + 1;

  const html = `
    <div class="qb-option-row mb-2" id="mcqOpt-${mcqOptCounter}">
      <input type="radio" name="correctAnswer" value="${mcqOptCounter}">
      <input type="text" class="form-control" placeholder="الخيار ${mcqOptCounter}">
      <label class="qb-option-img-btn" title="صورة للخيار">
        <i class="fas fa-image"></i>
        <input type="file" accept="image/*" style="display:none;" onchange="previewOptionImage(this,'mcqOpt-${mcqOptCounter}')">
      </label>
      <img class="qb-option-img-preview" style="display:none;">
      <button type="button" class="qb-remove-option" onclick="removeOption('mcqOpt-${mcqOptCounter}')" title="حذف الخيار"><i class="fas fa-times"></i></button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

/** Add a new multi-select option row */
function addMultiSelectOption() {
  const container = document.getElementById('multiSelectOptionsList');
  if (!container) return;

  let maxNum = 0;
  container.querySelectorAll('[id^="msOpt-"]').forEach(el => {
      let num = parseInt(el.id.replace('msOpt-', '')) || 0;
      if (num > maxNum) maxNum = num;
  });
  msOptCounter = maxNum + 1;

  const html = `
    <div class="qb-option-row mb-2" id="msOpt-${msOptCounter}">
      <input type="checkbox" name="multiCorrectAnswer" value="${msOptCounter}">
      <input type="text" class="form-control" placeholder="الخيار ${msOptCounter}">
      <label class="qb-option-img-btn" title="صورة للخيار">
        <i class="fas fa-image"></i>
        <input type="file" accept="image/*" style="display:none;" onchange="previewOptionImage(this,'msOpt-${msOptCounter}')">
      </label>
      <img class="qb-option-img-preview" style="display:none;">
      <button type="button" class="qb-remove-option" onclick="removeOption('msOpt-${msOptCounter}')" title="حذف الخيار"><i class="fas fa-times"></i></button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

/** Add a new short answer row */
function addShortAnswer() {
  const container = document.getElementById('shortAnswersList');
  if (!container) return;

  let maxNum = 0;
  container.querySelectorAll('[id^="shortAns-"]').forEach(el => {
      let num = parseInt(el.id.replace('shortAns-', '')) || 0;
      if (num > maxNum) maxNum = num;
  });
  shortAnsCounter = maxNum + 1;

  const html = `
    <div class="qb-short-answer-row mb-2 d-flex gap-2" id="shortAns-${shortAnsCounter}">
      <input type="text" class="form-control" placeholder="الإجابة المقبولة ${shortAnsCounter}">
      <button type="button" class="btn btn-outline-danger" onclick="removeOption('shortAns-${shortAnsCounter}')"><i class="fas fa-trash"></i></button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

/** Add a new fill-in-the-blank answer row */
function addFillAnswer() {
  const container = document.getElementById('fillAnswersList');
  if (!container) return;

  let maxNum = 0;
  container.querySelectorAll('[id^="fillAns-"]').forEach(el => {
      let num = parseInt(el.id.replace('fillAns-', '')) || 0;
      if (num > maxNum) maxNum = num;
  });
  fillAnsCounter = maxNum + 1;

  const html = `
    <div class="qb-short-answer-row mb-2 d-flex gap-2" id="fillAns-${fillAnsCounter}">
      <input type="text" class="form-control" placeholder="الإجابة المقبولة ${fillAnsCounter}">
      <button type="button" class="btn btn-outline-danger" onclick="removeOption('fillAns-${fillAnsCounter}')"><i class="fas fa-trash"></i></button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

/** Add a new matching pair row */
function addMatchingPair() {
  const container = document.getElementById('matchingPairsList');
  if (!container) return;

  let maxNum = 0;
  container.querySelectorAll('[id^="matchPair-"]').forEach(el => {
      let num = parseInt(el.id.replace('matchPair-', '')) || 0;
      if (num > maxNum) maxNum = num;
  });
  matchPairCounter = maxNum + 1;

  const html = `
    <div class="qb-matching-row mb-2 d-flex align-items-center gap-3" id="matchPair-${matchPairCounter}">
      <input type="text" class="form-control" placeholder="العنصر ${matchPairCounter}">
      <i class="fas fa-exchange-alt text-muted"></i>
      <input type="text" class="form-control" placeholder="المطابق ${matchPairCounter}">
      <button type="button" class="btn btn-outline-danger" onclick="removeOption('matchPair-${matchPairCounter}')"><i class="fas fa-trash"></i></button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

/** Remove any dynamic row by its element ID */
function removeOption(elementId) {
  const el = document.getElementById(elementId);
  if (el) {
    el.style.opacity = '0';
    el.style.transform = 'translateY(-6px)';
    setTimeout(() => el.remove(), 200);
  }
}

/* =============================================
   Image Upload & Preview
   ============================================= */

/** Preview question image after upload */
function previewQuestionImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const preview = document.getElementById('questionImagePreview');
      const placeholder = document.getElementById('questionUploadPlaceholder');
      const removeBtn = document.getElementById('removeQuestionImage');
      if (preview) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      }
      if (placeholder) placeholder.style.display = 'none';
      if (removeBtn) removeBtn.style.display = 'flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

/** Remove question image preview */
function removeQuestionImg() {
  const preview = document.getElementById('questionImagePreview');
  const placeholder = document.getElementById('questionUploadPlaceholder');
  const removeBtn = document.getElementById('removeQuestionImage');
  const input = document.getElementById('questionImageInput');

  if (preview) { preview.src = ''; preview.style.display = 'none'; }
  if (placeholder) placeholder.style.display = 'flex';
  if (removeBtn) removeBtn.style.display = 'none';
  if (input) input.value = '';
}

/** Preview option image after upload */
function previewOptionImage(input, rowId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const row = document.getElementById(rowId);
      if (!row) return;
      const img = row.querySelector('.qb-option-img-preview');
      if (img) {
        img.src = e.target.result;
        img.style.display = 'block';
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

/* =============================================
   Save Question
   ============================================= */

/** Save a new question from the modal form */
function saveQuestion() {
  const type = document.getElementById('questionType')?.value;
  const text = document.getElementById('questionText')?.value;
  const score = parseFloat(document.getElementById('questionScore')?.value) || 1;
  const category = document.getElementById('questionCategory')?.value || '';
  const difficulty = document.getElementById('questionDifficulty')?.value || 'medium';

  // Validate required fields
  if (!text) {
    showToast('يرجى كتابة نص السؤال', 'error');
    return;
  }

  // Build question object
  const newId = questionsData.length > 0 ? Math.max(...questionsData.map(q => q.id)) + 1 : 1;
  const question = { id: newId, type, text, score, category, difficulty, image: null };

  // Get uploaded question image if any
  const imgPreview = document.getElementById('questionImagePreview');
  if (imgPreview && imgPreview.style.display !== 'none' && imgPreview.src) {
    question.image = imgPreview.src;
  }

  // Type-specific data
  switch (type) {
    case 'mcq': {
      const rows = document.querySelectorAll('#mcqOptionsList .qb-option-row');
      const correctVal = document.querySelector('input[name="correctAnswer"]:checked')?.value;
      if (!correctVal) {
        showToast('يرجى تحديد الإجابة الصحيحة', 'error');
        return;
      }
      question.options = [];
      rows.forEach(row => {
        const txt = row.querySelector('.form-control')?.value || '';
        const radio = row.querySelector('input[type="radio"]');
        const imgEl = row.querySelector('.qb-option-img-preview');
        question.options.push({
          text: txt,
          correct: radio?.value === correctVal,
          image: (imgEl && imgEl.style.display !== 'none') ? imgEl.src : null
        });
      });
      break;
    }
    case 'multi_select': {
      const rows = document.querySelectorAll('#multiSelectOptionsList .qb-option-row');
      const checked = document.querySelectorAll('input[name="multiCorrectAnswer"]:checked');
      if (checked.length === 0) {
        showToast('يرجى تحديد إجابة صحيحة واحدة على الأقل', 'error');
        return;
      }
      const correctVals = [...checked].map(cb => cb.value);
      question.options = [];
      rows.forEach(row => {
        const txt = row.querySelector('.form-control')?.value || '';
        const cb = row.querySelector('input[type="checkbox"]');
        const imgEl = row.querySelector('.qb-option-img-preview');
        question.options.push({
          text: txt,
          correct: correctVals.includes(cb?.value),
          image: (imgEl && imgEl.style.display !== 'none') ? imgEl.src : null
        });
      });
      break;
    }
    case 'tf': {
      const answer = document.querySelector('input[name="tfAnswer"]:checked')?.value;
      question.correctAnswer = answer === 'true';
      break;
    }
    case 'short': {
      const rows = document.querySelectorAll('#shortAnswersList .qb-short-answer-row');
      question.acceptedAnswers = [];
      rows.forEach(row => {
        const val = row.querySelector('.form-control')?.value;
        if (val) question.acceptedAnswers.push(val);
      });
      if (question.acceptedAnswers.length === 0) {
        showToast('يرجى إدخال إجابة مقبولة واحدة على الأقل', 'error');
        return;
      }
      break;
    }
    case 'essay': {
      question.minWords = parseInt(document.getElementById('essayMinWords')?.value) || 0;
      question.maxWords = parseInt(document.getElementById('essayMaxWords')?.value) || 500;
      question.rubric = document.getElementById('essayRubric')?.value || '';
      break;
    }
    case 'matching': {
      const rows = document.querySelectorAll('#matchingPairsList .qb-matching-row');
      question.pairs = [];
      rows.forEach(row => {
        const inputs = row.querySelectorAll('.form-control');
        if (inputs.length >= 2 && (inputs[0].value || inputs[1].value)) {
          question.pairs.push({ left: inputs[0].value, right: inputs[1].value });
        }
      });
      if (question.pairs.length < 2) {
        showToast('يرجى إدخال زوجين مطابقين على الأقل', 'error');
        return;
      }
      break;
    }
    case 'fill': {
      const rows = document.querySelectorAll('#fillAnswersList .qb-short-answer-row');
      question.acceptedAnswers = [];
      rows.forEach(row => {
        const val = row.querySelector('.form-control')?.value;
        if (val) question.acceptedAnswers.push(val);
      });
      if (question.acceptedAnswers.length === 0) {
        showToast('يرجى إدخال إجابة مقبولة واحدة على الأقل', 'error');
        return;
      }
      break;
    }
  }

  // Add to data and re-render
  const urlParams = new URLSearchParams(window.location.search);
  const editId = urlParams.get('id');

  if (editId) {
    const idx = questionsData.findIndex(q => q.id == editId);
    if (idx !== -1) {
      question.id = parseInt(editId);
      questionsData[idx] = question;
    } else {
      questionsData.push(question);
    }
  } else {
    questionsData.push(question);
  }

  if (document.getElementById('questionsList')) {
    renderQuestions();
  }
  
  // If we are in a modal, close it. Otherwise, redirect to questions.html
  if (document.getElementById('addQuestionModal')) {
    closeModal('addQuestionModal');
    resetModal();
    showToast('تم حفظ السؤال بنجاح!', 'success');
  } else {
    // Save to localStorage or similar if needed for persistence across pages,
    // but since we don't have a backend we just show toast and redirect.
    // For purely frontend simulation without persistence across navigation:
    // This will lose the new question due to page refresh, but it's expected in static HTML prototypes.
    showToast('تم حفظ السؤال بنجاح في البنك', 'success');
    setTimeout(() => {
      window.location.href = 'questions.html';
    }, 1500);
  }
}

/** Reset modal form to defaults */
function resetModal() {
  const titleEl = document.getElementById('modalTitle');
  if (titleEl) titleEl.innerHTML = '<i class="fas fa-plus-circle" style="margin-left:8px;"></i>إنشاء سؤال جديد';
  const typeEl = document.getElementById('questionType');
  if (typeEl) typeEl.value = 'mcq';
  const textEl = document.getElementById('questionText');
  if (textEl) textEl.value = '';
  const scoreEl = document.getElementById('questionScore');
  if (scoreEl) scoreEl.value = '1';
  removeQuestionImg();
  toggleQuestionType();
}

/* =============================================
   Utility Functions
   ============================================= */

/** Escape HTML special characters */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/* =============================================
   Initialization
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
  initAttendanceToggles();

  // Render question bank if on the questions page
  if (document.getElementById('questionsList')) {
    renderQuestions();
  }

  // Handle Edit Mode initialization
  if (window.location.pathname.includes('question-add.html')) {
    const urlParams = new URL(window.location.href).searchParams;
    const id = urlParams.get('id');
    if (id) {
      const q = questionsData.find(x => x.id == id);
      if (q) {
        document.getElementById('questionType').value = q.type;
        document.getElementById('questionCategory').value = q.category;
        document.getElementById('questionDifficulty').value = q.difficulty;
        document.getElementById('questionScore').value = q.score;
        document.getElementById('questionText').value = q.text;
        if (typeof toggleQuestionType === 'function') toggleQuestionType();
        
        const headerTitle = document.querySelector('.page-header h2');
        if (headerTitle) {
          headerTitle.innerHTML = '<i class="fas fa-edit" style="margin-inline-start: 10px; color: var(--secondary)"></i>تعديل السؤال';
        }
      }
    }
  }
});
