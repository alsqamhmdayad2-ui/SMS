/**
 * Marks Entry Module
 * Handles auto-saving, calculations, and UI updates for the marks entry grid.
 */
class MarksEntry extends SMS.Core.BaseModule {
    constructor(config = {}) {
        super(config);
        this.state = {
            hasUnsavedChanges: false
        };
    }

    cache() {
        this.elements = {
            table: SMS.Core.DOM.get('#marksTable'),
            indicator: SMS.Core.DOM.get('#saveIndicator'),
            statusSaved: SMS.Core.DOM.get('#statusSaved'),
            statusSaving: SMS.Core.DOM.get('#statusSaving'),
            statusError: SMS.Core.DOM.get('#statusError'),
            sumEntered: SMS.Core.DOM.get('#sumEntered'),
            sumMissing: SMS.Core.DOM.get('#sumMissing'),
            sumAvg: SMS.Core.DOM.get('#sumAvg'),
            sumHigh: SMS.Core.DOM.get('#sumHigh'),
            sumLow: SMS.Core.DOM.get('#sumLow'),
            sumPass: SMS.Core.DOM.get('#sumPass'),
            filterGrade: SMS.Core.DOM.get('#filterGrade'),
            filterClass: SMS.Core.DOM.get('#filterClass'),
            filterSection: SMS.Core.DOM.get('#filterSection'),
            btnSaveAll: SMS.Core.DOM.get('#btnSaveAll'),
            btnAllPresent: SMS.Core.DOM.get('#btnAllPresent'),
            btnAllAbsent: SMS.Core.DOM.get('#btnAllAbsent')
        };
    }

    bind() {
        // We must bind `this` for event listeners
        this.onBlur = this.onBlur.bind(this);
        this.onInput = this.onInput.bind(this);
        this.onChange = this.onChange.bind(this);
        this.onKeyDown = this.onKeyDown.bind(this);
        this.onClick = this.onClick.bind(this);
        this.onGlobalKeyDown = this.onGlobalKeyDown.bind(this);
        this.onBeforeUnload = this.onBeforeUnload.bind(this);
        this.loadClasses = this.loadClasses.bind(this);
        this.loadSections = this.loadSections.bind(this);

        if (this.elements.table) {
            this.elements.table.addEventListener('blur', this.onBlur, true);
            this.elements.table.addEventListener('input', this.onInput);
            this.elements.table.addEventListener('change', this.onChange);
            this.elements.table.addEventListener('keydown', this.onKeyDown);
            this.elements.table.addEventListener('click', this.onClick);
        }

        document.addEventListener('keydown', this.onGlobalKeyDown);
        window.addEventListener('beforeunload', this.onBeforeUnload);

        if (this.elements.filterGrade) {
            this.elements.filterGrade.addEventListener('change', this.loadClasses);
        }
        if (this.elements.filterClass) {
            this.elements.filterClass.addEventListener('change', this.loadSections);
        }

        if (this.elements.btnSaveAll) {
            this.elements.btnSaveAll.addEventListener('click', () => this.saveAll());
        }
        if (this.elements.btnAllPresent) {
            this.elements.btnAllPresent.addEventListener('click', () => this.markAllStatus('present'));
        }
        if (this.elements.btnAllAbsent) {
            this.elements.btnAllAbsent.addEventListener('click', () => this.markAllStatus('absent'));
        }
    }

    unbind() {
        if (this.elements.table) {
            this.elements.table.removeEventListener('blur', this.onBlur, true);
            this.elements.table.removeEventListener('input', this.onInput);
            this.elements.table.removeEventListener('change', this.onChange);
            this.elements.table.removeEventListener('keydown', this.onKeyDown);
            this.elements.table.removeEventListener('click', this.onClick);
        }
        document.removeEventListener('keydown', this.onGlobalKeyDown);
        window.removeEventListener('beforeunload', this.onBeforeUnload);

        if (this.elements.filterGrade) {
            this.elements.filterGrade.removeEventListener('change', this.loadClasses);
        }
        if (this.elements.filterClass) {
            this.elements.filterClass.removeEventListener('change', this.loadSections);
        }
    }

    // ─── Event Handlers ────────────────────────────────────────────────────────

    onBlur(e) {
        if (e.target.matches('.mark-input') || e.target.matches('.remarks-input')) {
            this.saveRow(e.target.closest('tr').dataset.studentId);
        }
    }

