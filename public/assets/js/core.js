/**
 * =============================================
 * School Management System — Core JavaScript
 * Shared functionality: sidebar, Bootstrap modals,
 * Bootstrap tabs, search, dropdowns, delete confirm,
 * form validation, toast notifications, cascading select
 * =============================================
 */

/* =============================================
   0. Safe Fallback — showToast before DOM loads (Fix #10)
   ============================================= */
(function() {
  if (typeof window.showToast === 'undefined') {
    window.showToast = function(msg, type) {
      console.info('[Toast]', type, msg);
    };
  }
})();

/* =============================================
   0b. HTML Escape — XSS Prevention (Fix #7)
   ============================================= */
function escapeHtml(str) {
  if (!str) return '';
  var d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

/* =============================================
   0c. localStorage Persistence (Fix #9)
   ============================================= */
var DB_KEY = 'noor_sms_v1';

function saveDB() {
  try {
    var snap = {};
    if (typeof STUDENTS      !== 'undefined') snap.STUDENTS      = STUDENTS;
    if (typeof TEACHERS      !== 'undefined') snap.TEACHERS      = TEACHERS;
    if (typeof INVOICES      !== 'undefined') snap.INVOICES      = INVOICES;
    if (typeof RECEIPTS      !== 'undefined') snap.RECEIPTS      = RECEIPTS;
    if (typeof PAYMENTS      !== 'undefined') snap.PAYMENTS      = PAYMENTS;
    if (typeof PROMOTION_LOG !== 'undefined') snap.PROMOTION_LOG = PROMOTION_LOG;
    if (typeof USERS         !== 'undefined') snap.USERS         = USERS;
    localStorage.setItem(DB_KEY, JSON.stringify(snap));
  } catch(e) { console.warn('saveDB:', e); }
}

function loadDB() {
  try {
    var raw = localStorage.getItem(DB_KEY);
    if (!raw) return;
    var db = JSON.parse(raw);
    if (db.STUDENTS      && db.STUDENTS.length)      STUDENTS      = db.STUDENTS;
    if (db.TEACHERS      && db.TEACHERS.length)      TEACHERS      = db.TEACHERS;
    if (db.INVOICES      && db.INVOICES.length)      INVOICES      = db.INVOICES;
    if (db.RECEIPTS      && db.RECEIPTS.length)      RECEIPTS      = db.RECEIPTS;
    if (db.PAYMENTS      && db.PAYMENTS.length)      PAYMENTS      = db.PAYMENTS;
    if (db.PROMOTION_LOG && db.PROMOTION_LOG.length)  PROMOTION_LOG = db.PROMOTION_LOG;
    if (db.USERS         && db.USERS.length)         USERS         = db.USERS;
  } catch(e) { console.warn('loadDB:', e); }
}

document.addEventListener('DOMContentLoaded', () => {
  // Load persisted data first
  if (typeof loadDB === 'function') loadDB();

  initSidebar();
  initModals();
  initTabs();
  initTableSearch();
  initDeleteConfirm();
  initFormValidation();
  initSelectAll();
  initPasswordToggle();
  initAriaLabels();
});

/* =============================================
   1. Sidebar
   — Toggle collapse/expand + mobile overlay + accordion submenus
   ============================================= */
function initSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const toggleBtn = document.querySelector('.sidebar-toggle');

  // --- Create mobile overlay ---
  const mobileOverlay = document.createElement('div');
  mobileOverlay.className = 'sidebar-overlay';
  mobileOverlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:999;display:none;transition:opacity 0.3s ease;';
  document.body.appendChild(mobileOverlay);

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 992) {
        sidebar.classList.toggle('mobile-open');
        mobileOverlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
      } else {
        sidebar.classList.toggle('collapsed');
      }
    });

    mobileOverlay.addEventListener('click', () => {
      sidebar.classList.remove('mobile-open');
      mobileOverlay.style.display = 'none';
    });
  }

  // --- Accordion behavior ---
  document.querySelectorAll('.has-submenu .submenu').forEach(submenu => {
    submenu.addEventListener('show.bs.collapse', () => {
      document.querySelectorAll('.has-submenu .submenu.show').forEach(openMenu => {
        if (openMenu !== submenu) {
          const bsCollapse = bootstrap.Collapse.getInstance(openMenu);
          if (bsCollapse) bsCollapse.hide();
        }
      });
    });
  });

  // --- Auto-set active link & open parent submenu ---
  const currentPath = window.location.pathname.split('/').pop() || 'dashboard.html';
  const sidebarNav = document.querySelector('.sidebar-nav');

  if (sidebarNav) {
    sidebarNav.classList.add('no-transition');

    document.querySelectorAll('.sidebar-nav a').forEach(link => {
      const href = link.getAttribute('href');
      if (href && href !== '#' && !href.startsWith('..')) {
        if (href === currentPath) {
          link.classList.add('active');
          const parent = link.closest('.has-submenu');
          if (parent) {
            const toggle = parent.querySelector('.menu-toggle');
            const submenu = parent.querySelector('.submenu');
            if (toggle && submenu) {
              toggle.setAttribute('aria-expanded', 'true');
              submenu.classList.add('show');
            }
          }
        }
      }
    });

    setTimeout(() => sidebarNav.classList.remove('no-transition'), 100);
  }
}

