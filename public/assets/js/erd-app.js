// =============================================
// ERD Rendering Logic (Vanilla JS & SVG)
// =============================================

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('erd-container');
    const svg = document.getElementById('erd-svg');
    const linesGroup = document.getElementById('lines-group');
    
    // Zoom & Pan Variables
    let isDraggingCanvas = false;
    let startX = 0, startY = 0;
    let translateX = 0, translateY = 0;
    let scale = 1;

    // Draggable Table Variables
    let activeTable = null;
    let tableOffsetX = 0, tableOffsetY = 0;

    // Calculate dynamic dimensions
    const fieldHeight = 25;
    const headerHeight = 35;
    const tableWidth = 220;

    // 1. Initialize Canvas
    function init() {
        renderTables();
        updateLines();
        setupCanvasEvents();
        createLegend();
    }

    // 2. Render Tables as HTML elements over SVG
    function renderTables() {
        const tablesWrapper = document.getElementById('tables-wrapper');
        tablesWrapper.innerHTML = '';

        TABLES.forEach(table => {
            const tableHeight = headerHeight + (table.fields.length * fieldHeight);
            
            const groupInfo = TABLE_GROUPS.find(g => g.id === table.group) || TABLE_GROUPS[0];
            
            const tableEl = document.createElement('div');
            tableEl.className = 'erd-table';
            tableEl.id = `table-${table.name}`;
            tableEl.style.left = `${table.x}px`;
            tableEl.style.top = `${table.y}px`;
            tableEl.style.width = `${tableWidth}px`;
            tableEl.style.borderColor = groupInfo.color;

            // Header
            const header = document.createElement('div');
            header.className = 'erd-table-header';
            header.style.backgroundColor = groupInfo.color;
            header.innerHTML = `<span><i class="fas ${groupInfo.icon}"></i> ${table.label}</span><small>${table.name}</small>`;
            
            // Drag Event on Header
            header.addEventListener('mousedown', (e) => startDragTable(e, table));

            tableEl.appendChild(header);

            // Fields
            const fieldsContainer = document.createElement('div');
            fieldsContainer.className = 'erd-table-fields';
            
            table.fields.forEach((field, idx) => {
                const fieldEl = document.createElement('div');
                fieldEl.className = `erd-field ${field.k ? 'has-key' : ''}`;
                fieldEl.id = `field-${table.name}-${field.n}`;
                
                let keyBadge = '';
                if(field.k) {
                    const badgeClass = field.k.includes('PK') ? 'pk' : (field.k.includes('FK') ? 'fk' : 'other');
                    keyBadge = `<span class="key-badge ${badgeClass}">${field.k}</span>`;
                }

                fieldEl.innerHTML = `
                    <span class="field-name">${field.n}</span>
                    <span class="field-type">${field.t}</span>
                    ${keyBadge}
                `;
                fieldsContainer.appendChild(fieldEl);
            });

            tableEl.appendChild(fieldsContainer);
            tablesWrapper.appendChild(tableEl);
        });
    }

    // 3. Update Relationship Lines
    function updateLines() {
        linesGroup.innerHTML = '';
        
        RELATIONSHIPS.forEach(rel => {
            const [fromTable, fromField, toTable, toField, cardinality] = rel;
            
            const fromEl = document.getElementById(`field-${fromTable}-${fromField}`);
            const toEl = document.getElementById(`field-${toTable}-${toField}`);
            const fromTableData = TABLES.find(t => t.name === fromTable);
            const toTableData = TABLES.find(t => t.name === toTable);

            if (!fromEl || !toEl || !fromTableData || !toTableData) return;

            // Get positions relative to canvas
            const fromRect = fromEl.getBoundingClientRect();
            const toRect = toEl.getBoundingClientRect();
            const canvasRect = document.getElementById('tables-wrapper').getBoundingClientRect();

            // Calculate anchor points (center right of from, center left of to)
            // Need to account for current zoom/pan
            let x1 = (fromRect.right - canvasRect.left) / scale;
            let y1 = (fromRect.top + fromRect.height / 2 - canvasRect.top) / scale;
            let x2 = (toRect.left - canvasRect.left) / scale;
            let y2 = (toRect.top + toRect.height / 2 - canvasRect.top) / scale;

            // Swap if "to" is to the left of "from"
            if (fromTableData.x > toTableData.x) {
                x1 = (fromRect.left - canvasRect.left) / scale;
                x2 = (toRect.right - canvasRect.left) / scale;
            }

            // Draw cubic bezier curve
            const dx = Math.abs(x2 - x1);
            const cpOffset = dx * 0.4 + 20;

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', `M ${x1} ${y1} C ${x1 + cpOffset} ${y1}, ${x2 - cpOffset} ${y2}, ${x2} ${y2}`);
            path.setAttribute('class', 'erd-line');
            path.setAttribute('marker-end', 'url(#arrow)');
            
            // Highlight table groups
            const groupInfo = TABLE_GROUPS.find(g => g.id === fromTableData.group);
            if(groupInfo) path.setAttribute('stroke', groupInfo.color);

            linesGroup.appendChild(path);
        });
    }

    // 4. Drag & Drop Table Logic
    function startDragTable(e, table) {
        e.stopPropagation();
        activeTable = table;
        const tableEl = document.getElementById(`table-${table.name}`);
        
        // Calculate offset based on scale
        tableOffsetX = (e.clientX - tableEl.getBoundingClientRect().left) / scale;
        tableOffsetY = (e.clientY - tableEl.getBoundingClientRect().top) / scale;
        
        tableEl.style.zIndex = 100;
        
        document.addEventListener('mousemove', dragTable);
        document.addEventListener('mouseup', endDragTable);
    }

    function dragTable(e) {
        if (!activeTable) return;
        
        const canvasRect = document.getElementById('tables-wrapper').getBoundingClientRect();
        
        const x = (e.clientX - canvasRect.left) / scale - tableOffsetX;
        const y = (e.clientY - canvasRect.top) / scale - tableOffsetY;
        
        activeTable.x = x;
        activeTable.y = y;
        
        const tableEl = document.getElementById(`table-${activeTable.name}`);
        tableEl.style.left = `${x}px`;
        tableEl.style.top = `${y}px`;
        
        updateLines();
    }

    function endDragTable() {
        if (activeTable) {
            const tableEl = document.getElementById(`table-${activeTable.name}`);
            tableEl.style.zIndex = '';
            activeTable = null;
        }
        document.removeEventListener('mousemove', dragTable);
        document.removeEventListener('mouseup', endDragTable);
    }

    // 5. Canvas Zoom & Pan Logic
    function setupCanvasEvents() {
        const container = document.getElementById('erd-container');
        const transformGroup = document.getElementById('transform-group');

        container.addEventListener('mousedown', (e) => {
            if (e.target.closest('.erd-table')) return;
            isDraggingCanvas = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
            container.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDraggingCanvas) return;
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            applyTransform();
        });

        document.addEventListener('mouseup', () => {
            isDraggingCanvas = false;
            container.style.cursor = 'grab';
        });

        container.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomAmount = e.deltaY > 0 ? 0.9 : 1.1;
            
            // Limit zoom
            const newScale = scale * zoomAmount;
            if (newScale < 0.2 || newScale > 3) return;

            // Zoom towards mouse position
            const rect = container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            translateX = mouseX - (mouseX - translateX) * zoomAmount;
            translateY = mouseY - (mouseY - translateY) * zoomAmount;
            scale = newScale;

            applyTransform();
        });
        
        // Setup control buttons
        document.getElementById('btn-zoom-in').addEventListener('click', () => {
            scale = Math.min(scale * 1.2, 3); applyTransform();
        });
        document.getElementById('btn-zoom-out').addEventListener('click', () => {
            scale = Math.max(scale * 0.8, 0.2); applyTransform();
        });
        document.getElementById('btn-reset').addEventListener('click', () => {
            scale = 1; translateX = 0; translateY = 0; applyTransform();
        });
    }

    function applyTransform() {
        const wrapper = document.getElementById('tables-wrapper');
        const transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
        wrapper.style.transform = transform;
        svg.style.transform = transform;
        updateLines(); // Re-calculate lines based on new scale
    }

    // 6. Create Legend
    function createLegend() {
        const legend = document.getElementById('erd-legend');
        TABLE_GROUPS.forEach(group => {
            const item = document.createElement('div');
            item.className = 'legend-item';
            item.innerHTML = `
                <span class="legend-color" style="background-color: ${group.color}"></span>
                <span>${group.label}</span>
            `;
            // Add filtering capability
            item.addEventListener('click', () => {
                const tables = document.querySelectorAll('.erd-table');
                tables.forEach(t => {
                    if (TABLES.find(tbl => tbl.name === t.id.replace('table-', '')).group !== group.id) {
                        t.style.opacity = t.style.opacity === '0.1' ? '1' : '0.1';
                    } else {
                        t.style.opacity = '1';
                    }
                });
            });
            legend.appendChild(item);
        });
    }

    // Run
    init();
});