    onInput(e) {
        if (e.target.matches('.mark-input')) {
            this.state.hasUnsavedChanges = true;
            this.validateInput(e.target);
            this.updateRowUI(e.target.closest('tr'));
            this.refresh();
        } else if (e.target.matches('.remarks-input')) {
            this.state.hasUnsavedChanges = true;
        }
    }

    onChange(e) {
        if (e.target.matches('.status-select')) {
            this.state.hasUnsavedChanges = true;
            this.handleStatusChange(e.target);
        }
    }

    onClick(e) {
        if (e.target.closest('.btn-reset-mark')) {
            const btn = e.target.closest('.btn-reset-mark');
            this.handleResetMark(btn);
        }
    }

    onKeyDown(e) {
        if (e.target.matches('.mark-input')) {
            this.handleKeyboardNavigation(e, e.target);
        }
    }

    onGlobalKeyDown(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            this.saveAll();
        }
    }

    onBeforeUnload(e) {
        if (this.state.hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    }

    afterMount() {
        // Wire up the Bootstrap modal confirm button
        const confirmBtn = document.getElementById('btnConfirmResetMark');
        const modalEl   = document.getElementById('resetMarkModal');

        if (confirmBtn && modalEl) {
            confirmBtn.addEventListener('click', () => {
                // Hide modal first
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                // Execute the actual delete
                if (this._pendingResetStudentId && this._pendingResetRow) {
                    this._executeResetMark(this._pendingResetStudentId, this._pendingResetRow);
                    this._pendingResetStudentId = null;
                    this._pendingResetRow = null;
                }
            });

            // Clear pending state if modal is dismissed without confirming
            modalEl.addEventListener('hidden.bs.modal', () => {
                this._pendingResetStudentId = null;
                this._pendingResetRow = null;
            });
        }

        // Run initial stats
        this.refresh();
    }

    // ─── Core Logic ────────────────────────────────────────────────────────────

    handleKeyboardNavigation(e, currentInput) {
        const inputs = SMS.Core.DOM.getAll('.mark-input:not([disabled])', this.elements.table);
        const idx = inputs.indexOf(currentInput);
        
        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            if (inputs[idx + 1]) inputs[idx + 1].focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (inputs[idx - 1]) inputs[idx - 1].focus();
        }
    }

    handleStatusChange(select) {
        const row = select.closest('tr');
        const studentId = row.dataset.studentId;
        const markInput = row.querySelector('.mark-input');
        
        if (select.value !== 'present') {
            markInput.value = '';
            markInput.disabled = true;
        } else {
            markInput.disabled = false;
            markInput.focus();
        }
        
        this.updateRowUI(row);
        this.refresh();
        this.saveRow(studentId);
    }

    handleResetMark(btn) {
        const row = btn.closest('tr');
        const studentId = row.dataset.studentId;

        if (!this.config.deleteUrl) {
            alert('Delete URL is not configured.');
            return;
        }

        // Store pending action data and show modal
        this._pendingResetStudentId = studentId;
        this._pendingResetRow = row;

        const modal = document.getElementById('resetMarkModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        } else {
            // Fallback if modal not present
            if (!confirm('هل أنت متأكد من حذف درجة هذا الطالب وإعادة تعيين اختباره لكي يتمكن من تقديمه مرة أخرى؟')) return;
            this._executeResetMark(studentId, row);
        }
    }

    _executeResetMark(studentId, row) {
        this.showStatus('saving');

        fetch(this.config.deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || this.config.csrf
            },
            body: JSON.stringify({ exam_id: this.config.examId, student_id: studentId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success === true) {
                this.showStatus('saved');

                // Reset Row UI
                const markInput = row.querySelector('.mark-input');
                if (markInput) { markInput.value = ''; markInput.disabled = false; }
                const statusSelect = row.querySelector('.status-select');
                if (statusSelect) statusSelect.value = 'present';
                const remarksInput = row.querySelector('.remarks-input');
                if (remarksInput) remarksInput.value = '';
                const pctCell = row.querySelector('.pct-cell');
                if (pctCell) pctCell.textContent = '-';
                const gradeCell = row.querySelector('.grade-cell');
                if (gradeCell) gradeCell.textContent = '-';

                this.colorRow(row, null);
                this.refresh();

                row.classList.add('row-saved');
                setTimeout(() => row.classList.remove('row-saved'), 1500);
            } else {
                this.showStatus('error');
                alert(data.message || 'حدث خطأ أثناء الحذف.');
            }
        })
        .catch(err => {
            this.showStatus('error');
            console.error('Delete error:', err);
        });
    }

    validateInput(input) {
        const val = parseFloat(input.value);
        if (val > this.config.totalMarks) {
            input.classList.add('is-invalid');
            input.title = 'الحد الأقصى للدرجة هو ' + this.config.totalMarks;
        } else {
            input.classList.remove('is-invalid');
            input.title = '';
        }
    }

    saveRow(studentId) {
        const row = document.getElementById('row-' + studentId);
        if (!row) return;

        const markInput = row.querySelector('.mark-input');
        const statusSelect = row.querySelector('.status-select');
        const remarksInput = row.querySelector('.remarks-input');

        const mark = markInput.value !== '' ? parseFloat(markInput.value) : null;
        if (mark !== null && mark > this.config.totalMarks) {
            SMS.Core.Notifier.warning('توجد درجة تتجاوز الحد الأقصى');
            return;
        }

        this.showStatus('saving');
        
        const payload = {
            exam_id: this.config.examId,
            student_id: studentId,
            marks_obtained: mark,
            attendance_status: statusSelect.value,
            remarks: remarksInput ? remarksInput.value : ''
        };

        SMS.Core.Http.post(this.config.saveUrl, payload)
            .then(data => {
                if (data.success) {
                    this.state.hasUnsavedChanges = false;
                    this.showStatus('saved');
                    row.classList.add('row-saved');
                    row.classList.remove('row-error');
                    setTimeout(() => row.classList.remove('row-saved'), 1500);
                    
                    if (data.data && data.data.percentage !== null) {
                        this.applyServerResultToRow(row, data.data);
                    }
                    SMS.Events.emit(SMS.Events.Types.MARK_SAVED, { studentId: studentId });
                }
            })
            .catch(() => {
                this.showStatus('error');
                row.classList.add('row-error');
            });
    }

    applyServerResultToRow(row, data) {
        const pctCell = row.querySelector('.pct-cell');
        const gradeCell = row.querySelector('.grade-cell');
        
        if (pctCell) pctCell.textContent = data.percentage + '%';
        if (gradeCell) {
            const letter = data.letter_grade;
            const passing = data.is_passing;
            gradeCell.innerHTML = letter ? `<span class="badge ${passing ? 'bg-success' : 'bg-danger'}">${letter}</span>` : '-';
        }
        this.colorRow(row, data.percentage);
        this.refresh();
    }

    updateRowUI(row) {
        const markInput = row.querySelector('.mark-input');
        const pctCell = row.querySelector('.pct-cell');
        const gradeCell = row.querySelector('.grade-cell');
        const val = parseFloat(markInput.value);

        if (!isNaN(val) && this.config.totalMarks > 0) {
            const pct = Math.round((val / this.config.totalMarks) * 10000) / 100;
            if(pctCell) pctCell.textContent = pct + '%';
            
            const scale = this.config.scales.find(s => pct >= parseFloat(s.percentage_from) && pct <= parseFloat(s.percentage_to));
            if (scale && gradeCell) {
                gradeCell.innerHTML = `<span class="badge ${scale.is_passing ? 'bg-success' : 'bg-danger'}">${scale.letter_grade}</span>`;
            }
            this.colorRow(row, pct);
        } else {
            if(pctCell) pctCell.textContent = '-';
            if(gradeCell) gradeCell.textContent = '-';
            this.colorRow(row, null);
        }
    }

    colorRow(row, pct) {
        row.classList.remove('grade-excellent','grade-good','grade-average','grade-fail');
        if (pct === null) return;
        if (pct >= 90) row.classList.add('grade-excellent');
        else if (pct >= 75) row.classList.add('grade-good');
        else if (pct >= 60) row.classList.add('grade-average');
        else row.classList.add('grade-fail');
    }

    showStatus(type) {
        if(!this.elements.indicator) return;

        this.elements.indicator.style.display = 'flex';
        this.elements.indicator.style.setProperty('display', 'flex', 'important');
        
        if(this.elements.statusSaved) this.elements.statusSaved.style.display = type === 'saved' ? 'inline' : 'none';
        if(this.elements.statusSaving) this.elements.statusSaving.style.display = type === 'saving' ? 'inline' : 'none';
        if(this.elements.statusError) this.elements.statusError.style.display = type === 'error' ? 'inline' : 'none';
        
        if (type === 'saved') {
            setTimeout(() => { 
                this.elements.indicator.style.setProperty('display','none','important'); 
            }, 3000);
        }
    }

    refresh() {
        if(!this.elements.table) return;

        const rows = SMS.Core.DOM.getAll('.mark-row', this.elements.table);
        if(rows.length === 0) return;

        let entered = 0, total = rows.length, marks = [], passing = 0;
        
        rows.forEach(row => {
            const input = row.querySelector('.mark-input');
            const statusSelect = row.querySelector('.status-select');
            
            if (statusSelect && statusSelect.value !== 'present') { 
                entered++; 
                return; 
            }
            
            if (input) {
                const val = parseFloat(input.value);
                if (!isNaN(val)) { 
                    entered++; 
                    marks.push(val); 
                }
            }
        });

        marks.forEach(m => {
            const pct = (m / this.config.totalMarks) * 100;
            const scale = this.config.scales.find(s => pct >= parseFloat(s.percentage_from) && pct <= parseFloat(s.percentage_to));
            if (scale && scale.is_passing) passing++;
        });

        if(this.elements.sumEntered) this.elements.sumEntered.textContent = entered;
        if(this.elements.sumMissing) this.elements.sumMissing.textContent = total - entered;
        
        if (marks.length > 0) {
            const avg = marks.reduce((a,b) => a+b, 0) / marks.length;
            if(this.elements.sumAvg) this.elements.sumAvg.textContent = avg.toFixed(1);
            if(this.elements.sumHigh) this.elements.sumHigh.textContent = Math.max(...marks);
            if(this.elements.sumLow) this.elements.sumLow.textContent = Math.min(...marks);
            if(this.elements.sumPass) this.elements.sumPass.textContent = Math.round((passing / marks.length) * 100) + '%';
        } else {
            if(this.elements.sumAvg) this.elements.sumAvg.textContent = '-';
            if(this.elements.sumHigh) this.elements.sumHigh.textContent = '-';
            if(this.elements.sumLow) this.elements.sumLow.textContent = '-';
            if(this.elements.sumPass) this.elements.sumPass.textContent = '-';
        }
    }

    markAllStatus(statusValue) {
        if(!this.elements.table) return;
        SMS.Core.DOM.getAll('.status-select', this.elements.table).forEach(sel => {
            sel.value = statusValue;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    saveAll() {
        if(!this.elements.table) return;
        SMS.Core.DOM.getAll('.mark-input', this.elements.table).forEach(input => {
            if (!input.disabled) {
                input.dispatchEvent(new Event('blur', { bubbles: true }));
            }
        });
    }

    loadClasses(e) {
        const gradeId = e.target.value;
        const url = e.target.dataset.url;
        const classSelect = this.elements.filterClass;
        const sectionSelect = this.elements.filterSection;
        
        if (!classSelect) return;

        classSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        if (sectionSelect) sectionSelect.innerHTML = '<option value="">-- اختر --</option>';

        if (!gradeId || !url) { 
            classSelect.innerHTML = '<option value="">-- اختر --</option>'; 
            return; 
        }

        SMS.Core.Http.get(`${url}?grade_id=${gradeId}`)
            .then(response => {
                const data = response.data || response;
                classSelect.innerHTML = '<option value="">-- اختر --</option>';
                data.forEach(c => {
                    classSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            })
            .catch(() => {
                classSelect.innerHTML = '<option value="">-- اختر --</option>';
            });
    }

    loadSections(e) {
        const classId = e.target.value;
        const url = e.target.dataset.url;
        const sectionSelect = this.elements.filterSection;
        if (!sectionSelect) return;

        sectionSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        if (!classId || !url) { 
            sectionSelect.innerHTML = '<option value="">-- اختر --</option>'; 
            return; 
        }

        SMS.Core.Http.get(`${url}?class_id=${classId}`)
            .then(response => {
                const data = response.data || response;
                sectionSelect.innerHTML = '<option value="">-- اختر --</option>';
                data.forEach(s => {
                    sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                });
            })
            .catch(() => {
                sectionSelect.innerHTML = '<option value="">-- اختر --</option>';
            });
    }
}

// Register Module
SMS.Modules.MarksEntry = MarksEntry;