/* =============================================
   2. Custom Modal System (modal-overlay)
   ============================================= */
function initModals() {
  document.querySelectorAll('[data-modal]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const modalId = trigger.getAttribute('data-modal');
      const overlay = document.getElementById(modalId);
      if (overlay) overlay.classList.add('active');
    });
  });

  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-overlay');
      if (modal) {
        modal.classList.remove('active');
        const form = modal.querySelector('form');
        if (form) form.reset();
      }
    });
  });

  document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-overlay');
      if (modal) {
        modal.classList.remove('active');
        const form = modal.querySelector('form');
        if (form) form.reset();
      }
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        const form = overlay.querySelector('form');
        if (form) form.reset();
      }
    });
  });
}

/* =============================================
   3. Custom Tab System
   ============================================= */
function initTabs() {
  document.querySelectorAll('.tabs .tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tabGroup = btn.closest('.tabs');
      const parent = tabGroup.parentElement;

      tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      parent.querySelectorAll(':scope > .tab-content').forEach(c => c.classList.remove('active'));
      const targetId = btn.getAttribute('data-tab');
      const target = document.getElementById(targetId);
      if (target) target.classList.add('active');
    });
  });
}

/* =============================================
   4. Modal Helpers
   ============================================= */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('active');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.remove('active');
    const form = el.querySelector('form');
    if (form) form.reset();
  }
}

/* =============================================
   5. Table Search
   ============================================= */
function initTableSearch() {
  document.querySelectorAll('[data-table-search]').forEach(input => {
    const tableId = input.getAttribute('data-table-search');
    const table = document.getElementById(tableId);
    if (!table) return;

    input.addEventListener('input', () => {
      const query = input.value.toLowerCase();
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
      });
    });
  });
}

/* =============================================
   6. Delete Confirmation
   ============================================= */
function initDeleteConfirm() {
  document.querySelectorAll('[data-delete]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (confirm('هل أنت متأكد من الحذف؟')) {
        showToast('تم الحذف بنجاح', 'success');
      }
    });
  });
}

/* =============================================
   7. Form Validation
   ============================================= */
function initFormValidation() {
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', (e) => {
      let valid = true;
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          valid = false;
          field.classList.add('is-invalid');
          
          // Add error feedback if not exists
          let feedback = field.nextElementSibling;
          if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'هذا الحقل مطلوب';
            field.after(feedback);
          }

          field.addEventListener('input', () => {
            field.classList.remove('is-invalid');
          }, { once: true });
        }
      });

      if (!valid) {
        e.preventDefault();
        showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
      }
    });
  });
}

