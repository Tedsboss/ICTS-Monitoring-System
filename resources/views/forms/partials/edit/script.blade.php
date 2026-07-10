<script>
  document.addEventListener('DOMContentLoaded', function () {
    const canUpdate = @json($canUpdate);
    const isUpliftBuilder = @json(request()->routeIs('uplift-builder.*'));

    let fields = @json($builderFields);
    let selectedId = fields.length ? fields[0].id : null;
    let draggedItem = null;
    let isSubmittingBuilderForm = false;
    const deletedFieldIds = new Set();
    const sectionColumnCount = 3;

    const typeLabels = {
      section: 'Section',
      integer: 'Integer',
      decimal: 'Decimal',
      text: 'Text',
      date: 'Date',
      date_range: 'Date Range',
      select: 'Select',
      boolean: 'Yes/No',
      repeating_group: 'Repeating Group',
      user_picker: 'User Picker'
    };

    const repeatingColumnTypes = isUpliftBuilder
      ? { text: 'Text' }
      : {
        text: 'Text',
        select: 'Select',
        date: 'Date'
      };

    const repeatingColumnSources = {
      user_name: 'User Name',
      designation: 'Designation',
      status: 'Status'
    };

    const canvasDropZone = document.getElementById('canvasDropZone');
    const canvasFields = document.getElementById('canvasFields');
    const canvasEmpty = document.getElementById('canvasEmpty');
    const settingsEmpty = document.getElementById('settingsEmpty');
    const settingsForm = document.getElementById('settingsForm');
    const generatedInputs = document.getElementById('builderGeneratedInputs');

    const settingLabel = document.getElementById('settingLabel');
    const settingSubtitle = document.getElementById('settingSubtitle');
    const settingValueType = document.getElementById('settingValueType');
    const settingWidthColumn = document.getElementById('settingWidthColumn');
    const settingWidthFull = document.getElementById('settingWidthFull');
    const repeatingGroupSettings = document.getElementById('repeatingGroupSettings');
    const repeatingGroupColumnsList = document.getElementById('repeatingGroupColumnsList');
    const addRepeatingGroupColumn = document.getElementById('addRepeatingGroupColumn');
    const selectOptionsSettings = document.getElementById('selectOptionsSettings');
    const settingSelectOptions = document.getElementById('settingSelectOptions');
    const settingRequiredYes = document.getElementById('settingRequiredYes');
    const settingRequiredNo = document.getElementById('settingRequiredNo');
    const settingRemarksYes = document.getElementById('settingRemarksYes');
    const settingRemarksNo = document.getElementById('settingRemarksNo');
    const settingStatusActive = document.getElementById('settingStatusActive');
    const settingStatusInactive = document.getElementById('settingStatusInactive');

    const activeCount = document.getElementById('activeCount');
    const totalCount = document.getElementById('totalCount');
    const activeCountMeta = document.getElementById('activeCountMeta');
    const totalCountMeta = document.getElementById('totalCountMeta');

    const removeFieldBtn = document.getElementById('removeFieldBtn');
    const saveFieldSettingsBtn = document.getElementById('saveFieldSettingsBtn');
    const settingsActionText = document.getElementById('settingsActionText');
    const settingsSelectedBadge = document.getElementById('settingsSelectedBadge');
    const settingsPanel = document.getElementById('settingsPanel');
    const settingsPanelContent = document.getElementById('settingsPanelContent');
    const settingsCollapseToggle = document.getElementById('settingsCollapseToggle');
    const form = document.getElementById('form-details-form');

    initBuilderSidenav();
    initSettingsCollapse();

    function initBuilderSidenav() {
      const body = document.body;
      const sidenav = document.getElementById('sidenav-main');

      if (!body || !sidenav) {
        return;
      }

      body.classList.add('form-builder-sidenav-page', 'g-sidenav-show');

      function setSidenavOpen(isOpen) {
        body.classList.toggle('g-sidenav-hidden', !isOpen);
        body.classList.toggle('g-sidenav-pinned', isOpen);
        sidenav.classList.remove('bg-transparent');
      }

      setSidenavOpen(true);

      document.querySelectorAll('.sidenav-toggler, #iconNavbarSidenav, #iconSidenav').forEach(function (toggler) {
        toggler.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          setSidenavOpen(!body.classList.contains('g-sidenav-pinned'));
        }, true);
      });
    }

    function initSettingsCollapse() {
      if (!settingsPanel || !settingsPanelContent || !settingsCollapseToggle) {
        return;
      }

      function setSettingsCollapsed(isCollapsed) {
        settingsPanel.classList.toggle('is-collapsed', isCollapsed);
        settingsPanelContent.hidden = isCollapsed;
        settingsCollapseToggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');

        const icon = settingsCollapseToggle.querySelector('i');

        if (icon) {
          icon.classList.toggle('fa-chevron-up', !isCollapsed);
          icon.classList.toggle('fa-chevron-down', isCollapsed);
        }
      }

      settingsCollapseToggle.addEventListener('click', function () {
        setSettingsCollapsed(!settingsPanel.classList.contains('is-collapsed'));
      });

      setSettingsCollapsed(false);
    }

    function makeTempId() {
      if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return 'new_' + window.crypto.randomUUID();
      }

      return 'new_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    }

    function clearDraggedItem() {
      draggedItem = null;
    }

    function isSectionDrag() {
      return draggedItem && (
        draggedItem.source === 'section' ||
        (draggedItem.source === 'palette' && draggedItem.type === 'section')
      );
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function selectedField() {
      return fields.find(field => String(field.id) === String(selectedId)) || null;
    }

    function isSection(field) {
      return field && field.value_type === 'section';
    }

    function sortFields() {
      fields.sort(function (a, b) {
        if (Number(a.row_number) !== Number(b.row_number)) {
          return Number(a.row_number) - Number(b.row_number);
        }

        return Number(a.order) - Number(b.order);
      });
    }

    function makeColumnId() {
      return 'col_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    }

    function slotOrder(row, column) {
      return ((Math.max(1, Number(row) || 1) - 1) * sectionColumnCount) + Math.max(1, Number(column) || 1);
    }

    function fieldSlot(field) {
      const order = Math.max(1, Number(field.order) || 1);

      return {
        row: Math.floor((order - 1) / sectionColumnCount) + 1,
        column: ((order - 1) % sectionColumnCount) + 1,
      };
    }

    function fieldColumnSize(field) {
      if (!field || field.value_type === 'section' || field.value_type === 'repeating_group') {
        return 12;
      }

      return Number(field.column_size) === 12 ? 12 : 4;
    }

    function repeatingGroupColumns(field) {
      if (!field || field.value_type !== 'repeating_group') {
        return [];
      }

      if (typeof field.options === 'string') {
        try {
          field.options = JSON.parse(field.options);
        } catch (error) {
          field.options = { columns: [] };
        }
      }

      if (!field.options || typeof field.options !== 'object' || Array.isArray(field.options)) {
        field.options = { columns: [] };
      }

      if (!Array.isArray(field.options.columns)) {
        field.options.columns = [];
      }

      field.options.columns = field.options.columns
        .filter(column => column && typeof column === 'object')
        .map(function (column, index) {
          const type = normalizeRepeatingColumnType(column.type);
          const source = Object.prototype.hasOwnProperty.call(repeatingColumnSources, column.source) ? column.source : '';

          return {
            id: String(column.id || makeColumnId()),
            label: String(column.label || ('Column ' + (index + 1))),
            type,
            source: type === 'select' ? source : '',
          };
        });

      if (field.options.columns.length === 0) {
        field.options.columns.push({
          id: makeColumnId(),
          label: 'Column 1',
          type: 'text',
          source: '',
        });
      }

      return field.options.columns;
    }

    function normalizeRepeatingColumnType(type) {
      const normalized = String(type || '').trim();

      return Object.prototype.hasOwnProperty.call(repeatingColumnTypes, normalized) ? normalized : 'text';
    }

    function selectOptions(field) {
      if (!field || field.value_type !== 'select') {
        return [];
      }

      if (typeof field.options === 'string') {
        try {
          field.options = JSON.parse(field.options);
        } catch (error) {
          field.options = [];
        }
      }

      if (!Array.isArray(field.options)) {
        field.options = [];
      }

      field.options = field.options
        .map(option => String(option || '').trim())
        .filter((option, index, options) => option !== '' && options.indexOf(option) === index);

      return field.options;
    }

    function syncSelectOptionInputToState(field) {
      if (!field || field.value_type !== 'select' || !settingSelectOptions) {
        return;
      }

      field.options = settingSelectOptions.value
        .split(/\r?\n/)
        .map(option => option.trim())
        .filter((option, index, options) => option !== '' && options.indexOf(option) === index);
    }

    function isFullRowField(field) {
      return fieldColumnSize(field) === 12;
    }

    function coveredOrdersFor(order, columnSize) {
      const slot = fieldSlot({ order });

      if (Number(columnSize) === 12) {
        return [1, 2, 3].map(column => slotOrder(slot.row, column));
      }

      return [Number(order)];
    }

    function coveredOrders(field) {
      return coveredOrdersFor(Number(field.order) || 1, fieldColumnSize(field));
    }

    function fieldsInVisualRow(sectionFields, row) {
      return sectionFields.filter(function (field) {
        return fieldSlot(field).row === Number(row);
      });
    }

    function visualGridColumn(sectionFields, field) {
      if (isFullRowField(field)) {
        return '1 / -1';
      }

      const slot = fieldSlot(field);
      const rowFields = fieldsInVisualRow(sectionFields, slot.row).filter(item => !isFullRowField(item));
      const hasFullRowField = fieldsInVisualRow(sectionFields, slot.row).some(item => isFullRowField(item));

      if (!hasFullRowField && rowFields.length === 2) {
        const visualIndex = rowFields.findIndex(item => String(item.id) === String(field.id));

        return visualIndex === 0 ? '1 / 7' : '7 / 13';
      }

      const start = ((slot.column - 1) * 4) + 1;

      return `${start} / ${start + 4}`;
    }

    function slotGridColumn(column) {
      const start = ((Math.max(1, Number(column) || 1) - 1) * 4) + 1;

      return `${start} / ${start + 4}`;
    }

    function visualGridRow(row) {
      return ((Math.max(1, Number(row) || 1) * 2) - 1);
    }

    function dropGridRow(row) {
      return Math.max(1, Number(row) || 1) * 2;
    }

    function getSections() {
      return fields
        .filter(field => field.value_type === 'section')
        .sort((a, b) => Number(a.row_number) - Number(b.row_number));
    }

    function getFieldsInsideSection(section) {
      return fields
        .filter(field =>
          field.value_type !== 'section' &&
          Number(field.row_number) === Number(section.row_number)
        )
        .sort((a, b) => Number(a.order) - Number(b.order));
    }

    function getSelectedSection() {
      const selected = selectedField();

      if (isSection(selected)) {
        return selected;
      }

      if (selected) {
        return fields.find(field =>
          field.value_type === 'section' &&
          Number(field.row_number) === Number(selected.row_number)
        ) || null;
      }

      const sections = getSections();

      return sections.length ? sections[sections.length - 1] : null;
    }

    function dedupeFieldsById() {
      const seen = new Set();
      fields = fields.filter(function (field) {
        const key = String(field.id);

        if (seen.has(key)) {
          return false;
        }

        seen.add(key);
        return true;
      });
    }

    function fieldIdentity(field) {
      return [
        Number(field.row_number) || 0,
        Number(field.order) || 0,
        fieldColumnSize(field),
        String(field.value_type || ''),
        String(field.label || '').trim().toLowerCase(),
        String(field.subtitle || '').trim().toLowerCase(),
        JSON.stringify(field.value_type === 'repeating_group'
          ? { columns: repeatingGroupColumns(field) }
          : (field.value_type === 'select' ? selectOptions(field) : {})
        ),
        Number(field.column_size) || 12,
      ].join('|');
    }

    function normalizePositions() {
      dedupeFieldsById();

      const rowMap = new Map();
      const sections = fields
        .filter(field => field.value_type === 'section')
        .sort(function (a, b) {
          if (Number(a.row_number) !== Number(b.row_number)) {
            return Number(a.row_number) - Number(b.row_number);
          }

          return Number(a.order) - Number(b.order);
        });

      sections.forEach(function (section, index) {
        const oldRowNumber = Number(section.row_number) || index + 1;
        const newRowNumber = index + 1;

        rowMap.set(String(oldRowNumber), newRowNumber);

        section.row_number = newRowNumber;
        section.order = 0;
        section.is_required = 0;
        section.has_remarks = 0;
      });

      fields = fields.filter(function (field) {
        if (field.value_type === 'section') {
          return true;
        }

        const sectionRowNumber = rowMap.get(String(Number(field.row_number) || 0));

        if (!sectionRowNumber) {
          return false;
        }

        field.row_number = sectionRowNumber;
        field.order = Math.max(1, Math.floor(Number(field.order) || 1));
        field.column_size = fieldColumnSize(field);

        return true;
      });

      sections.forEach(function (section) {
        const usedOrders = new Set();

        getFieldsInsideSection(section).forEach(function (field) {
          let fieldOrder = Math.max(1, Math.floor(Number(field.order) || 1));
          const columnSize = fieldColumnSize(field);

          if (columnSize === 12) {
            const slot = fieldSlot({ order: fieldOrder });
            fieldOrder = slotOrder(slot.row, 1);
          }

          while (coveredOrdersFor(fieldOrder, columnSize).some(order => usedOrders.has(order))) {
            fieldOrder++;

            if (columnSize === 12) {
              const slot = fieldSlot({ order: fieldOrder });
              fieldOrder = slotOrder(slot.row + 1, 1);
            }
          }

          field.order = fieldOrder;
          coveredOrders(field).forEach(order => usedOrders.add(order));
        });
      });

      sortFields();
    }

    function nextOpenOrder(section, startOrder = 1, columnSize = 4) {
      const usedOrders = new Set();

      getFieldsInsideSection(section).forEach(function (field) {
        coveredOrders(field).forEach(order => usedOrders.add(order));
      });

      let order = Math.max(1, Math.floor(Number(startOrder) || 1));

      if (Number(columnSize) === 12) {
        const slot = fieldSlot({ order });
        order = slotOrder(slot.row, 1);
      }

      while (coveredOrdersFor(order, columnSize).some(item => usedOrders.has(item))) {
        order++;

        if (Number(columnSize) === 12) {
          const slot = fieldSlot({ order });
          order = slotOrder(slot.row + 1, 1);
        }
      }

      return order;
    }

    function getTypeIcon(type) {
      if (type === 'section') return 'fa-folder-open';
      if (type === 'text') return 'fa-align-left';
      if (type === 'date') return 'fa-calendar';
      if (type === 'date_range') return 'fa-calendar-o';
      if (type === 'select') return 'fa-list';
      if (type === 'boolean') return 'fa-toggle-on';
      if (type === 'repeating_group') return 'fa-list-alt';
      if (type === 'user_picker') return 'fa-user';
      if (type === 'decimal') return 'fa-calculator';

      return 'fa-hashtag';
    }

    function updateCounts() {
      const active = fields.filter(field => Number(field.status) === 1).length;
      const total = fields.length;

      if (activeCount) activeCount.textContent = active;
      if (totalCount) totalCount.textContent = total;
      if (activeCountMeta) activeCountMeta.textContent = active;
      if (totalCountMeta) totalCountMeta.textContent = total;
    }

    function renderCanvas() {
      normalizePositions();

      canvasFields.innerHTML = '';

      const sections = getSections();

      sections.forEach(function (section, sectionIndex) {
        const sectionFields = getFieldsInsideSection(section);

        const sectionCard = document.createElement('div');

        sectionCard.className = 'builder-section'
          + (String(section.id) === String(selectedId) ? ' is-selected' : '')
          + (Number(section.status) === 0 ? ' is-inactive' : '');

        sectionCard.dataset.id = section.id;
        sectionCard.dataset.sectionId = section.id;

        sectionCard.innerHTML = `
          <div class="builder-section-header">
            <div class="builder-section-icon">
              <i class="fa fa-folder-open"></i>
            </div>

            <div class="builder-section-main">
              <div class="builder-section-title">
                ${escapeHtml(section.label || 'Untitled Section')}
              </div>

              ${section.subtitle ? `
                <div class="builder-section-help">
                  ${escapeHtml(section.subtitle)}
                </div>
              ` : ''}

              <div class="builder-section-meta">
                <span class="mini-badge is-blue">Section ${sectionIndex + 1}</span>
                <span class="mini-badge ${Number(section.status) === 1 ? 'is-green' : 'is-gray'}">
                  ${Number(section.status) === 1 ? 'Active' : 'Inactive'}
                </span>
                <span class="mini-badge">${sectionFields.length} field${sectionFields.length === 1 ? '' : 's'}</span>
              </div>
            </div>

            ${isUpliftBuilder && canUpdate ? `
              <div class="builder-section-actions">
                <button type="button" class="builder-section-move move-up" title="Move section up" aria-label="Move section up" ${sectionIndex === 0 ? 'disabled' : ''}>
                  <i class="fa fa-arrow-up"></i>
                </button>
                <button type="button" class="builder-section-move move-down" title="Move section down" aria-label="Move section down" ${sectionIndex === sections.length - 1 ? 'disabled' : ''}>
                  <i class="fa fa-arrow-down"></i>
                </button>
              </div>
            ` : ''}
          </div>

          <div class="section-field-list" data-section-id="${section.id}">
            ${
              sectionFields.length
                ? ''
                : `<div class="section-empty">Drag or click field types to add multiple fields here.</div>`
            }
          </div>
        `;

        sectionCard.addEventListener('click', function () {
          selectedId = section.id;
          render();
        });

        if (isUpliftBuilder && canUpdate) {
          initSectionPlacementControls(sectionCard, section);
        }

        const list = sectionCard.querySelector('.section-field-list');

        list.addEventListener('dragover', function (event) {
          if (!canUpdate) return;

          event.preventDefault();
          list.classList.add('is-dragover');
        });

        list.addEventListener('dragleave', function () {
          list.classList.remove('is-dragover');
        });

        list.addEventListener('drop', function (event) {
          if (!canUpdate) return;

          event.preventDefault();
          list.classList.remove('is-dragover');

          if (!draggedItem) {
            return;
          }

          if (isUpliftBuilder && isSectionDrag()) {
            event.stopPropagation();
            dropSectionOnSection(section.id, event);
            return;
          }

          event.stopPropagation();

          if (draggedItem.source === 'palette') {
            addFieldToSection(draggedItem.type, section.id);
            clearDraggedItem();
            return;
          }

          if (draggedItem.source === 'field') {
            moveFieldToSection(draggedItem.fieldId, section.id);
            clearDraggedItem();
          }
        });

        sectionFields.forEach(function (field) {
          const card = document.createElement('div');
          const slot = fieldSlot(field);

          card.className = 'canvas-field'
            + (String(field.id) === String(selectedId) ? ' is-selected' : '')
            + (Number(field.status) === 0 ? ' is-inactive' : '');

          card.setAttribute('draggable', canUpdate && field.value_type !== 'section' ? 'true' : 'false');
          card.dataset.id = field.id;
          card.classList.toggle('is-full-row', isFullRowField(field));
          card.style.gridColumn = visualGridColumn(sectionFields, field);
          card.style.gridRow = String(visualGridRow(slot.row));

          card.innerHTML = `
            <div class="canvas-field-icon" title="Drag to move">
              <i class="fa ${getTypeIcon(field.value_type)}"></i>
            </div>

            <div class="canvas-field-main">
              <div class="canvas-field-header">
                <div class="canvas-field-title-group">
                  <div class="canvas-field-label">
                    ${escapeHtml(fieldDisplayLabel(field))}
                    ${Number(field.is_required) === 1 ? '<span class="text-danger">*</span>' : ''}
                  </div>

                  ${field.subtitle ? `<span class="canvas-field-help">${escapeHtml(field.subtitle)}</span>` : ''}
                </div>

                <span class="canvas-field-type">
                  <i class="fa ${getTypeIcon(field.value_type)}"></i>
                  ${escapeHtml(typeLabels[normalizeFieldType(field.value_type)] || 'Field')}
                </span>
              </div>

              <div class="canvas-field-meta">
                <span class="mini-badge is-blue">Row ${slot.row}</span>
                <span class="mini-badge is-blue">${isFullRowField(field) ? 'Whole row' : 'Column ' + slot.column}</span>

                <span class="mini-badge ${Number(field.status) === 1 ? 'is-green' : 'is-gray'}">
                  ${Number(field.status) === 1 ? 'Active' : 'Inactive'}
                </span>

                ${Number(field.is_required) === 1 ? `<span class="mini-badge is-red">Required</span>` : ''}
                ${Number(field.has_remarks) === 1 ? `<span class="mini-badge">With remarks</span>` : ''}
                ${field.is_new ? `<span class="mini-badge">New</span>` : ''}
              </div>
            </div>

          `;

          card.addEventListener('click', function (event) {
            event.stopPropagation();
            selectedId = field.id;
            render();
          });

          card.addEventListener('dragstart', function (event) {
            if (!canUpdate) return;
            if (field.value_type === 'section') return;

            draggedItem = {
              source: 'field',
              fieldId: field.id,
            };

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', `field:${field.id}`);
          });

          card.addEventListener('dragend', function () {
            clearDraggedItem();
          });

          card.addEventListener('dragover', function (event) {
            if (!canUpdate) return;

            event.preventDefault();

            const sectionList = card.closest('.section-field-list');
            if (sectionList) {
              sectionList.classList.add('is-dragover');
            }
          });

          card.addEventListener('drop', function (event) {
            if (!canUpdate) return;

            event.preventDefault();
            event.stopPropagation();

            const sectionList = card.closest('.section-field-list');
            const sectionId = sectionList ? sectionList.dataset.sectionId : null;

            if (sectionList) {
              sectionList.classList.remove('is-dragover');
            }

            if (!draggedItem) {
              return;
            }

            if (draggedItem.source === 'palette') {
              if (draggedItem.type === 'section') {
                clearDraggedItem();
                return;
              }

              if (sectionId) {
                addFieldToSection(draggedItem.type, sectionId);
              }

              clearDraggedItem();
              return;
            }

            if (draggedItem.source === 'field') {
              moveDraggedFieldToSlot(draggedItem.fieldId, field.id);
              clearDraggedItem();
            }
          });

          list.appendChild(card);
        });

        renderSectionSlots(list, section, sectionFields);

        canvasFields.appendChild(sectionCard);
      });

      canvasEmpty.style.display = sections.length ? 'none' : 'flex';

      updateCounts();
    }

    function initSectionPlacementControls(sectionCard, section) {
      const moveUp = sectionCard.querySelector('.builder-section-move.move-up');
      const moveDown = sectionCard.querySelector('.builder-section-move.move-down');

      if (moveUp) {
        moveUp.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          moveSection(section.id, -1);
        });
      }

      if (moveDown) {
        moveDown.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          moveSection(section.id, 1);
        });
      }

      sectionCard.addEventListener('dragover', function (event) {
        if (!isSectionDrag()) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.dataTransfer.dropEffect = draggedItem.source === 'palette' ? 'copy' : 'move';
        showSectionDropPlacement(sectionCard, sectionDropPlacement(sectionCard, event));
      });

      sectionCard.addEventListener('dragleave', function (event) {
        if (sectionCard.contains(event.relatedTarget)) {
          return;
        }

        clearSectionDragState(sectionCard);
      });

      sectionCard.addEventListener('drop', function (event) {
        if (!isSectionDrag()) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        dropSectionOnSection(section.id, event);
      });
    }

    function sectionDropPlacement(sectionCard, event) {
      const rect = sectionCard.getBoundingClientRect();
      const middle = rect.top + (rect.height / 2);

      return event.clientY < middle ? 'before' : 'after';
    }

    function showSectionDropPlacement(sectionCard, placement) {
      clearSectionDragState(sectionCard);
      sectionCard.classList.toggle('is-section-drop-before', placement === 'before');
      sectionCard.classList.toggle('is-section-drop-after', placement === 'after');
    }

    function clearSectionDragState(exceptSectionCard = null) {
      document.querySelectorAll('.builder-section.is-section-drop-before, .builder-section.is-section-drop-after').forEach(function (sectionCard) {
        if (exceptSectionCard && sectionCard === exceptSectionCard) {
          return;
        }

        sectionCard.classList.remove('is-section-drop-before', 'is-section-drop-after');
      });
    }

    function dropSectionOnSection(targetSectionId, event) {
      if (!isUpliftBuilder || !canUpdate || !isSectionDrag()) {
        return;
      }

      const targetSectionCard = event.currentTarget.closest
        ? event.currentTarget.closest('.builder-section')
        : Array.from(document.querySelectorAll('.builder-section')).find(function (sectionCard) {
          return String(sectionCard.dataset.sectionId) === String(targetSectionId);
        });
      const placement = targetSectionCard ? sectionDropPlacement(targetSectionCard, event) : 'after';
      const item = draggedItem;

      clearSectionDragState();

      if (item.source === 'palette') {
        addSection(targetSectionId, placement);
        clearDraggedItem();
        return;
      }

      if (item.source === 'section') {
        moveSectionToPlacement(item.sectionId, targetSectionId, placement);
        clearDraggedItem();
      }
    }

    function renderRepeatingGroupColumns(field) {
      if (!repeatingGroupColumnsList) {
        return;
      }

      repeatingGroupColumnsList.innerHTML = '';

      const columns = repeatingGroupColumns(field);
      columns.forEach(function (column, index) {
        const row = document.createElement('div');
        row.className = 'repeating-column-row';
        row.dataset.columnId = column.id;

        row.innerHTML = `
          <div class="repeating-column-main">
            <label class="repeating-column-field">
              <span>Column</span>
              <input
                class="form-control builder-setting-input repeating-column-input"
                type="text"
                value="${escapeHtml(column.label)}"
                placeholder="Column label"
                ${!canUpdate ? 'disabled' : ''}
              >
            </label>

            <label class="repeating-column-field repeating-column-type-field">
              <span>Type</span>
              <select class="form-control builder-setting-input repeating-column-type" ${!canUpdate ? 'disabled' : ''}>
                ${Object.entries(repeatingColumnTypes).map(function ([value, label]) {
                  return `<option value="${escapeHtml(value)}" ${column.type === value ? 'selected' : ''}>${escapeHtml(label)}</option>`;
                }).join('')}
              </select>
            </label>

            <button type="button" class="repeating-column-remove" title="Delete column" aria-label="Delete column" ${!canUpdate || columns.length <= 1 ? 'disabled' : ''}>
              <i class="fa fa-trash"></i>
            </button>
          </div>

          <label class="repeating-column-field repeating-column-source-field ${column.type === 'select' ? '' : 'is-hidden'}">
            <span>Source</span>
            <select class="form-control builder-setting-input repeating-column-source" ${!canUpdate || column.type !== 'select' ? 'disabled' : ''}>
              <option value="">Source</option>
              ${Object.entries(repeatingColumnSources).map(function ([value, label]) {
                return `<option value="${escapeHtml(value)}" ${column.source === value ? 'selected' : ''}>${escapeHtml(label)}</option>`;
              }).join('')}
            </select>
          </label>
        `;

        row.querySelector('.repeating-column-input').addEventListener('input', function (event) {
          column.label = event.target.value;
          renderGeneratedInputs();
        });

        row.querySelector('.repeating-column-type').addEventListener('change', function (event) {
          column.type = normalizeRepeatingColumnType(event.target.value);

          if (column.type !== 'select') {
            column.source = '';
          }

          const sourceInput = row.querySelector('.repeating-column-source');
          const sourceField = row.querySelector('.repeating-column-source-field');

          if (sourceInput) {
            sourceInput.disabled = !canUpdate || column.type !== 'select';

            if (column.type !== 'select') {
              sourceInput.value = '';
            }
          }

          if (sourceField) {
            sourceField.classList.toggle('is-hidden', column.type !== 'select');
          }

          const field = selectedField();

          if (field && field.value_type === 'repeating_group') {
            syncRepeatingGroupColumnInputsToState(field);
          }

          renderGeneratedInputs();
        });

        row.querySelector('.repeating-column-source').addEventListener('change', function (event) {
          column.source = Object.prototype.hasOwnProperty.call(repeatingColumnSources, event.target.value)
            ? event.target.value
            : '';
          renderGeneratedInputs();
        });

        row.querySelector('.repeating-column-remove').addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          const field = selectedField();

          if (!field || field.value_type !== 'repeating_group' || !canUpdate) {
            return;
          }

          syncRepeatingGroupColumnInputsToState(field);

          const currentColumns = repeatingGroupColumns(field);

          if (currentColumns.length <= 1) {
            return;
          }

          const columnIndex = currentColumns.findIndex(function (item) {
            return item.id === column.id;
          });

          if (columnIndex === -1) {
            return;
          }

          currentColumns.splice(columnIndex, 1);
          field.options = { columns: currentColumns };
          renderRepeatingGroupColumns(field);
          renderGeneratedInputs();
        });

        repeatingGroupColumnsList.appendChild(row);
      });
    }

    function syncRepeatingGroupColumnInputsToState(field) {
      if (!field || field.value_type !== 'repeating_group' || !repeatingGroupColumnsList) {
        return;
      }

      const rows = Array.from(repeatingGroupColumnsList.querySelectorAll('.repeating-column-row'));

      if (!rows.length) {
        return;
      }

      const columns = repeatingGroupColumns(field);

      rows.forEach(function (row, index) {
        if (columns[index]) {
          const labelInput = row.querySelector('.repeating-column-input');
          const typeInput = row.querySelector('.repeating-column-type');
          const sourceInput = row.querySelector('.repeating-column-source');

          columns[index].label = labelInput ? labelInput.value : columns[index].label;
          columns[index].type = typeInput ? normalizeRepeatingColumnType(typeInput.value) : 'text';
          columns[index].source = columns[index].type === 'select' && sourceInput && Object.prototype.hasOwnProperty.call(repeatingColumnSources, sourceInput.value)
            ? sourceInput.value
            : '';
        }
      });

      field.options = { columns };
    }

    function renderSectionSlots(list, section, sectionFields) {
      const occupiedOrders = new Set();

      sectionFields.forEach(function (field) {
        coveredOrders(field).forEach(order => occupiedOrders.add(order));
      });

      const maxOrder = Math.max(0, ...sectionFields.map(field => Number(field.order) || 0));
      let rowCount = Math.max(1, Math.ceil(maxOrder / sectionColumnCount));

      if (sectionFields.length >= rowCount * sectionColumnCount || sectionFields.some(field => isFullRowField(field) && fieldSlot(field).row === rowCount)) {
        rowCount++;
      }

      for (let row = 1; row <= rowCount; row++) {
        const rowFields = fieldsInVisualRow(sectionFields, row);
        const hasFullRowField = rowFields.some(field => isFullRowField(field));
        const normalRowFields = rowFields.filter(field => !isFullRowField(field));
        const stretchTwoFieldRow = !hasFullRowField && normalRowFields.length === 2;

        for (let column = 1; column <= sectionColumnCount; column++) {
          const order = slotOrder(row, column);

          if (occupiedOrders.has(order) || stretchTwoFieldRow) {
            continue;
          }

          const slot = document.createElement('div');
          slot.className = 'section-slot-placeholder';
          slot.dataset.sectionId = section.id;
          slot.dataset.order = String(order);
          slot.style.gridColumn = slotGridColumn(column);
          slot.style.gridRow = String(visualGridRow(row));
          slot.innerHTML = `
            <span>Row ${row}</span>
            <strong>Column ${column}</strong>
          `;

          slot.addEventListener('dragover', function (event) {
            if (!canUpdate) return;

            event.preventDefault();
            slot.classList.add('is-dragover');
          });

          slot.addEventListener('dragleave', function () {
            slot.classList.remove('is-dragover');
          });

          slot.addEventListener('drop', function (event) {
            if (!canUpdate) return;

            event.preventDefault();
            event.stopPropagation();
            slot.classList.remove('is-dragover');

        if (draggedItem.source === 'palette' && draggedItem.type === 'section') {
          clearDraggedItem();
          return;
        }

        if (draggedItem.source === 'palette') {
          addFieldToSlot(draggedItem.type, section.id, order);
          clearDraggedItem();
          return;
        }

        if (draggedItem.source === 'field') {
          moveFieldToOrder(draggedItem.fieldId, section.row_number, order);
          clearDraggedItem();
        }
      });

          list.appendChild(slot);
        }

        renderDropBelowRow(list, section, row);
      }
    }

    function renderDropBelowRow(list, section, row) {
      const dropRow = document.createElement('div');

      dropRow.className = 'section-row-drop-target';
      dropRow.dataset.sectionId = section.id;
      dropRow.style.gridColumn = '1 / -1';
      dropRow.style.gridRow = String(dropGridRow(row));
      dropRow.innerHTML = '<span>Drop below row ' + row + '</span>';

      dropRow.addEventListener('dragover', function (event) {
        if (!canUpdate) return;

        event.preventDefault();
        dropRow.classList.add('is-dragover');
      });

      dropRow.addEventListener('dragleave', function () {
        dropRow.classList.remove('is-dragover');
      });

      dropRow.addEventListener('drop', function (event) {
        if (!canUpdate) return;

        event.preventDefault();
        event.stopPropagation();
        dropRow.classList.remove('is-dragover');

        if (draggedItem.source === 'palette' && draggedItem.type === 'section') {
          clearDraggedItem();
          return;
        }

        if (draggedItem.source === 'palette') {
          addFieldBelowRow(draggedItem.type, section.id, row);
          clearDraggedItem();
          return;
        }

        if (draggedItem.source === 'field') {
          moveFieldBelowRow(draggedItem.fieldId, section.id, row);
          clearDraggedItem();
        }
      });

      list.appendChild(dropRow);
    }

    function renderSettings() {
      const field = selectedField();

      if (!field) {
        settingsEmpty.style.display = 'block';
        settingsForm.style.display = 'none';
        if (repeatingGroupSettings) repeatingGroupSettings.style.display = 'none';
        if (selectOptionsSettings) selectOptionsSettings.style.display = 'none';
        if (removeFieldBtn) removeFieldBtn.disabled = true;
        if (saveFieldSettingsBtn) saveFieldSettingsBtn.disabled = true;
        if (settingsActionText) settingsActionText.textContent = 'Select an item to edit settings.';
        if (settingsSelectedBadge) {
          settingsSelectedBadge.style.display = 'none';
          settingsSelectedBadge.textContent = 'Selected';
        }
        return;
      }

      settingsEmpty.style.display = 'none';
      settingsForm.style.display = 'block';

      const isSelectedSection = field.value_type === 'section';

      if (removeFieldBtn) removeFieldBtn.disabled = !canUpdate;
      if (saveFieldSettingsBtn) saveFieldSettingsBtn.disabled = !canUpdate;
      if (settingsActionText) settingsActionText.textContent = isSelectedSection ? 'Save or remove the selected section.' : 'Save or remove the selected field.';
      if (settingsSelectedBadge) {
        settingsSelectedBadge.style.display = 'inline-flex';
        settingsSelectedBadge.textContent = isSelectedSection ? 'Section' : typeLabels[field.value_type] || 'Field';
      }

      settingLabel.value = field.label || '';
      settingSubtitle.value = field.subtitle || '';

      document.querySelectorAll('.field-behavior-option').forEach(function (item) {
        item.style.display = isSelectedSection ? 'none' : '';
      });

      if (!isSelectedSection) {
        const isRepeatingGroup = field.value_type === 'repeating_group';
        const isSelect = field.value_type === 'select';

        settingValueType.value = field.value_type || 'integer';
        settingValueType.disabled = !canUpdate;
        settingWidthColumn.checked = fieldColumnSize(field) !== 12;
        settingWidthFull.checked = fieldColumnSize(field) === 12;
        settingWidthColumn.disabled = !canUpdate || isRepeatingGroup;
        settingWidthFull.disabled = !canUpdate || isRepeatingGroup;

        if (repeatingGroupSettings) {
          repeatingGroupSettings.style.display = isRepeatingGroup ? '' : 'none';
        }

        if (selectOptionsSettings) {
          selectOptionsSettings.style.display = isSelect ? '' : 'none';
        }

        if (settingSelectOptions) {
          settingSelectOptions.value = selectOptions(field).join('\n');
        }

        if (isRepeatingGroup) {
          renderRepeatingGroupColumns(field);
        }
      } else if (repeatingGroupSettings) {
        repeatingGroupSettings.style.display = 'none';
        if (selectOptionsSettings) selectOptionsSettings.style.display = 'none';
      }

      settingRequiredYes.checked = Number(field.is_required) === 1;
      settingRequiredNo.checked = Number(field.is_required) === 0;

      settingRemarksYes.checked = Number(field.has_remarks) === 1;
      settingRemarksNo.checked = Number(field.has_remarks) === 0;

      settingStatusActive.checked = Number(field.status) === 1;
      settingStatusInactive.checked = Number(field.status) === 0;
    }

    function syncSelectedSettingsToState() {
      const field = selectedField();

      if (!field || !canUpdate) return;

      field.label = settingLabel ? settingLabel.value : field.label;
      field.subtitle = settingSubtitle ? settingSubtitle.value : field.subtitle;

      if (field.value_type !== 'section') {
        const nextValueType = settingValueType ? settingValueType.value : field.value_type;
        field.value_type = nextValueType;

        if (field.value_type === 'repeating_group') {
          syncRepeatingGroupColumnInputsToState(field);
        } else if (field.value_type === 'select') {
          syncSelectOptionInputToState(field);
        }

        field.column_size = fieldColumnSize(field.value_type === 'repeating_group' ? { value_type: 'repeating_group' } : {
          value_type: field.value_type,
          column_size: settingWidthFull && settingWidthFull.checked ? 12 : 4
        });
        field.options = field.value_type === 'repeating_group'
          ? { columns: repeatingGroupColumns(field) }
          : (field.value_type === 'select' ? selectOptions(field) : {});
        field.is_required = settingRequiredYes && settingRequiredYes.checked ? 1 : 0;
        field.has_remarks = settingRemarksYes && settingRemarksYes.checked ? 1 : 0;
      } else {
        field.column_size = 12;
        field.is_required = 0;
        field.has_remarks = 0;
        field.column_size = 12;
      }

      field.status = settingStatusActive && settingStatusActive.checked ? 1 : 0;
    }

    function renderGeneratedInputs() {
      syncSelectedSettingsToState();
      normalizePositions();

      generatedInputs.innerHTML = '';

      if (form) {
        form.querySelectorAll('input[data-builder-generated="1"]').forEach(function (input) {
          input.remove();
        });
      }

      const submittedIds = new Set();
      const submittedNewFields = new Set();

      fields.forEach(function (field) {
        const id = String(field.id);

        if (submittedIds.has(id)) {
          return;
        }

        submittedIds.add(id);

        const group = field.is_new ? 'new_fields' : 'fields';

        if (field.is_new) {
          const newFieldKey = fieldIdentity(field);

          if (submittedNewFields.has(newFieldKey)) {
            return;
          }

          submittedNewFields.add(newFieldKey);
        }

        const values = {
          label: fieldDisplayLabel(field),
          subtitle: field.subtitle || '',
          value_type: field.value_type || 'integer',
          options: JSON.stringify(field.value_type === 'repeating_group'
            ? { columns: repeatingGroupColumns(field) }
            : (field.value_type === 'select' ? selectOptions(field) : {})
          ),
          column_size: fieldColumnSize(field),
          row_number: field.row_number || 1,
          order: field.order || 0,
          column_size: field.value_type === 'section'
            ? 12
            : Math.max(1, Math.min(12, Number(field.column_size) || 12)),
          is_required: Number(field.is_required) === 1 ? 1 : 0,
          has_remarks: Number(field.has_remarks) === 1 ? 1 : 0,
          status: Number(field.status) === 1 ? 1 : 0,
        };

        Object.keys(values).forEach(function (key) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = `${group}[${id}][${key}]`;
          input.value = values[key];
          input.dataset.builderGenerated = '1';
          generatedInputs.appendChild(input);
        });
      });

      deletedFieldIds.forEach(function (id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_fields[]';
        input.value = id;
        input.dataset.builderGenerated = '1';
        generatedInputs.appendChild(input);
      });
    }

    function render() {
      renderCanvas();
      renderSettings();
      renderGeneratedInputs();
    }

    function updateSelectedField(key, value) {
      const field = selectedField();

      if (!field || !canUpdate) return;

      if (field.value_type === 'section' && ['value_type', 'column_size', 'is_required', 'has_remarks'].includes(key)) {
        return;
      }

      field[key] = value;

      if (field.value_type === 'section') {
        field.column_size = 12;
        field.is_required = 0;
        field.has_remarks = 0;
      }

      if (field.value_type === 'repeating_group') {
        field.column_size = 12;
        field.options = { columns: repeatingGroupColumns(field) };
      } else if (field.value_type === 'select') {
        field.options = selectOptions(field);
      }

      render();
    }

    function addSection(targetSectionId = null, placement = 'after') {
      if (!canUpdate || isSubmittingBuilderForm) return;

      const targetSection = targetSectionId
        ? fields.find(field => String(field.id) === String(targetSectionId) && field.value_type === 'section')
        : null;

      const newSection = {
        id: makeTempId(),
        is_new: true,
        label: 'New Section',
        subtitle: '',
        value_type: 'section',
        column_size: 12,
        row_number: targetSection
          ? sectionRowForPlacement(targetSection, placement)
          : getSections().length + 1,
        order: 0,
        column_size: 12,
        is_required: 0,
        has_remarks: 0,
        status: 1,
      };

      fields.push(newSection);
      selectedId = newSection.id;

      normalizePositions();
      render();
    }

    function addField(type) {
      if (!canUpdate) return;

      type = normalizeFieldType(type);

      if (!type) return;

      if (type === 'section') {
        addSection();
        return;
      }

      const section = getSelectedSection();

      if (!section) {
        alert('Please add a section first before adding fields.');
        return;
      }

      addFieldToSection(type, section.id);
    }

    function addFieldToSection(type, sectionId) {
      const section = fields.find(field => String(field.id) === String(sectionId));

      if (!section || section.value_type !== 'section') return;

      addFieldToSlot(type, sectionId, nextOpenOrder(section));
    }

    function normalizeFieldType(type) {
      const normalized = String(type || '').trim();
      return Object.prototype.hasOwnProperty.call(typeLabels, normalized) ? normalized : '';
    }

    function fieldDisplayLabel(field) {
      const label = String(field && field.label ? field.label : '').trim();

      if (label && label.toLowerCase() !== 'undefined field') {
        return label;
      }

      const type = normalizeFieldType(field ? field.value_type : '');
      return `${typeLabels[type] || 'Field'} Field`;
    }

    function addFieldToSlot(type, sectionId, preferredOrder) {
      if (!canUpdate || isSubmittingBuilderForm) return;

      type = normalizeFieldType(type);

      if (!type || type === 'section') return;

      const section = fields.find(field => String(field.id) === String(sectionId));

      if (!section || section.value_type !== 'section') return;

      const typeColumnSize = type === 'repeating_group' ? 12 : 4;
      const order = nextOpenOrder(section, 1, typeColumnSize);

      const newField = {
        id: makeTempId(),
        is_new: true,
        label: `${typeLabels[type] || 'Field'} Field`,
        subtitle: '',
        value_type: type,
        options: type === 'repeating_group'
          ? { columns: [{ id: makeColumnId(), label: 'Column 1', type: 'text', source: '' }] }
          : (type === 'select' ? [] : {}),
        column_size: typeColumnSize,
        row_number: section.row_number,
        order: nextOpenOrder(section, preferredOrder || order, typeColumnSize),
        is_required: 1,
        has_remarks: 0,
        status: 1,
      };

      fields.push(newField);
      selectedId = newField.id;

      normalizePositions();
      render();
    }

    function addFieldBelowRow(type, sectionId, row) {
      if (!canUpdate || isSubmittingBuilderForm) return;

      const section = fields.find(field => String(field.id) === String(sectionId));

      if (!section || section.value_type !== 'section') return;

      const targetRow = Math.max(1, Number(row) || 1) + 1;

      shiftSectionRowsDown(section.row_number, targetRow);
      addFieldToSlot(type, sectionId, slotOrder(targetRow, 1));
    }

    function shiftSectionRowsDown(sectionRowNumber, fromRow, excludedFieldId = null) {
      fields.forEach(function (field) {
        if (field.value_type === 'section') return;
        if (String(field.id) === String(excludedFieldId)) return;
        if (Number(field.row_number) !== Number(sectionRowNumber)) return;

        const slot = fieldSlot(field);

        if (slot.row >= Number(fromRow)) {
          field.order = Number(field.order) + sectionColumnCount;
        }
      });
    }

    function moveField(id, direction) {
      moveFieldBySlot(id, 0, direction);
    }

    function moveSection(id, direction) {
      if (!canUpdate) return;

      normalizePositions();

      const sections = getSections();
      const index = sections.findIndex(section => String(section.id) === String(id));
      const targetIndex = index + direction;

      if (index < 0 || targetIndex < 0 || targetIndex >= sections.length) return;

      const currentRow = Number(sections[index].row_number);
      const targetRow = Number(sections[targetIndex].row_number);

      fields.forEach(function (field) {
        if (Number(field.row_number) === currentRow) {
          field.row_number = targetRow;
        } else if (Number(field.row_number) === targetRow) {
          field.row_number = currentRow;
        }
      });

      selectedId = id;

      normalizePositions();
      render();
    }

    function sectionRowForPlacement(targetSection, placement) {
      const targetRow = Number(targetSection.row_number) || 1;

      return placement === 'before' ? targetRow - 0.5 : targetRow + 0.5;
    }

    function moveSectionToPlacement(sectionId, targetSectionId, placement) {
      if (!canUpdate) return;
      if (String(sectionId) === String(targetSectionId)) return;

      normalizePositions();

      const section = fields.find(item => String(item.id) === String(sectionId) && item.value_type === 'section');
      const targetSection = fields.find(item => String(item.id) === String(targetSectionId) && item.value_type === 'section');

      if (!section || !targetSection) return;

      const previousRowNumber = Number(section.row_number);
      const nextRowNumber = sectionRowForPlacement(targetSection, placement);

      fields.forEach(function (field) {
        if (Number(field.row_number) === previousRowNumber) {
          field.row_number = nextRowNumber;
        }
      });

      selectedId = section.id;

      normalizePositions();
      render();
    }

    function moveDraggedFieldToSlot(draggedId, targetId) {
      if (!draggedId || String(draggedId) === String(targetId)) return;

      normalizePositions();

      const dragged = fields.find(field => String(field.id) === String(draggedId));
      const target = fields.find(field => String(field.id) === String(targetId));

      if (!dragged || !target) return;
      if (dragged.value_type === 'section' || target.value_type === 'section') return;

      moveFieldToOrder(dragged.id, target.row_number, target.order);
    }

    function moveFieldToSection(fieldId, sectionId) {
      if (!canUpdate) return;

      normalizePositions();

      const field = fields.find(item => String(item.id) === String(fieldId));
      const section = fields.find(item => String(item.id) === String(sectionId));

      if (!field || !section) return;
      if (field.value_type === 'section' || section.value_type !== 'section') return;

      field.row_number = section.row_number;
      field.order = nextOpenOrder(section);

      selectedId = field.id;

      normalizePositions();
      render();
    }

    function moveFieldBelowRow(fieldId, sectionId, row) {
      if (!canUpdate) return;

      normalizePositions();

      const field = fields.find(item => String(item.id) === String(fieldId));
      const section = fields.find(item => String(item.id) === String(sectionId));

      if (!field || !section) return;
      if (field.value_type === 'section' || section.value_type !== 'section') return;

      const targetRow = Math.max(1, Number(row) || 1) + 1;

      shiftSectionRowsDown(section.row_number, targetRow, field.id);
      field.row_number = section.row_number;
      field.order = slotOrder(targetRow, 1);

      selectedId = field.id;

      normalizePositions();
      render();
    }

    function moveFieldBySlot(fieldId, rowDelta, columnDelta) {
      if (!canUpdate) return;

      normalizePositions();

      const field = fields.find(item => String(item.id) === String(fieldId));

      if (!field || field.value_type === 'section') return;

      const slot = fieldSlot(field);
      const targetRow = slot.row + rowDelta;
      const targetColumn = isFullRowField(field) ? 1 : slot.column + columnDelta;

      if (targetRow < 1 || targetColumn < 1 || targetColumn > sectionColumnCount || (isFullRowField(field) && columnDelta !== 0)) {
        return;
      }

      moveFieldToOrder(field.id, field.row_number, slotOrder(targetRow, targetColumn));
    }

    function moveFieldToOrder(fieldId, sectionRowNumber, targetOrder) {
      if (!canUpdate) return;

      normalizePositions();

      const field = fields.find(item => String(item.id) === String(fieldId));

      if (!field || field.value_type === 'section') return;

      if (isFullRowField(field)) {
        const slot = fieldSlot({ order: targetOrder });
        targetOrder = slotOrder(slot.row, 1);
      }

      const target = fields.find(item =>
        item.value_type !== 'section' &&
        String(item.id) !== String(field.id) &&
        Number(item.row_number) === Number(sectionRowNumber) &&
        coveredOrders(item).includes(Number(targetOrder))
      );

      const previousRowNumber = field.row_number;
      const previousOrder = field.order;

      field.row_number = sectionRowNumber;
      field.order = targetOrder;

      if (target) {
        target.row_number = previousRowNumber;
        target.order = previousOrder;
      }

      selectedId = field.id;

      normalizePositions();
      render();
    }


    function removeSelectedField() {
      if (!canUpdate) return;

      const field = selectedField();

      if (!field) return;

      let confirmationMessage = 'Remove this item?';

      if (field.value_type === 'section') {
        const sectionFields = fields.filter(item =>
          item.value_type !== 'section' &&
          Number(item.row_number) === Number(field.row_number)
        );

        if (sectionFields.length > 0) {
          confirmationMessage = 'This section has fields inside it. Removing it will also remove all fields inside this section. Continue?';
        }
      }

      const confirmed = confirm(confirmationMessage);

      if (!confirmed) return;

      if (field.value_type === 'section') {
        fields
          .filter(item => Number(item.row_number) === Number(field.row_number))
          .forEach(function (item) {
            if (!item.is_new) {
              deletedFieldIds.add(String(item.id));
            }
          });

        fields = fields.filter(item => Number(item.row_number) !== Number(field.row_number));
      } else {
        if (!field.is_new) {
          deletedFieldIds.add(String(field.id));
        }

        fields = fields.filter(item => String(item.id) !== String(field.id));
      }

      selectedId = fields.length ? fields[0].id : null;

      normalizePositions();
      render();
    }

    document.querySelectorAll('.field-type-item').forEach(function (item) {
      item.addEventListener('dragstart', function (event) {
        if (!canUpdate) return;

        draggedItem = {
          source: 'palette',
          type: item.dataset.type,
        };

        event.dataTransfer.effectAllowed = 'copy';
        event.dataTransfer.setData('text/plain', `palette:${item.dataset.type}`);
      });

      item.addEventListener('dragend', function () {
        clearDraggedItem();
      });

      item.addEventListener('click', function () {
        addField(item.dataset.type);
      });
    });

    canvasDropZone.addEventListener('dragover', function (event) {
      if (!canUpdate) return;

      event.preventDefault();
      canvasDropZone.classList.add('is-dragover');
    });

    canvasDropZone.addEventListener('dragleave', function () {
      canvasDropZone.classList.remove('is-dragover');
    });

    canvasDropZone.addEventListener('drop', function (event) {
      if (!canUpdate) return;

      event.preventDefault();
      canvasDropZone.classList.remove('is-dragover');

      if (!draggedItem) {
        return;
      }

      if (draggedItem.source === 'palette' && draggedItem.type === 'section') {
        addSection();
        clearDraggedItem();
        return;
      }

      if (draggedItem.source === 'palette') {
        const section = getSelectedSection();

        if (!section) {
          alert('Please add a section first before adding fields.');
          clearDraggedItem();
          return;
        }

        addFieldToSection(draggedItem.type, section.id);
        clearDraggedItem();
      }
    });

    settingLabel.addEventListener('input', function () {
      updateSelectedField('label', settingLabel.value);
    });

    settingSubtitle.addEventListener('input', function () {
      updateSelectedField('subtitle', settingSubtitle.value);
    });

    settingValueType.addEventListener('change', function () {
      updateSelectedField('value_type', settingValueType.value);
    });

    if (settingSelectOptions) {
      settingSelectOptions.addEventListener('input', function () {
        const field = selectedField();

        if (!field || field.value_type !== 'select' || !canUpdate) {
          return;
        }

        syncSelectOptionInputToState(field);
        renderGeneratedInputs();
      });
    }

    if (addRepeatingGroupColumn) {
      addRepeatingGroupColumn.addEventListener('click', function () {
        const field = selectedField();

        if (!field || field.value_type !== 'repeating_group' || !canUpdate) {
          return;
        }

        const columns = repeatingGroupColumns(field);
        columns.push({
          id: makeColumnId(),
          label: 'Column ' + (columns.length + 1),
          type: 'text',
          source: '',
        });

        render();
      });
    }

    document.querySelectorAll('input[name="settingWidth"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        updateSelectedField('column_size', Number(radio.value));
      });
    });

    document.querySelectorAll('input[name="settingRequired"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        updateSelectedField('is_required', Number(radio.value));
      });
    });

    document.querySelectorAll('input[name="settingRemarks"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        updateSelectedField('has_remarks', Number(radio.value));
      });
    });

    document.querySelectorAll('input[name="settingStatus"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        updateSelectedField('status', Number(radio.value));
      });
    });

    if (removeFieldBtn) {
      removeFieldBtn.addEventListener('click', removeSelectedField);
    }

    if (saveFieldSettingsBtn) {
      saveFieldSettingsBtn.addEventListener('click', function () {
        renderGeneratedInputs();
      });
    }

    form.addEventListener('submit', function (event) {
      if (isSubmittingBuilderForm) {
        event.preventDefault();
        return false;
      }

      isSubmittingBuilderForm = true;
      renderGeneratedInputs();

      form.querySelectorAll('button[type="submit"]').forEach(function (button) {
        button.disabled = true;
        button.classList.add('is-saving');

        if (!button.dataset.originalHtml) {
          button.dataset.originalHtml = button.innerHTML;
        }

        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
      });
    });
    if (document.getElementById('agency_id') && typeof initTomSelect === 'function') {
      initTomSelect('agency_id', true);

      if (window.tomSelects && window.tomSelects.agency_id) {
        const agencyTomSelect = window.tomSelects.agency_id;

        agencyTomSelect.wrapper.classList.add('agency-tomselect');
        agencyTomSelect.dropdown.classList.add('agency-tomselect-dropdown');

        agencyTomSelect.on('dropdown_open', function () {
          const searchInput = agencyTomSelect.dropdown.querySelector('.dropdown-input');

          if (searchInput) {
            searchInput.placeholder = 'Type agency name or abbreviation...';
            searchInput.focus();
          }
        });
      }
    }

    if (document.getElementById('assigned_sector_id') && typeof initTomSelect === 'function') {
      initTomSelect('assigned_sector_id', true);

      if (window.tomSelects && window.tomSelects.assigned_sector_id) {
        const sectorTomSelect = window.tomSelects.assigned_sector_id;

        const syncSectorDropdownWidth = function () {
          const wrapperWidth = sectorTomSelect.wrapper.getBoundingClientRect().width;

          sectorTomSelect.dropdown.style.width = `${wrapperWidth}px`;
          sectorTomSelect.dropdown.style.minWidth = `${wrapperWidth}px`;
          sectorTomSelect.dropdown.style.maxWidth = `${wrapperWidth}px`;
        };

        sectorTomSelect.wrapper.classList.add('sector-tomselect');
        sectorTomSelect.dropdown.classList.add('sector-tomselect-dropdown');

        sectorTomSelect.on('dropdown_open', function () {
          syncSectorDropdownWidth();

          const searchInput = sectorTomSelect.dropdown.querySelector('.dropdown-input');

          if (searchInput) {
            searchInput.placeholder = 'Type sector name or abbreviation...';
            searchInput.focus();
          }
        });

        window.addEventListener('resize', syncSectorDropdownWidth);
      }
    }

    render();
  });
</script>