/* =============================================
   8. Select All Checkboxes
   ============================================= */
function initSelectAll() {
  document.querySelectorAll('table thead input[type="checkbox"]').forEach(selectAllBtn => {
    selectAllBtn.addEventListener('change', () => {
      const table = selectAllBtn.closest('table');
      const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
      checkboxes.forEach(cb => {
        cb.checked = selectAllBtn.checked;
        cb.dispatchEvent(new Event('change'));
      });
    });
  });
}

/* =============================================
   10. Password Visibility Toggle
   ============================================= */
function initPasswordToggle() {
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);

      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
          input.type = 'password';
          btn.innerHTML = '<i class="fas fa-eye"></i>';
        }
      }
    });
  });
}

/* =============================================
   9. Toast Notifications
   ============================================= */
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container-custom');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container-custom';
    document.body.appendChild(container);
  }

  const icons = {
    success: 'fa-check-circle',
    error: 'fa-times-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };

  const toast = document.createElement('div');
  toast.className = `custom-toast ${type}`;
  toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${message}</span>`;
  container.prepend(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-100%)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/* =============================================
   10. Sort Table
   — Sort any data-table by column index
   ============================================= */
function sortTable(tableId, colIndex, type) {
  var table = document.getElementById(tableId);
  if (!table) return;

  var tbody = table.querySelector('tbody');
  if (!tbody) return;

  var rows = Array.from(tbody.querySelectorAll('tr'));
  var asc = table.getAttribute('data-sort-asc') !== 'false';

  rows.sort(function(a, b) {
    var aCell = a.cells[colIndex];
    var bCell = b.cells[colIndex];
    if (!aCell || !bCell) return 0;

    var aVal = aCell.textContent.trim();
    var bVal = bCell.textContent.trim();

    if (type === 'number') {
      var aNum = parseFloat(aVal.replace(/[^0-9.-]/g, '')) || 0;
      var bNum = parseFloat(bVal.replace(/[^0-9.-]/g, '')) || 0;
      return asc ? aNum - bNum : bNum - aNum;
    }

    // string / default
    return asc
      ? aVal.localeCompare(bVal, 'ar')
      : bVal.localeCompare(aVal, 'ar');
  });

  // Toggle direction for next click
  table.setAttribute('data-sort-asc', asc ? 'false' : 'true');

  // Re-append sorted rows
  rows.forEach(function(r) { tbody.appendChild(r); });
}

/* =============================================
   11. Cascading Select
   ============================================= */
function initCascadingSelect(gradeId, classroomId, sectionId) {
  const gradeSelect = document.getElementById(gradeId);
  const classroomSelect = document.getElementById(classroomId);
  const sectionSelect = document.getElementById(sectionId);

  if (gradeSelect && classroomSelect) {
    gradeSelect.addEventListener('change', () => {
      classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
      if (sectionSelect) sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';
      classroomSelect.innerHTML += '<option value="1">الفصل الأول</option><option value="2">الفصل الثاني</option>';
    });
  }

  if (classroomSelect && sectionSelect) {
    classroomSelect.addEventListener('change', () => {
      sectionSelect.innerHTML = '<option value="">اختر الشعبة</option>';
      sectionSelect.innerHTML += '<option value="1">أ</option><option value="2">ب</option>';
    });
  }
}

/* =============================================
   12. Print Page
   ============================================= */
function printPage() {
  window.print();
}

/* =============================================
   13. Auto aria-label for icon-only buttons (Fix #11)
   ============================================= */
function initAriaLabels() {
  document.querySelectorAll('.action-btn').forEach(function(btn) {
    if (!btn.getAttribute('aria-label') && btn.title) {
      btn.setAttribute('aria-label', btn.title);
    }
    // Hide decorative icons from screen readers
    var icon = btn.querySelector('i');
    if (icon) icon.setAttribute('aria-hidden', 'true');
  });
}