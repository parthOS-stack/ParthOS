
import DevOSAlert from './alerts.js';
import DevOSNotifications from './notifications.js';
import DevOSSounds from './sounds.js';
import DevOSSidebar from './sidebar.js';
import LogoutSwipe from './logout-swipe.js';
import DevOSHourglass from './hourglass-loader.js';
import Transactions from './transactions.js';

const DailyOps = (() => {
    const STATUS_MAP = {
        todo: { text: 'Todo', icon: '○' },
        backlog: { text: 'Backlog', icon: '○' },
        in_progress: { text: 'In Progress', icon: '○' },
        done: { text: 'Done', icon: '✓' },
        canceled: { text: 'Canceled', icon: '○' },
    };

    const PRIORITY_MAP = {
        low: { text: 'Low', icon: '🟢' },
        medium: { text: 'Medium', icon: '🟡' },
        high: { text: 'High', icon: '🔴' },
        urgent: { text: 'Urgent', icon: '🚨' },
    };

    const state = {
        search: '',
        status: [],
        priority: [],
        initialized: false,
        debounceTimer: null,
        abortController: null,
        isUpdatingStatus: false,
        isCopyingTask: false,
        isSubmittingTask: false,
        editingTaskId: null,
        isBulkBusy: false,
        bulkBusyAction: null,
        isDeletingTask: false,
        activeTaskId: null,
        activeTaskTitle: '',
        activeTaskStatus: '',
        activeTaskRow: null,
        mockNotif: {},
        bulkMenuDragged: false,
        bulkMenuDismissed: false,
        bulkDrag: null,
        bulkFlyoutTimer: null,
        projectId: null,
        projectsLoaded: false,
        statusCounts: null,
        priorityCounts: null,
    };

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/\n/g, ' ');
    }

    function showToast(message) {
        if (window.DevOSAlert) {
            window.DevOSAlert.success('done successfully :)', message);
        } else {
            const toast = document.createElement('div');
            toast.className = 'dailyops-toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }
        window.DevOSNotifications?.refresh();
    }

    function showError(message) {
        if (window.DevOSAlert) {
            window.DevOSAlert.error('Something went wrong', message || 'Please try again.');
            return;
        }
        showToast(message || 'Something went wrong. Please try again.');
    }

    function syncQueryStateFromDom() {
        const searchInput = document.getElementById('doSearchInput');
        state.search = searchInput ? searchInput.value.trim() : '';

        state.status = [];
        const statusFilter = document.getElementById('doStatusFilter');
        if (statusFilter) {
            statusFilter.querySelectorAll('input[type="checkbox"]:checked').forEach((cb) => {
                state.status.push(cb.value);
            });
        }

        state.priority = [];
        const priorityFilter = document.getElementById('doPriorityFilter');
        if (priorityFilter) {
            priorityFilter.querySelectorAll('input[type="checkbox"]:checked').forEach((cb) => {
                state.priority.push(cb.value);
            });
        }
    }

    function buildQueryString() {
        const params = new URLSearchParams();
        if (state.search) params.append('search', state.search);
        state.status.forEach((s) => params.append('status[]', s));
        state.priority.forEach((p) => params.append('priority[]', p));
        if (state.projectId) params.append('project_id', String(state.projectId));
        const qs = params.toString();
        return qs ? `?${qs}` : '';
    }

    function taskMatchesFilters(task) {
        if (state.status.length && !state.status.includes(task.status)) {
            return false;
        }
        if (state.priority.length && !state.priority.includes(task.priority)) {
            return false;
        }
        if (state.search) {
            const q = state.search.toLowerCase();
            const haystack = [task.task_key, task.title, task.description, task.category]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
            if (!haystack.includes(q)) return false;
        }
        return true;
    }

    function getUiRefs() {
        return {
            container: document.querySelector('.dailyops-task-container'),
            emptyState: document.querySelector('.dailyops-empty-state'),
            loader: document.getElementById('doLoadingState'),
            tbody: document.querySelector('.dailyops-task-table tbody'),
            emptyTitle: document.querySelector('.dailyops-empty-state p.font-semibold'),
            emptySub: document.querySelector('.dailyops-empty-state p.text-\\[var\\(--color-dp-text-muted\\)\\]'),
        };
    }

    function setListVisibility({ loading = false, empty = false, hasTasks = false, filteredEmpty = false } = {}) {
        const { container, emptyState, loader, emptyTitle, emptySub } = getUiRefs();
        if (loader) loader.style.display = loading ? 'flex' : 'none';
        if (container) container.style.display = hasTasks ? 'block' : 'none';
        if (emptyState) emptyState.style.display = empty ? 'flex' : 'none';

        if (empty && emptyTitle) {
            if (filteredEmpty) {
                emptyTitle.textContent = 'No tasks match your filters.';
                if (emptySub) emptySub.textContent = '';
            } else {
                emptyTitle.textContent = 'No tasks yet';
                if (emptySub) emptySub.textContent = 'Start by adding your first task for today.';
            }
        }
    }

    function updateEmptyStateIfNeeded() {
        const { tbody } = getUiRefs();
        const hasRows = tbody && tbody.children.length > 0;
        if (hasRows) {
            setListVisibility({ hasTasks: true });
            return;
        }
        const filtered = Boolean(state.search || state.status.length || state.priority.length);
        setListVisibility({ empty: true, filteredEmpty: filtered });
    }

    function buildTaskRowHtml(task) {
        const s = STATUS_MAP[task.status] || STATUS_MAP.todo;
        const p = PRIORITY_MAP[task.priority] || PRIORITY_MAP.medium;
        const title = task.title || '';
        const safeTitleAttr = escapeAttr(title);
        const safeTitle = escapeHtml(title);
        const desc = (task.description || '').trim();
        const safeDescAttr = escapeAttr(desc);
        const safeDesc = escapeHtml(desc);
        const taskKey = escapeHtml(task.task_key || '');
        const status = escapeAttr(task.status || 'todo');
        const id = Number(task.id);
        const projectLabel = escapeHtml(task.project_label || (task.project_id ? (task.project_name || 'Project') : 'Personal'));
        const isPersonal = !task.project_id;

        return `
            <tr class="dailyops-task-row" data-task-id="${id}" data-status="${status}" data-priority="${escapeAttr(task.priority || 'medium')}" data-project-id="${task.project_id ? Number(task.project_id) : ''}" data-notification="${task.notification_enabled ? '1' : '0'}">
                <td class="do-col-check">
                    <div class="checkbox-pro-container" style="font-size: 11.25px;">
                        <input type="checkbox" class="dailyops-task-row-checkbox dailyops-task-checkbox" aria-label="Select task">
                        <div class="checkmark"></div>
                    </div>
                </td>
                <td class="do-col-task">
                    <span class="dailyops-task-id">${taskKey}</span>
                </td>
                <td class="do-col-title">
                    <div class="dailyops-title-wrap">
                        <span class="dailyops-task-title-text" title="${safeTitleAttr}">${safeTitle}</span>
                        ${desc
                            ? `<span class="dailyops-task-desc-text" title="${safeDescAttr}">${safeDesc}</span>`
                            : `<span class="dailyops-task-desc-text is-empty" hidden></span>`}
                    </div>
                </td>
                <td class="do-col-project">
                    <span class="dailyops-project-pill ${isPersonal ? 'is-personal' : 'is-project'}" title="${projectLabel}">${projectLabel}</span>
                </td>
                <td class="do-col-status">
                    <div class="dailyops-task-status-cell">
                        <span class="do-status-ic">${s.icon}</span>
                        <span class="do-status-text">${s.text}</span>
                    </div>
                </td>
                <td class="do-col-priority">
                    <div class="dailyops-task-priority-cell">
                        <span class="do-priority-ic">${p.icon}</span>
                        <span class="do-priority-text">${p.text}</span>
                    </div>
                </td>
                <td class="do-col-actions">
                    <button
                        type="button"
                        class="dailyops-task-actions-btn"
                        aria-label="Task actions"
                        data-task-id="${id}"
                        data-task-title="${safeTitleAttr}"
                        data-task-status="${status}"
                    >⋮</button>
                </td>
            </tr>
        `;
    }

    function renderTasks(tasks) {
        const { tbody } = getUiRefs();
        if (!tbody) return;

        if (!tasks || tasks.length === 0) {
            tbody.innerHTML = '';
            const filtered = Boolean(state.search || state.status.length || state.priority.length);
            setListVisibility({ empty: true, filteredEmpty: filtered });
            syncSelectionUI();
            return;
        }

        const html = tasks.map((task) => buildTaskRowHtml(task)).join('');
        tbody.innerHTML = html;
        setListVisibility({ hasTasks: true });
        syncSelectionUI();
    }

    function updateFilterCounts(counts, projectProgress = null) {
        if (counts) {
            if (counts.status) {
                state.statusCounts = counts.status;
                Object.keys(counts.status).forEach((key) => {
                    const el = document.querySelector(`[data-status-count="${key}"]`);
                    if (el) el.textContent = String(counts.status[key] ?? 0);
                });
            }
            if (counts.priority) {
                state.priorityCounts = counts.priority;
                Object.keys(counts.priority).forEach((key) => {
                    const el = document.querySelector(`[data-priority-count="${key}"]`);
                    if (el) el.textContent = String(counts.priority[key] ?? 0);
                });
            }
        }

        if (projectProgress) {
            applyProjectProgress(projectProgress);
        } else {
            refreshProjectDetailStats();
        }
    }

    /**
     * Project progress = (Done / Total) × 100 from actual project tasks only.
     * Only status=done counts. Personal tasks never affect this.
     */
    function applyProjectProgress(progress) {
        if (!state.projectId) return;
        const root = document.querySelector('[data-project-detail-root]');
        if (!root || !progress) return;

        const total = Number(progress.tasks_count ?? 0);
        const done = Number(progress.completed_tasks_count ?? 0);
        const percent = Number.isFinite(Number(progress.progress))
            ? Number(progress.progress)
            : (total > 0 ? Math.round((done / total) * 100) : 0);

        const percentEl = root.querySelector('.project-progress-percent');
        if (percentEl) percentEl.textContent = `${percent}%`;

        const legacyStrong = root.querySelector('.project-detail-progress-label strong');
        if (legacyStrong) legacyStrong.textContent = `${percent}%`;

        const bar = root.querySelector('.project-detail-progress-wrap .project-progress-bar > span');
        if (bar) bar.style.width = `${percent}%`;

        const stats = root.querySelector('.project-detail-stats');
        if (stats) stats.textContent = `${done} of ${total} tasks completed`;
    }

    function refreshProjectDetailStats() {
        if (!state.projectId) return;

        const status = state.statusCounts || {};
        const keys = ['backlog', 'todo', 'in_progress', 'done', 'canceled'];
        const total = keys.reduce((sum, key) => sum + Number(status[key] || 0), 0);
        const done = Number(status.done || 0);

        applyProjectProgress({
            tasks_count: total,
            completed_tasks_count: done,
            progress: total > 0 ? Math.round((done / total) * 100) : 0,
        });
    }

    function applyMutationMeta(data) {
        if (!data) return;
        if (data.counts || data.project_progress) {
            updateFilterCounts(data.counts, data.project_progress || null);
        }
    }

    function projectScopePayload() {
        return {
            project_id: state.projectId ? Number(state.projectId) : null,
        };
    }

    function clearAllFilters() {
        document.querySelectorAll('#doStatusFilter input[type="checkbox"], #doPriorityFilter input[type="checkbox"]').forEach((cb) => {
            cb.checked = false;
        });

        const statusLabel = document.querySelector('#doStatusFilter .dailyops-filter-label');
        const priorityLabel = document.querySelector('#doPriorityFilter .dailyops-filter-label');
        if (statusLabel) statusLabel.textContent = 'Status';
        if (priorityLabel) priorityLabel.textContent = 'Priority';

        document.querySelectorAll('.dailyops-filter.is-open').forEach((el) => el.classList.remove('is-open'));

        loadTasks({ showLoader: true });
    }

    function getSelectedRows() {
        return Array.from(document.querySelectorAll('.dailyops-task-row-checkbox:checked'))
            .map((cb) => cb.closest('.dailyops-task-row'))
            .filter(Boolean);
    }

    function getSelectedIds() {
        return getSelectedRows()
            .map((row) => Number(row.dataset.taskId))
            .filter((id) => Number.isFinite(id));
    }

    function closeBulkMenu() {
        const menu = document.getElementById('doBulkActionsMenu');
        if (!menu) return;
        menu.classList.remove('is-open', 'is-dragging');
        menu.setAttribute('aria-hidden', 'true');
        closeBulkSubmenus();
        state.bulkDrag = null;
        if (state.bulkFlyoutTimer) {
            clearTimeout(state.bulkFlyoutTimer);
            state.bulkFlyoutTimer = null;
        }
        setTimeout(() => {
            if (menu && !menu.classList.contains('is-open')) menu.style.display = 'none';
        }, 150);
    }

    function closeBulkSubmenus(exceptGroup = null) {
        document.querySelectorAll('#doBulkActionsMenu .dailyops-bulk-group').forEach((group) => {
            const key = group.dataset.bulkGroup;
            if (exceptGroup && key === exceptGroup) return;
            group.classList.remove('is-open');
            const toggle = group.querySelector('[data-bulk-group-toggle]');
            const submenu = group.querySelector('.dailyops-bulk-submenu');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            if (submenu) submenu.classList.remove('is-left');
        });
    }

    function positionBulkFlyout(group) {
        const submenu = group.querySelector('.dailyops-bulk-submenu');
        if (!submenu) return;
        submenu.classList.remove('is-left');
        requestAnimationFrame(() => {
            const rect = submenu.getBoundingClientRect();
            if (rect.right > window.innerWidth - 10) {
                submenu.classList.add('is-left');
            }
        });
    }

    function openBulkSubmenu(groupKey) {
        const group = document.querySelector(`#doBulkActionsMenu .dailyops-bulk-group[data-bulk-group="${groupKey}"]`);
        if (!group) return;
        if (state.bulkFlyoutTimer) {
            clearTimeout(state.bulkFlyoutTimer);
            state.bulkFlyoutTimer = null;
        }
        closeBulkSubmenus(groupKey);
        group.classList.add('is-open');
        const toggle = group.querySelector('[data-bulk-group-toggle]');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        positionBulkFlyout(group);
    }

    function scheduleCloseBulkSubmenus() {
        if (state.bulkFlyoutTimer) clearTimeout(state.bulkFlyoutTimer);
        state.bulkFlyoutTimer = setTimeout(() => {
            closeBulkSubmenus();
            state.bulkFlyoutTimer = null;
        }, 140);
    }

    function toggleBulkSubmenu(groupKey) {
        const group = document.querySelector(`#doBulkActionsMenu .dailyops-bulk-group[data-bulk-group="${groupKey}"]`);
        if (!group) return;
        if (group.classList.contains('is-open')) {
            closeBulkSubmenus();
            return;
        }
        openBulkSubmenu(groupKey);
    }

    function clampBulkMenuPosition(left, top, menu) {
        const pad = 8;
        const width = menu.offsetWidth || 220;
        const height = menu.offsetHeight || 280;
        const maxLeft = Math.max(pad, window.innerWidth - width - pad);
        const maxTop = Math.max(pad, window.innerHeight - height - pad);
        return {
            left: Math.min(Math.max(pad, left), maxLeft),
            top: Math.min(Math.max(pad, top), maxTop),
        };
    }

    function getBulkMenuAnchorEl() {
        const selectedRows = getSelectedRows();
        if (selectedRows.length) {
            const last = selectedRows[selectedRows.length - 1];
            return (
                last.querySelector('.checkbox-pro-container') ||
                last.querySelector('.dailyops-task-row-checkbox') ||
                last
            );
        }
        return document.getElementById('doSelectAllCheckbox');
    }

    function positionBulkMenu({ force = false } = {}) {
        const menu = document.getElementById('doBulkActionsMenu');
        const anchor = getBulkMenuAnchorEl();
        if (!menu || !anchor) return;

        menu.style.display = 'flex';

        if (!force && state.bulkMenuDragged) {
            requestAnimationFrame(() => {
                menu.classList.add('is-open');
                menu.setAttribute('aria-hidden', 'false');
            });
            return;
        }

        const rect = anchor.getBoundingClientRect();
        const width = menu.offsetWidth || 220;
        const height = menu.offsetHeight || 280;

        // Open beside the selected checkbox (right), not over the row
        let leftPos = rect.right + 12;
        let topPos = rect.top - 4;

        if (leftPos + width > window.innerWidth - 12) {
            leftPos = rect.left - width - 12;
        }
        if (topPos + height > window.innerHeight - 12) {
            topPos = window.innerHeight - height - 12;
        }
        if (topPos < 8) topPos = 8;

        const clamped = clampBulkMenuPosition(leftPos, topPos, menu);
        menu.style.top = `${clamped.top}px`;
        menu.style.left = `${clamped.left}px`;

        requestAnimationFrame(() => {
            menu.classList.add('is-open');
            menu.setAttribute('aria-hidden', 'false');
        });
    }

    function onBulkMenuDragStart(event) {
        const header = event.target.closest('.dailyops-bulk-menu-header');
        const menu = document.getElementById('doBulkActionsMenu');
        if (!header || !menu || !menu.classList.contains('is-open')) return;
        if (event.button != null && event.button !== 0) return;

        event.preventDefault();
        const rect = menu.getBoundingClientRect();
        state.bulkDrag = {
            offsetX: event.clientX - rect.left,
            offsetY: event.clientY - rect.top,
        };
        menu.classList.add('is-dragging');
        document.body.classList.add('dailyops-bulk-dragging');
    }

    function onBulkMenuDragMove(event) {
        if (!state.bulkDrag) return;
        const menu = document.getElementById('doBulkActionsMenu');
        if (!menu) return;

        const next = clampBulkMenuPosition(
            event.clientX - state.bulkDrag.offsetX,
            event.clientY - state.bulkDrag.offsetY,
            menu
        );
        menu.style.left = `${next.left}px`;
        menu.style.top = `${next.top}px`;
        state.bulkMenuDragged = true;
    }

    function onBulkMenuDragEnd() {
        if (!state.bulkDrag) return;
        state.bulkDrag = null;
        document.getElementById('doBulkActionsMenu')?.classList.remove('is-dragging');
        document.body.classList.remove('dailyops-bulk-dragging');
    }

    function syncSelectionUI() {
        const rows = Array.from(document.querySelectorAll('.dailyops-task-row'));
        const rowChecks = Array.from(document.querySelectorAll('.dailyops-task-row-checkbox'));
        const selectAll = document.getElementById('doSelectAllCheckbox');
        const selected = rowChecks.filter((cb) => cb.checked);
        const countEl = document.getElementById('doBulkSelectedCount');

        rows.forEach((row) => {
            const cb = row.querySelector('.dailyops-task-row-checkbox');
            row.classList.toggle('is-selected', Boolean(cb?.checked));
        });

        if (selectAll) {
            selectAll.checked = rowChecks.length > 0 && selected.length === rowChecks.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < rowChecks.length;
        }

        if (countEl) {
            countEl.textContent = `${selected.length} selected`;
        }

        if (selected.length > 0) {
            if (!state.bulkMenuDismissed) {
                positionBulkMenu({ force: !state.bulkMenuDragged });
            }
        } else {
            state.bulkMenuDismissed = false;
            state.bulkMenuDragged = false;
            closeBulkMenu();
        }
    }

    function toggleSelectAll(checked) {
        document.querySelectorAll('.dailyops-task-row-checkbox').forEach((cb) => {
            cb.checked = checked;
        });
        syncSelectionUI();
    }

    function renderNewTask(task, { prepend = true } = {}) {
        if (!task || !taskMatchesFilters(task)) {
            updateEmptyStateIfNeeded();
            return null;
        }

        const { tbody } = getUiRefs();
        if (!tbody) return null;

        const existing = tbody.querySelector(`[data-task-id="${task.id}"]`);
        if (existing) existing.remove();

        const temp = document.createElement('tbody');
        temp.innerHTML = buildTaskRowHtml(task).trim();
        const row = temp.firstElementChild;
        if (!row) return null;

        if (prepend && tbody.firstChild) {
            tbody.insertBefore(row, tbody.firstChild);
        } else {
            tbody.appendChild(row);
        }

        setListVisibility({ hasTasks: true });
        syncSelectionUI();
        return row;
    }

    function updateTaskRow(task) {
        const { tbody } = getUiRefs();
        if (!tbody || !task) return;

        const row = tbody.querySelector(`[data-task-id="${task.id}"]`);
        if (!taskMatchesFilters(task)) {
            if (row) row.remove();
            updateEmptyStateIfNeeded();
            syncSelectionUI();
            return;
        }

        if (!row) {
            renderNewTask(task, { prepend: true });
            return;
        }

        const s = STATUS_MAP[task.status] || STATUS_MAP.todo;
        const p = PRIORITY_MAP[task.priority] || PRIORITY_MAP.medium;

        row.dataset.status = task.status || 'todo';
        row.dataset.priority = task.priority || 'medium';
        if (typeof task.notification_enabled !== 'undefined') {
            row.dataset.notification = task.notification_enabled ? '1' : '0';
        }

        const statusCell = row.querySelector('.dailyops-task-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="do-status-ic">${s.icon}</span><span class="do-status-text">${s.text}</span>`;
        }

        const priorityCell = row.querySelector('.dailyops-task-priority-cell');
        if (priorityCell) {
            priorityCell.innerHTML = `<span class="do-priority-ic">${p.icon}</span><span class="do-priority-text">${p.text}</span>`;
        }

        const titleEl = row.querySelector('.dailyops-task-title-text');
        if (titleEl && task.title != null) {
            titleEl.textContent = task.title;
            titleEl.setAttribute('title', task.title);
        }

        const descEl = row.querySelector('.dailyops-task-desc-text');
        if (descEl && typeof task.description !== 'undefined') {
            const desc = String(task.description || '').trim();
            if (desc) {
                descEl.textContent = desc;
                descEl.setAttribute('title', desc);
                descEl.hidden = false;
                descEl.classList.remove('is-empty');
            } else {
                descEl.textContent = '';
                descEl.removeAttribute('title');
                descEl.hidden = true;
                descEl.classList.add('is-empty');
            }
        }

        const keyEl = row.querySelector('.dailyops-task-id');
        if (keyEl && task.task_key) keyEl.textContent = task.task_key;

        const projectPill = row.querySelector('.dailyops-project-pill');
        if (projectPill) {
            const label = task.project_label || (task.project_id ? (task.project_name || 'Project') : 'Personal');
            projectPill.textContent = label;
            projectPill.setAttribute('title', label);
            projectPill.classList.toggle('is-personal', !task.project_id);
            projectPill.classList.toggle('is-project', Boolean(task.project_id));
        }
        if (typeof task.project_id !== 'undefined') {
            row.dataset.projectId = task.project_id ? String(task.project_id) : '';
        }
        const btn = row.querySelector('.dailyops-task-actions-btn');
        if (btn) {
            btn.dataset.taskId = String(task.id);
            btn.dataset.taskTitle = task.title || '';
            btn.dataset.taskStatus = task.status || 'todo';
        }
    }

    async function loadTasks({ showLoader = true } = {}) {
        syncQueryStateFromDom();

        const { tbody } = getUiRefs();
        if (!tbody && !document.querySelector('.dailyops-toolbar')) return;

        if (state.abortController) {
            state.abortController.abort();
        }
        state.abortController = new AbortController();

        if (showLoader) {
            setListVisibility({ loading: true });
        }

        try {
            const response = await fetch(`/tasks${buildQueryString()}`, {
                headers: { Accept: 'application/json' },
                signal: state.abortController.signal,
            });

            if (!response.ok) throw new Error('Failed to load tasks');

            const result = await response.json();
            updateFilterCounts(result.counts, result.project_progress || null);
            renderTasks(result.tasks || []);
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error(error);
            setListVisibility({ empty: true });
            showError('Unable to load tasks. Please try again.');
        }
    }

    function handleDoSearch() {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(() => {
            loadTasks({ showLoader: true });
        }, 300);
    }

    function toggleDoFilter(filterId, event) {
        event.stopPropagation();
        const filterEl = document.getElementById(filterId);
        if (!filterEl) return;
        const isOpen = filterEl.classList.contains('is-open');

        document.querySelectorAll('.dailyops-filter.is-open').forEach((el) => {
            el.classList.remove('is-open');
        });

        if (!isOpen) {
            filterEl.classList.add('is-open');
            const searchInput = filterEl.querySelector('.dailyops-filter-search input');
            if (searchInput) searchInput.focus();
        }
    }

    function updateDoFilterLabel(filterId, defaultLabel) {
        const filterEl = document.getElementById(filterId);
        if (!filterEl) return;
        const checkedCount = filterEl.querySelectorAll('input[type="checkbox"]:checked').length;
        const labelEl = filterEl.querySelector('.dailyops-filter-label');
        if (labelEl) {
            labelEl.textContent = checkedCount > 0 ? `${defaultLabel} (${checkedCount})` : defaultLabel;
        }
        loadTasks({ showLoader: true });
    }

    function filterDoOptions(inputEl) {
        const query = inputEl.value.toLowerCase();
        const filterMenu = inputEl.closest('.dailyops-filter-menu');
        if (!filterMenu) return;
        filterMenu.querySelectorAll('.dailyops-filter-option').forEach((opt) => {
            const text = opt.querySelector('.do-opt-text')?.textContent.toLowerCase() || '';
            opt.style.display = text.includes(query) ? 'flex' : 'none';
        });
    }

    function syncMenuState() {
        const doneTextEl = document.getElementById('doActionDoneText');
        if (state.activeTaskStatus === 'done') {
            if (doneTextEl) doneTextEl.textContent = 'Mark as Todo';
        } else {
            if (doneTextEl) doneTextEl.textContent = 'Mark as Done';
        }
    }

    function closeTaskActions() {
        const menu = document.getElementById('doGlobalActionsMenu');
        if (!menu) return;
        menu.classList.remove('is-open');
        setTimeout(() => {
            if (menu && !menu.classList.contains('is-open')) menu.style.display = 'none';
        }, 150);
    }

    function openTaskActions(btnEl, event) {
        event.stopPropagation();
        closeTaskActions();

        state.activeTaskId = btnEl.dataset.taskId;
        state.activeTaskTitle = btnEl.dataset.taskTitle || '';
        state.activeTaskStatus = btnEl.dataset.taskStatus || 'todo';
        state.activeTaskRow = btnEl.closest('.dailyops-task-row');

        syncMenuState();

        const menu = document.getElementById('doGlobalActionsMenu');
        if (!menu) return;

        menu.style.display = 'flex';
        const rect = btnEl.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();

        let topPos = rect.bottom + window.scrollY + 4;
        let leftPos = rect.right - menuRect.width + window.scrollX;

        if (topPos + menuRect.height > window.scrollY + window.innerHeight) {
            topPos = rect.top + window.scrollY - menuRect.height - 4;
        }

        menu.style.top = `${topPos}px`;
        menu.style.left = `${leftPos}px`;

        requestAnimationFrame(() => menu.classList.add('is-open'));
    }

    async function doActionMarkDone() {
        if (state.isUpdatingStatus || !state.activeTaskId) return;

        const newStatus = state.activeTaskStatus === 'done' ? 'todo' : 'done';
        state.isUpdatingStatus = true;

        try {
            const response = await fetch(`/tasks/${state.activeTaskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    status: newStatus,
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Status update failed');

            const data = await response.json();
            if (!data.success) throw new Error('Status update failed');

            state.activeTaskStatus = newStatus;
            showToast(newStatus === 'done' ? 'Task marked as done' : 'Task marked as todo');
            closeTaskActions();

            applyMutationMeta(data);
            if (data.task) {
                updateTaskRow(data.task);
            } else if (state.activeTaskRow) {
                updateTaskRow({
                    id: Number(state.activeTaskId),
                    status: newStatus,
                    priority: state.activeTaskRow.dataset.priority,
                    title: state.activeTaskTitle,
                    task_key: state.activeTaskRow.querySelector('.dailyops-task-id')?.textContent,
                });
            }
        } catch (error) {
            console.error(error);
            showError('Unable to update task status. Please try again.');
        } finally {
            state.isUpdatingStatus = false;
        }
    }

    function doActionEdit() {
        const taskId = state.activeTaskId;
        closeTaskActions();
        if (!taskId) return;
        openEditTaskModal(taskId);
    }

    async function doActionCopy() {
        if (state.isCopyingTask || !state.activeTaskId) return;
        state.isCopyingTask = true;

        try {
            const response = await fetch(`/tasks/${state.activeTaskId}/duplicate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(projectScopePayload()),
            });

            if (!response.ok) throw new Error('Task duplication failed');

            const data = await response.json();
            if (!data.success) throw new Error('Task duplication failed');

            showToast('Task copied successfully');
            closeTaskActions();

            applyMutationMeta(data);
            if (data.task) {
                renderNewTask(data.task, { prepend: true });
            }
        } catch (error) {
            console.error(error);
            showError('Unable to copy task. Please try again.');
        } finally {
            state.isCopyingTask = false;
        }
    }

    function doActionDelete() {
        closeTaskActions();
        const titleEl = document.getElementById('doDeleteTaskTitle');
        if (titleEl) titleEl.textContent = state.activeTaskTitle;
        const overlay = document.getElementById('doDeleteModalOverlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('is-open'));
    }

    function closeDoDeleteModal() {
        const overlay = document.getElementById('doDeleteModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }

    function confirmDoDelete() {
        if (!state.activeTaskId || state.isDeletingTask) {
            closeDoDeleteModal();
            return;
        }

        state.isDeletingTask = true;
        const taskId = state.activeTaskId;
        const row = state.activeTaskRow;

        fetch(`/tasks/${taskId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(projectScopePayload()),
        })
            .then(async (response) => {
                if (!response.ok) throw new Error('Delete failed');
                const data = await response.json();
                if (!data.success) throw new Error('Delete failed');

                if (row) {
                    row.style.transition = 'opacity 0.2s, transform 0.2s';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        row.remove();
                        updateEmptyStateIfNeeded();
                        syncSelectionUI();
                    }, 200);
                }

                if (data.counts || data.project_progress) applyMutationMeta(data);
                else loadTasks({ showLoader: false });

                showToast('Task deleted successfully');
                closeDoDeleteModal();
            })
            .catch((error) => {
                console.error(error);
                showError('Unable to delete task. Please try again.');
            })
            .finally(() => {
                state.isDeletingTask = false;
            });
    }

    function beginBulkAction(btn, actionKey) {
        if (state.isBulkBusy) return false;
        const ids = getSelectedIds();
        if (!ids.length) return false;

        state.isBulkBusy = true;
        state.bulkBusyAction = actionKey;

        const menu = document.getElementById('doBulkActionsMenu');
        menu?.classList.add('is-busy');

        document.querySelectorAll('#doBulkActionsMenu .dailyops-task-action').forEach((el) => {
            el.disabled = true;
            el.classList.remove('is-bulk-loading');
        });

        if (btn) {
            btn.dataset.bulkOriginalHtml = btn.innerHTML;
            btn.classList.add('is-bulk-loading');
            btn.innerHTML = `${DevOSHourglass.html('xs')} Working...`;
        }

        return ids;
    }

    function endBulkAction(btn) {
        state.isBulkBusy = false;
        state.bulkBusyAction = null;

        const menu = document.getElementById('doBulkActionsMenu');
        menu?.classList.remove('is-busy');

        document.querySelectorAll('#doBulkActionsMenu .dailyops-task-action').forEach((el) => {
            el.disabled = false;
            el.classList.remove('is-bulk-loading');
        });

        if (btn?.dataset.bulkOriginalHtml) {
            btn.innerHTML = btn.dataset.bulkOriginalHtml;
            delete btn.dataset.bulkOriginalHtml;
        }
    }

    async function bulkUpdateStatus(status, btn = null) {
        const ids = beginBulkAction(btn, `status:${status}`);
        if (!ids) return;

        try {
            const response = await fetch('/tasks/bulk-status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ids,
                    status,
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Bulk status update failed');
            const data = await response.json();
            if (!data.success) throw new Error('Bulk status update failed');

            (data.tasks || []).forEach((task) => updateTaskRow(task));
            applyMutationMeta(data);

            showToast('Selected tasks updated');
            toggleSelectAll(false);
        } catch (error) {
            console.error(error);
            showError('Unable to update selected tasks. Please try again.');
        } finally {
            endBulkAction(btn);
        }
    }

    async function bulkUpdatePriority(priority, btn = null) {
        const ids = beginBulkAction(btn, `priority:${priority}`);
        if (!ids) return;

        try {
            const response = await fetch('/tasks/bulk-priority', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ids,
                    priority,
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Bulk priority update failed');
            const data = await response.json();
            if (!data.success) throw new Error('Bulk priority update failed');

            (data.tasks || []).forEach((task) => updateTaskRow(task));
            applyMutationMeta(data);

            showToast('Selected tasks priority updated');
            toggleSelectAll(false);
        } catch (error) {
            console.error(error);
            showError('Unable to update priority for selected tasks.');
        } finally {
            endBulkAction(btn);
        }
    }

    async function bulkDuplicateSelected(btn = null) {
        const ids = beginBulkAction(btn, 'duplicate');
        if (!ids) return;

        try {
            const response = await fetch('/tasks/bulk-duplicate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ids,
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Bulk duplicate failed');
            const data = await response.json();
            if (!data.success) throw new Error('Bulk duplicate failed');

            (data.tasks || []).forEach((task) => renderNewTask(task, { prepend: true }));
            applyMutationMeta(data);

            showToast(`Duplicated ${Number((data.tasks || []).length)} task${(data.tasks || []).length === 1 ? '' : 's'}`);
            toggleSelectAll(false);
        } catch (error) {
            console.error(error);
            showError('Unable to duplicate selected tasks.');
        } finally {
            endBulkAction(btn);
        }
    }

    async function bulkUpdateNotification(enabled, btn = null) {
        const ids = beginBulkAction(btn, `notification:${enabled ? 'on' : 'off'}`);
        if (!ids) return;

        try {
            const response = await fetch('/tasks/bulk-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ids,
                    notification_enabled: Boolean(enabled),
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Bulk notification update failed');
            const data = await response.json();
            if (!data.success) throw new Error('Bulk notification update failed');

            (data.tasks || []).forEach((task) => {
                updateTaskRow(task);
                if (task?.id != null) {
                    state.mockNotif[task.id] = Boolean(task.notification_enabled);
                }
            });
            applyMutationMeta(data);

            showToast(enabled ? 'Notifications turned on' : 'Notifications turned off');
            toggleSelectAll(false);
        } catch (error) {
            console.error(error);
            showError('Unable to update notifications for selected tasks.');
        } finally {
            endBulkAction(btn);
        }
    }

    async function bulkDeleteSelected(btn = null) {
        if (state.isBulkBusy) return;
        const ids = getSelectedIds();
        if (!ids.length) return;

        if (!window.confirm(`Delete ${ids.length} selected task${ids.length > 1 ? 's' : ''}?`)) {
            return;
        }

        const started = beginBulkAction(btn || document.getElementById('doBulkDeleteBtn'), 'delete');
        if (!started) return;

        try {
            const response = await fetch('/tasks/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ids,
                    ...projectScopePayload(),
                }),
            });

            if (!response.ok) throw new Error('Bulk delete failed');
            const data = await response.json();
            if (!data.success) throw new Error('Bulk delete failed');

            const removedIds = Array.isArray(data.ids) && data.ids.length ? data.ids : ids;
            removedIds.forEach((id) => {
                document.querySelector(`.dailyops-task-row[data-task-id="${id}"]`)?.remove();
            });

            applyMutationMeta(data);

            updateEmptyStateIfNeeded();
            syncSelectionUI();
            showToast('Selected tasks deleted');
        } catch (error) {
            console.error(error);
            showError('Unable to delete selected tasks. Please try again.');
        } finally {
            endBulkAction(btn || document.getElementById('doBulkDeleteBtn'));
        }
    }

    // ---- Add / Edit Task Modal ----

    function reminderToSelectValue(reminder) {
        const n = Number(reminder);
        if (n === 10) return '10min';
        if (n === 30) return '30min';
        if (n === 60) return '1hour';
        if (n === 1440) return '1day';
        return 'none';
    }

    function formatDueTimeForInput(dueTime) {
        if (!dueTime) return '';
        const raw = String(dueTime);
        return raw.length >= 5 ? raw.slice(0, 5) : raw;
    }

    function setTaskModalMode(mode) {
        const isEdit = mode === 'edit';
        const titleEl = document.getElementById('addTaskModalTitle');
        const subtitleEl = document.querySelector('#addTaskModalOverlay .do-modal-subtitle');
        const submitLabel = document.getElementById('addTaskSubmitLabel');
        const iconEl = document.querySelector('#addTaskSubmitBtn .do-btn-create-icon');

        if (titleEl) titleEl.textContent = isEdit ? 'Edit Task' : 'Add New Task';
        if (subtitleEl) {
            subtitleEl.textContent = isEdit
                ? 'Update details for this task in your DailyOps workflow.'
                : 'Create and organize a task for your DailyOps workflow.';
        }
        if (submitLabel) submitLabel.textContent = isEdit ? 'Save Changes' : 'Create Task';
        if (iconEl) {
            iconEl.innerHTML = isEdit
                ? '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>'
                : '<line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />';
        }
    }

    function fillTaskForm(task) {
        const form = document.getElementById('addTaskForm');
        if (!form || !task) return;

        const editId = document.getElementById('taskEditId');
        if (editId) editId.value = String(task.id || '');

        const title = document.getElementById('taskTitle');
        if (title) title.value = task.title || '';

        const description = document.getElementById('taskDescription');
        if (description) description.value = task.description || '';

        const dueDate = document.getElementById('taskDueDate');
        if (dueDate) dueDate.value = task.due_date || '';

        const dueTime = document.getElementById('taskDueTime');
        if (dueTime) dueTime.value = formatDueTimeForInput(task.due_time);

        const priority = document.getElementById('taskPriority');
        if (priority) priority.value = task.priority || 'medium';

        const project = document.getElementById('taskProject');
        if (project) {
            if (state.projectId) {
                project.value = String(state.projectId);
                project.disabled = true;
            } else {
                project.disabled = false;
                project.value = task.project_id ? String(task.project_id) : '';
            }
        }

        const focus = document.getElementById('taskFocus');
        if (focus) focus.checked = Boolean(task.focus_task);

        const reminder = document.getElementById('taskReminder');
        if (reminder) reminder.value = reminderToSelectValue(task.reminder);
    }

    async function loadSelectableProjects() {
        const select = document.getElementById('taskProject');
        if (!select) return;

        try {
            const response = await fetch('/projects/data?selectable=1', {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Failed to load projects');
            const result = await response.json();
            const projects = result.projects || [];

            const current = select.value;
            select.innerHTML = '<option value="">🧘 Personal Task</option>';
            projects.forEach((p) => {
                const opt = document.createElement('option');
                opt.value = String(p.id);
                opt.textContent = `${p.key} — ${p.name}`;
                select.appendChild(opt);
            });

            if (state.projectId) {
                select.value = String(state.projectId);
                select.disabled = true;
            } else if (current) {
                select.value = current;
                select.disabled = false;
            } else {
                select.disabled = false;
            }
            state.projectsLoaded = true;
        } catch (error) {
            console.error(error);
        }
    }

    function openTaskModalShell() {
        const overlay = document.getElementById('addTaskModalOverlay');
        if (!overlay) return null;
        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('is-open');
        document.body.classList.add('do-modal-open');
        setTimeout(() => {
            const firstInput = overlay.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) firstInput.focus();
        }, 240);
        return overlay;
    }

    function openAddTaskModal() {
        const overlay = document.getElementById('addTaskModalOverlay');
        if (!overlay) return;
        state.editingTaskId = null;
        const editId = document.getElementById('taskEditId');
        if (editId) editId.value = '';
        setTaskModalMode('create');
        loadSelectableProjects();
        openTaskModalShell();
    }

    async function openEditTaskModal(taskId) {
        const overlay = document.getElementById('addTaskModalOverlay');
        if (!overlay || !taskId) return;

        state.editingTaskId = String(taskId);
        setTaskModalMode('edit');
        await loadSelectableProjects();
        openTaskModalShell();

        try {
            const response = await fetch(`/tasks/${taskId}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Failed to load task');
            const result = await response.json();
            if (!result.success || !result.task) throw new Error('Task not found');
            fillTaskForm(result.task);
        } catch (error) {
            console.error(error);
            closeAddTaskModal();
            showError('Unable to open task for editing. Please try again.');
        }
    }

    function closeAddTaskModal() {
        const overlay = document.getElementById('addTaskModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('do-modal-open');
        state.editingTaskId = null;
        const form = document.getElementById('addTaskForm');
        if (form) form.reset();
        const editId = document.getElementById('taskEditId');
        if (editId) editId.value = '';
        setTaskModalMode('create');
        const select = document.getElementById('taskProject');
        if (select) {
            select.disabled = Boolean(state.projectId);
            if (state.projectId) select.value = String(state.projectId);
        }
        form?.querySelectorAll('.do-error-message').forEach((el) => el.remove());
        form?.querySelectorAll('.do-field-error').forEach((el) => el.classList.remove('do-field-error'));
    }

    async function handleAddTask(e) {
        e.preventDefault();
        if (state.isSubmittingTask) return;

        const form = e.target;
        const submitBtn = form.querySelector('.do-btn-create');
        if (!submitBtn) return;

        form.querySelectorAll('.do-error-message').forEach((el) => el.remove());
        form.querySelectorAll('.do-field-error').forEach((el) => el.classList.remove('do-field-error'));

        const isEdit = Boolean(state.editingTaskId);
        const originalBtnHtml = submitBtn.innerHTML;
        state.isSubmittingTask = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `${DevOSHourglass.html('xs')} ${isEdit ? 'Saving...' : 'Creating Task...'}`;

        const data = new FormData(form);

        let reminderVal = data.get('reminder');
        let reminderInt = null;
        if (reminderVal === '10min') reminderInt = 10;
        else if (reminderVal === '30min') reminderInt = 30;
        else if (reminderVal === '1hour') reminderInt = 60;
        else if (reminderVal === '1day') reminderInt = 1440;

        const token = data.get('_token') || csrfToken();
        let projectId = null;
        const projectRaw = data.get('project_id');
        if (projectRaw !== null && projectRaw !== undefined && String(projectRaw).trim() !== '' && String(projectRaw) !== '0') {
            const parsed = Number(projectRaw);
            if (Number.isFinite(parsed) && parsed > 0) {
                projectId = parsed;
            }
        }

        const payload = {
            title: data.get('title'),
            description: data.get('description'),
            due_date: data.get('due_date'),
            due_time: data.get('due_time') || null,
            priority: data.get('priority'),
            project_id: projectId,
            focus_task: data.get('is_focus') === 'on' ? 1 : 0,
            reminder: reminderInt,
            scope_project_id: state.projectId,
        };

        if (!isEdit) {
            payload.status = 'todo';
            payload.notification_enabled = false;
        }

        // Project detail page forces the current project
        if (state.projectId) {
            payload.project_id = Number(state.projectId);
        }

        try {
            const url = isEdit ? `/tasks/${state.editingTaskId}` : '/tasks';
            const response = await fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    Object.keys(result.errors).forEach((field) => {
                        let fieldIdName = field === 'project_id' ? 'project_id' : field;
                        const inputEl = form.querySelector(`[name="${fieldIdName}"]`);
                        if (inputEl) {
                            inputEl.classList.add('do-field-error');
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'do-error-message';
                            errorMsg.style.color = '#e53e3e';
                            errorMsg.style.fontSize = '12px';
                            errorMsg.style.marginTop = '4px';
                            errorMsg.textContent = result.errors[field][0];
                            inputEl.parentNode.appendChild(errorMsg);
                        }
                    });
                } else {
                    showError('Something went wrong. Please try again.');
                }
                return;
            }

            closeAddTaskModal();
            showToast(isEdit ? 'Task updated successfully' : 'Task created successfully');
            syncQueryStateFromDom();
            applyMutationMeta(result);

            if (isEdit) {
                if (result.task) updateTaskRow(result.task);
            } else {
                // If task was assigned to another project from DailyOps, don't insert into personal list
                const createdProjectId = result.task?.project_id ?? null;
                const currentScope = state.projectId ? Number(state.projectId) : null;
                if (currentScope === null && createdProjectId) {
                    // personal page — project task won't appear here
                } else if (result.task) {
                    renderNewTask(result.task, { prepend: true });
                }
            }
        } catch (error) {
            console.error(error);
            showError('Something went wrong. Please try again.');
        } finally {
            state.isSubmittingTask = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    function onDocumentClick(event) {
        document.querySelectorAll('.dailyops-filter.is-open').forEach((el) => {
            if (!el.contains(event.target)) el.classList.remove('is-open');
        });

        const menu = document.getElementById('doGlobalActionsMenu');
        if (menu && menu.classList.contains('is-open') && !menu.contains(event.target)) {
            const isActionsBtn = event.target.closest('.dailyops-task-actions-btn');
            if (!isActionsBtn) closeTaskActions();
        }

        const bulkMenu = document.getElementById('doBulkActionsMenu');
        if (bulkMenu && bulkMenu.classList.contains('is-open') && !bulkMenu.contains(event.target)) {
            const isSelectControl = event.target.closest('#doSelectAllCheckbox, .dailyops-task-row-checkbox, .checkbox-pro-container');
            if (!isSelectControl) {
                state.bulkMenuDismissed = true;
                state.bulkMenuDragged = false;
                closeBulkMenu();
            }
        }

        const overlay = document.getElementById('addTaskModalOverlay');
        const wrapper = document.getElementById('addTaskModalWrapper');
        if (overlay && wrapper && overlay.classList.contains('is-open') && event.target === overlay) {
            closeAddTaskModal();
        }
    }

    function onDocumentKeydown(event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.dailyops-filter.is-open').forEach((el) => {
            el.classList.remove('is-open');
        });

        const deleteModal = document.getElementById('doDeleteModalOverlay');
        const menu = document.getElementById('doGlobalActionsMenu');
        const addOverlay = document.getElementById('addTaskModalOverlay');

        if (deleteModal && deleteModal.classList.contains('is-open')) {
            closeDoDeleteModal();
        } else if (document.getElementById('doBulkActionsMenu')?.classList.contains('is-open')) {
            state.bulkMenuDismissed = true;
            state.bulkMenuDragged = false;
            closeBulkMenu();
        } else if (menu && menu.classList.contains('is-open')) {
            closeTaskActions();
        } else if (addOverlay && addOverlay.classList.contains('is-open')) {
            closeAddTaskModal();
        } else if (getSelectedIds().length) {
            toggleSelectAll(false);
        }
    }

    function onTaskTableClick(event) {
        const btn = event.target.closest('.dailyops-task-actions-btn');
        if (btn) {
            openTaskActions(btn, event);
            return;
        }
    }

    function onTaskTableChange(event) {
        const target = event.target;
        state.bulkMenuDismissed = false;
        state.bulkMenuDragged = false;
        if (target.id === 'doSelectAllCheckbox') {
            toggleSelectAll(target.checked);
            return;
        }
        if (target.classList.contains('dailyops-task-row-checkbox')) {
            syncSelectionUI();
        }
    }

    function initDailyOps() {
        if (state.initialized) return;
        if (!document.querySelector('[data-dailyops-root]')) {
            return;
        }
        state.initialized = true;

        const root = document.querySelector('[data-dailyops-root]');
        if (root?.dataset.projectId) {
            state.projectId = Number(root.dataset.projectId);
        }

        const searchInput = document.getElementById('doSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', handleDoSearch);
        }

        document.querySelectorAll('.dailyops-filter-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                const filter = btn.closest('.dailyops-filter');
                if (filter?.id) toggleDoFilter(filter.id, event);
            });
        });

        document.querySelectorAll('#doStatusFilter .dailyops-filter-options input[type="checkbox"]').forEach((cb) => {
            cb.addEventListener('change', () => updateDoFilterLabel('doStatusFilter', 'Status'));
        });
        document.querySelectorAll('#doPriorityFilter .dailyops-filter-options input[type="checkbox"]').forEach((cb) => {
            cb.addEventListener('change', () => updateDoFilterLabel('doPriorityFilter', 'Priority'));
        });

        document.querySelectorAll('[data-clear-filters]').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                clearAllFilters();
            });
        });

        document.querySelectorAll('.dailyops-filter-search input').forEach((input) => {
            input.addEventListener('keyup', () => filterDoOptions(input));
        });

        document.getElementById('btnOpenAddTask')?.addEventListener('click', openAddTaskModal);
        document.querySelectorAll('[data-open-add-task]').forEach((el) => {
            el.addEventListener('click', openAddTaskModal);
        });

        document.getElementById('doActionMarkDoneBtn')?.addEventListener('click', doActionMarkDone);
        document.getElementById('doActionEditBtn')?.addEventListener('click', doActionEdit);
        document.getElementById('doActionCopyBtn')?.addEventListener('click', doActionCopy);
        document.getElementById('doActionDeleteBtn')?.addEventListener('click', doActionDelete);
        document.getElementById('doDeleteCloseBtn')?.addEventListener('click', closeDoDeleteModal);
        document.getElementById('doDeleteCancelBtn')?.addEventListener('click', closeDoDeleteModal);
        document.getElementById('doDeleteConfirmBtn')?.addEventListener('click', confirmDoDelete);

        document.querySelectorAll('[data-bulk-status]').forEach((btn) => {
            btn.addEventListener('click', () => bulkUpdateStatus(btn.dataset.bulkStatus, btn));
        });
        document.querySelectorAll('[data-bulk-priority]').forEach((btn) => {
            btn.addEventListener('click', () => bulkUpdatePriority(btn.dataset.bulkPriority, btn));
        });
        document.getElementById('doBulkDuplicateBtn')?.addEventListener('click', (e) => {
            bulkDuplicateSelected(e.currentTarget);
        });
        document.getElementById('doBulkDeleteBtn')?.addEventListener('click', (e) => {
            bulkDeleteSelected(e.currentTarget);
        });
        document.getElementById('doBulkDeselectBtn')?.addEventListener('click', () => toggleSelectAll(false));

        document.querySelectorAll('#doBulkActionsMenu .dailyops-bulk-group').forEach((group) => {
            const key = group.dataset.bulkGroup;
            group.addEventListener('mouseenter', () => {
                openBulkSubmenu(key);
            });
            group.addEventListener('mouseleave', () => {
                scheduleCloseBulkSubmenus();
            });
        });

        document.querySelectorAll('#doBulkActionsMenu [data-bulk-group-toggle]').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleBulkSubmenu(btn.dataset.bulkGroupToggle);
            });
        });

        const bulkHeader = document.querySelector('#doBulkActionsMenu .dailyops-bulk-menu-header');
        bulkHeader?.addEventListener('pointerdown', onBulkMenuDragStart);
        document.addEventListener('pointermove', onBulkMenuDragMove);
        document.addEventListener('pointerup', onBulkMenuDragEnd);
        document.addEventListener('pointercancel', onBulkMenuDragEnd);

        const table = document.querySelector('.dailyops-task-table');
        table?.addEventListener('click', onTaskTableClick);
        table?.addEventListener('change', onTaskTableChange);

        const addForm = document.getElementById('addTaskForm');
        if (addForm) {
            addForm.addEventListener('submit', handleAddTask);
        }
        document.querySelectorAll('[data-close-add-task]').forEach((el) => {
            el.addEventListener('click', closeAddTaskModal);
        });

        document.addEventListener('click', onDocumentClick);
        document.addEventListener('keydown', onDocumentKeydown);

        if (document.querySelector('[data-dailyops-root]')) {
            loadTasks({ showLoader: true });
            loadSelectableProjects();
        }
    }

    return {
        init: initDailyOps,
        loadTasks,
        renderNewTask,
        openAddTaskModal,
        closeAddTaskModal,
    };
})();

// Public aliases for any residual inline handlers / empty-state button
window.openAddTaskModal = () => DailyOps.openAddTaskModal();
window.closeAddTaskModal = () => DailyOps.closeAddTaskModal();
window.renderNewTask = (task) => DailyOps.renderNewTask(task);

// ---------------------------------------------------------------------------
// Projects
// ---------------------------------------------------------------------------

const Projects = (() => {
    const STATUS_LABEL = {
        planning: 'Planning',
        active: 'Active',
        on_hold: 'On Hold',
        completed: 'Completed',
        archived: 'Archived',
    };
    const PRIORITY_LABEL = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        urgent: 'Urgent',
    };

    const state = {
        initialized: false,
        search: '',
        status: [],
        priority: [],
        debounceTimer: null,
        abortController: null,
        activeProject: null,
        isSubmitting: false,
        isDeleting: false,
        isArchiving: false,
    };

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showToast(message) {
        if (window.DevOSAlert) {
            window.DevOSAlert.success('done successfully :)', message);
        } else {
            const toast = document.createElement('div');
            toast.className = 'dailyops-toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }
        window.DevOSNotifications?.refresh();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatRelative(iso) {
        if (!iso) return '—';
        const date = new Date(iso);
        const days = Math.floor((Date.now() - date.getTime()) / 86400000);
        if (days <= 0) return 'Today';
        if (days === 1) return 'Yesterday';
        if (days < 7) return `${days} days ago`;
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatDue(dateStr) {
        if (!dateStr) return 'No due date';
        const d = new Date(`${dateStr}T00:00:00`);
        return `Due ${d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}`;
    }

    function syncFilters() {
        state.search = document.getElementById('pjSearchInput')?.value.trim() || '';
        state.status = Array.from(document.querySelectorAll('#pjStatusFilter input[type="checkbox"]:checked')).map((c) => c.value);
        state.priority = Array.from(document.querySelectorAll('#pjPriorityFilter input[type="checkbox"]:checked')).map((c) => c.value);
    }

    function buildQuery() {
        const params = new URLSearchParams();
        if (state.search) params.append('search', state.search);
        state.status.forEach((s) => params.append('status[]', s));
        state.priority.forEach((p) => params.append('priority[]', p));
        const qs = params.toString();
        return qs ? `?${qs}` : '';
    }

    function updateCounts(counts) {
        if (!counts) return;
        Object.entries(counts.status || {}).forEach(([k, v]) => {
            const el = document.querySelector(`[data-pj-status-count="${k}"]`);
            if (el) el.textContent = String(v);
        });
        Object.entries(counts.priority || {}).forEach(([k, v]) => {
            const el = document.querySelector(`[data-pj-priority-count="${k}"]`);
            if (el) el.textContent = String(v);
        });
    }

    function updateFilterLabel(filterId, defaultLabel) {
        const filterEl = document.getElementById(filterId);
        if (!filterEl) return;
        const count = filterEl.querySelectorAll('input[type="checkbox"]:checked').length;
        const label = filterEl.querySelector('.dailyops-filter-label');
        if (label) label.textContent = count > 0 ? `${defaultLabel} (${count})` : defaultLabel;
        loadProjects();
    }

    function clearFilters() {
        document.querySelectorAll('#pjStatusFilter input[type="checkbox"], #pjPriorityFilter input[type="checkbox"]').forEach((cb) => {
            cb.checked = false;
        });
        const s = document.querySelector('#pjStatusFilter .dailyops-filter-label');
        const p = document.querySelector('#pjPriorityFilter .dailyops-filter-label');
        if (s) s.textContent = 'Status';
        if (p) p.textContent = 'Priority';
        document.querySelectorAll('.projects-page .dailyops-filter.is-open').forEach((el) => el.classList.remove('is-open'));
        loadProjects();
    }

    function bumpCount(attr, key, delta) {
        if (!key) return;
        const el = document.querySelector(`[${attr}="${key}"]`);
        if (!el) return;
        el.textContent = String(Math.max(0, Number(el.textContent || 0) + delta));
    }

    function projectMatchesFilters(project) {
        syncFilters();
        // Default list hides archived unless Status filter explicitly includes it
        if (!state.status.length && project.status === 'archived') return false;
        if (state.status.length && !state.status.includes(project.status)) return false;
        if (state.priority.length && !state.priority.includes(project.priority)) return false;
        if (state.search) {
            const q = state.search.toLowerCase();
            const hay = [project.name, project.key, project.description || ''].join(' ').toLowerCase();
            if (!hay.includes(q)) return false;
        }
        return true;
    }

    function refreshGridEmptyState() {
        const grid = document.getElementById('pjGrid');
        const empty = document.getElementById('pjEmptyState');
        const hasCards = Boolean(grid?.querySelector('.project-card'));
        if (hasCards) {
            if (empty) empty.style.display = 'none';
            if (grid) grid.style.display = 'grid';
            return;
        }
        if (grid) {
            grid.style.display = 'none';
            grid.innerHTML = '';
        }
        if (empty) {
            empty.style.display = 'flex';
            const title = empty.querySelector('p.font-semibold');
            const sub = empty.querySelector('p.text-\\[var\\(--color-dp-text-muted\\)\\]');
            const filtered = Boolean(state.search || state.status.length || state.priority.length);
            if (title) title.textContent = filtered ? 'No projects match your filters.' : 'No projects yet';
            if (sub) sub.textContent = filtered ? '' : 'Create your first project to organize your work.';
        }
    }

    function removeProjectCard(id) {
        const card = document.querySelector(`.project-card[data-project-id="${id}"]`);
        if (card) card.remove();
        refreshGridEmptyState();
    }

    function upsertProjectCard(project, previous = null) {
        const grid = document.getElementById('pjGrid');
        if (!grid) return;

        if (previous) {
            if (previous.status !== project.status) {
                bumpCount('data-pj-status-count', previous.status, -1);
                bumpCount('data-pj-status-count', project.status, 1);
            }
            if (previous.priority !== project.priority) {
                bumpCount('data-pj-priority-count', previous.priority, -1);
                bumpCount('data-pj-priority-count', project.priority, 1);
            }
        } else {
            bumpCount('data-pj-status-count', project.status, 1);
            bumpCount('data-pj-priority-count', project.priority, 1);
        }

        const existing = grid.querySelector(`.project-card[data-project-id="${project.id}"]`);
        if (!projectMatchesFilters(project)) {
            if (existing) existing.remove();
            refreshGridEmptyState();
            return;
        }

        const html = cardHtml(project);
        if (existing) {
            existing.outerHTML = html;
        } else {
            grid.insertAdjacentHTML('afterbegin', html);
        }
        refreshGridEmptyState();
    }

    function cardHtml(project) {
        const progress = Number(project.progress || 0);
        const desc = project.description
            ? `<p class="project-card-desc">${escapeHtml(project.description)}</p>`
            : '';
        return `
            <article class="project-card" data-project-id="${project.id}" data-status="${escapeHtml(project.status)}" data-priority="${escapeHtml(project.priority)}" data-key="${escapeHtml(project.key)}" role="button" tabindex="0">
                <div class="project-card-top">
                    <div>
                        <h3 class="project-card-title">${escapeHtml(project.name)}</h3>
                        <span class="project-key-badge">${escapeHtml(project.key)}</span>
                    </div>
                    <button type="button" class="dailyops-task-actions-btn project-card-menu-btn" aria-label="Project actions" data-project-id="${project.id}">⋮</button>
                </div>
                ${desc}
                <div class="project-card-pills">
                    <span class="project-pill">${STATUS_LABEL[project.status] || project.status}</span>
                    <span class="project-pill">${PRIORITY_LABEL[project.priority] || project.priority}</span>
                </div>
                <div class="project-detail-progress-label">
                    <span>Progress</span>
                    <strong>${progress}%</strong>
                </div>
                <div class="project-progress-bar"><span style="width:${progress}%"></span></div>
                <div class="project-card-footer">
                    <span>${Number(project.tasks_count || 0)} Tasks</span>
                    <span>${formatDue(project.due_date)}</span>
                </div>
                <div class="project-card-updated">Last updated: ${formatRelative(project.updated_at)}</div>
            </article>
        `;
    }

    function renderProjects(projects) {
        const grid = document.getElementById('pjGrid');
        const empty = document.getElementById('pjEmptyState');
        const loader = document.getElementById('pjLoadingState');
        if (loader) loader.style.display = 'none';

        if (!projects.length) {
            if (grid) grid.style.display = 'none';
            if (empty) {
                empty.style.display = 'flex';
                const title = empty.querySelector('p.font-semibold');
                const sub = empty.querySelector('p.text-\\[var\\(--color-dp-text-muted\\)\\]');
                const filtered = Boolean(state.search || state.status.length || state.priority.length);
                if (title) title.textContent = filtered ? 'No projects match your filters.' : 'No projects yet';
                if (sub) sub.textContent = filtered ? '' : 'Create your first project to organize your work.';
            }
            return;
        }

        if (empty) empty.style.display = 'none';
        if (grid) {
            grid.style.display = 'grid';
            grid.innerHTML = projects.map(cardHtml).join('');
        }
    }

    async function loadProjects() {
        if (!document.querySelector('[data-projects-root]')) return;
        syncFilters();
        if (state.abortController) state.abortController.abort();
        state.abortController = new AbortController();

        const loader = document.getElementById('pjLoadingState');
        const grid = document.getElementById('pjGrid');
        const empty = document.getElementById('pjEmptyState');
        if (loader) loader.style.display = 'flex';
        if (grid) grid.style.display = 'none';
        if (empty) empty.style.display = 'none';

        try {
            const response = await fetch(`/projects/data${buildQuery()}`, {
                headers: { Accept: 'application/json' },
                signal: state.abortController.signal,
            });
            if (!response.ok) throw new Error('Failed to load projects');
            const result = await response.json();
            updateCounts(result.counts);
            renderProjects(result.projects || []);
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error(error);
            if (loader) loader.style.display = 'none';
            showToast('Unable to load projects. Please try again.');
        }
    }

    function closeActions() {
        const menu = document.getElementById('pjGlobalActionsMenu');
        if (!menu) return;
        menu.classList.remove('is-open');
        setTimeout(() => {
            if (!menu.classList.contains('is-open')) menu.style.display = 'none';
        }, 150);
    }

    function openActions(btn, project) {
        closeActions();
        state.activeProject = project;
        const menu = document.getElementById('pjGlobalActionsMenu');
        if (!menu) return;
        const archiveBtn = document.getElementById('pjActionArchiveBtn');
        if (archiveBtn) {
            archiveBtn.style.display = project.status === 'archived' ? 'none' : '';
        }
        menu.style.display = 'flex';
        const rect = btn.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        let top = rect.bottom + window.scrollY + 4;
        let left = rect.right - menuRect.width + window.scrollX;
        if (top + menuRect.height > window.scrollY + window.innerHeight) {
            top = rect.top + window.scrollY - menuRect.height - 4;
        }
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        requestAnimationFrame(() => menu.classList.add('is-open'));
    }

    function setProjectModalLockedFields(locked) {
        const ids = [
            'projectName',
            'projectDescription',
            'projectStatus',
            'projectPriority',
            'projectStartDate',
        ];
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.disabled = locked;
            el.classList.toggle('do-field-readonly', locked);
            if (locked) el.setAttribute('tabindex', '-1');
            else el.removeAttribute('tabindex');
        });

        const due = document.getElementById('projectDueDate');
        if (due) {
            due.disabled = false;
            due.classList.remove('do-field-readonly');
            due.removeAttribute('tabindex');
        }

        const hint = document.getElementById('projectEditLockHint');
        if (hint) hint.style.display = locked ? 'block' : 'none';

        const subtitle = document.querySelector('#projectModalOverlay .do-modal-subtitle');
        if (subtitle) {
            subtitle.textContent = locked
                ? 'After creation, only the due date can be changed.'
                : 'Organize related work into a personal project.';
        }
    }

    function openProjectModal(project = null) {
        const overlay = document.getElementById('projectModalOverlay');
        const form = document.getElementById('projectForm');
        if (!overlay || !form) return;
        form.reset();
        form.querySelectorAll('.do-error-message').forEach((el) => el.remove());
        form.querySelectorAll('.do-field-error').forEach((el) => el.classList.remove('do-field-error'));

        const title = document.getElementById('projectModalTitle');
        const submit = document.getElementById('projectSubmitBtn');
        const idInput = document.getElementById('projectEditId');
        const keyGroup = document.getElementById('projectKeyGroup');
        const keyDisplay = document.getElementById('projectKeyDisplay');

        if (project) {
            if (title) title.textContent = 'Edit Project';
            if (submit) submit.innerHTML = 'Save Changes';
            if (idInput) idInput.value = String(project.id);
            document.getElementById('projectName').value = project.name || '';
            if (keyGroup) keyGroup.style.display = 'flex';
            if (keyDisplay) {
                keyDisplay.value = project.key || '';
            }
            document.getElementById('projectDescription').value = project.description || '';
            document.getElementById('projectStatus').value = project.status || 'active';
            document.getElementById('projectPriority').value = project.priority || 'medium';
            document.getElementById('projectStartDate').value = project.start_date || '';
            document.getElementById('projectDueDate').value = project.due_date || '';
            setProjectModalLockedFields(true);
        } else {
            if (title) title.textContent = 'New Project';
            if (submit) submit.innerHTML = 'Create Project';
            if (idInput) idInput.value = '';
            if (keyGroup) keyGroup.style.display = 'none';
            if (keyDisplay) keyDisplay.value = '';
            document.getElementById('projectStatus').value = 'active';
            document.getElementById('projectPriority').value = 'medium';
            setProjectModalLockedFields(false);
        }

        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('is-open');
        document.body.classList.add('do-modal-open');

        setTimeout(() => {
            const focusEl = project
                ? document.getElementById('projectDueDate')
                : document.getElementById('projectName');
            focusEl?.focus();
        }, 200);
    }

    function closeProjectModal() {
        const overlay = document.getElementById('projectModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('do-modal-open');
        setProjectModalLockedFields(false);
    }

    async function handleProjectSubmit(e) {
        e.preventDefault();
        if (state.isSubmitting) return;
        const form = e.target;
        const submitBtn = document.getElementById('projectSubmitBtn');
        form.querySelectorAll('.do-error-message').forEach((el) => el.remove());
        form.querySelectorAll('.do-field-error').forEach((el) => el.classList.remove('do-field-error'));

        const original = submitBtn.innerHTML;
        state.isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `${DevOSHourglass.html('xs')} Saving...`;

        const data = new FormData(form);
        const id = data.get('id');
        const isEdit = Boolean(id);

        let payload;
        if (isEdit) {
            // After create: only due date is mutable
            payload = {
                due_date: data.get('due_date') || null,
            };
        } else {
            const name = String(data.get('name') || '').trim();
            if (!name) {
                const nameInput = form.querySelector('[name="name"]');
                if (nameInput) {
                    nameInput.classList.add('do-field-error');
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'do-error-message';
                    errorMsg.style.cssText = 'color:#e53e3e;font-size:12px;margin-top:4px;';
                    errorMsg.textContent = 'Project name is required.';
                    nameInput.parentNode.appendChild(errorMsg);
                }
                state.isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = original;
                return;
            }

            payload = {
                name,
                description: data.get('description') || null,
                status: data.get('status'),
                priority: data.get('priority'),
                start_date: data.get('start_date') || null,
                due_date: data.get('due_date') || null,
            };
        }

        try {
            const response = await fetch(id ? `/projects/${id}/update` : '/projects', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    Object.keys(result.errors).forEach((field) => {
                        const inputEl = form.querySelector(`[name="${field}"]`);
                        if (inputEl) {
                            inputEl.classList.add('do-field-error');
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'do-error-message';
                            errorMsg.style.cssText = 'color:#e53e3e;font-size:12px;margin-top:4px;';
                            errorMsg.textContent = result.errors[field][0];
                            inputEl.parentNode.appendChild(errorMsg);
                        }
                    });
                } else {
                    showToast('Unable to save project. Please try again.');
                }
                return;
            }
            closeProjectModal();
            showToast(id ? 'Project updated successfully' : 'Project created successfully');
            const saved = result.project;
            if (saved) {
                if (id) {
                    const prevCard = document.querySelector(`.project-card[data-project-id="${id}"]`);
                    const previous = prevCard ? {
                        status: prevCard.dataset.status,
                        priority: prevCard.dataset.priority,
                    } : null;
                    upsertProjectCard(saved, previous);
                } else {
                    upsertProjectCard(saved, null);
                }
            }
        } catch (error) {
            console.error(error);
            showToast('Unable to save project. Please try again.');
        } finally {
            state.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        }
    }

    function askArchive() {
        if (!state.activeProject) return;
        closeActions();
        const titleEl = document.getElementById('pjArchiveProjectTitle');
        if (titleEl) titleEl.textContent = state.activeProject.name;
        const overlay = document.getElementById('pjArchiveModalOverlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('is-open'));
    }

    function closeArchiveModal() {
        const overlay = document.getElementById('pjArchiveModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        setTimeout(() => { overlay.style.display = 'none'; }, 200);
    }

    async function confirmArchive() {
        if (!state.activeProject || state.isArchiving) return;
        state.isArchiving = true;
        const confirmBtn = document.getElementById('pjArchiveConfirmBtn');
        const original = confirmBtn?.innerHTML;
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Archiving...';
        }
        try {
            const response = await fetch(`/projects/${state.activeProject.id}/archive`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            });
            if (!response.ok) throw new Error('Archive failed');
            const result = await response.json();
            closeArchiveModal();
            showToast('Project archived');
            const prevCard = document.querySelector(`.project-card[data-project-id="${state.activeProject.id}"]`);
            const previous = prevCard ? {
                status: prevCard.dataset.status,
                priority: prevCard.dataset.priority,
            } : { status: state.activeProject.status, priority: state.activeProject.priority };
            if (result.project) {
                upsertProjectCard(result.project, previous);
            } else {
                removeProjectCard(state.activeProject.id);
            }
        } catch (error) {
            console.error(error);
            showToast('Unable to archive project.');
        } finally {
            state.isArchiving = false;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = original || 'Archive';
            }
        }
    }

    function askDelete() {
        if (!state.activeProject) return;
        closeActions();
        const titleEl = document.getElementById('pjDeleteProjectTitle');
        if (titleEl) titleEl.textContent = state.activeProject.name;
        const overlay = document.getElementById('pjDeleteModalOverlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('is-open'));
    }

    function closeDeleteModal() {
        const overlay = document.getElementById('pjDeleteModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        setTimeout(() => { overlay.style.display = 'none'; }, 200);
    }

    async function confirmDelete() {
        if (!state.activeProject || state.isDeleting) return;
        state.isDeleting = true;
        const confirmBtn = document.getElementById('pjDeleteConfirmBtn');
        const original = confirmBtn?.innerHTML;
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Deleting...';
        }
        const deletedId = state.activeProject.id;
        const deletedName = state.activeProject.name;
        const prevCard = document.querySelector(`.project-card[data-project-id="${deletedId}"]`);
        const prevStatus = prevCard?.dataset.status || state.activeProject.status;
        const prevPriority = prevCard?.dataset.priority || state.activeProject.priority;
        try {
            const response = await fetch(`/projects/${deletedId}/delete`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            });
            if (!response.ok) throw new Error('Delete failed');
            closeDeleteModal();
            if (window.DevOSAlert) {
                window.DevOSAlert.deleted('done successfully :)', `"${deletedName}" deleted. Tasks moved to DailyOps.`);
            } else {
                showToast('Project deleted. Tasks moved to DailyOps.');
            }
            window.DevOSNotifications?.refresh();
            bumpCount('data-pj-status-count', prevStatus, -1);
            bumpCount('data-pj-priority-count', prevPriority, -1);
            removeProjectCard(deletedId);
            state.activeProject = null;
        } catch (error) {
            console.error(error);
            if (window.DevOSAlert) {
                window.DevOSAlert.error('Delete failed', 'Unable to delete project.');
            } else {
                showToast('Unable to delete project.');
            }
        } finally {
            state.isDeleting = false;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = original || 'Confirm';
            }
        }
    }

    function findProjectFromCard(id) {
        const card = document.querySelector(`.project-card[data-project-id="${id}"]`);
        if (!card) return null;
        return {
            id: Number(id),
            name: card.querySelector('.project-card-title')?.textContent || '',
            key: card.dataset.key || card.querySelector('.project-key-badge')?.textContent || '',
            status: card.dataset.status || '',
            priority: card.dataset.priority || '',
        };
    }

    async function fetchProject(id) {
        const response = await fetch(`/projects/${id}/data`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Failed');
        const result = await response.json();
        return result.project;
    }

    function init() {
        if (state.initialized || !document.querySelector('[data-projects-root]')) return;
        state.initialized = true;

        document.getElementById('pjSearchInput')?.addEventListener('keyup', () => {
            clearTimeout(state.debounceTimer);
            state.debounceTimer = setTimeout(() => loadProjects(), 300);
        });

        document.querySelectorAll('#pjStatusFilter .dailyops-filter-btn, #pjPriorityFilter .dailyops-filter-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                const filter = btn.closest('.dailyops-filter');
                const isOpen = filter.classList.contains('is-open');
                document.querySelectorAll('.projects-page .dailyops-filter.is-open').forEach((el) => el.classList.remove('is-open'));
                if (!isOpen) filter.classList.add('is-open');
            });
        });

        document.querySelectorAll('#pjStatusFilter input[type="checkbox"]').forEach((cb) => {
            cb.addEventListener('change', () => updateFilterLabel('pjStatusFilter', 'Status'));
        });
        document.querySelectorAll('#pjPriorityFilter input[type="checkbox"]').forEach((cb) => {
            cb.addEventListener('change', () => updateFilterLabel('pjPriorityFilter', 'Priority'));
        });
        document.querySelectorAll('[data-pj-clear-filters]').forEach((btn) => {
            btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); clearFilters(); });
        });
        document.querySelectorAll('.projects-page .dailyops-filter-search input').forEach((input) => {
            input.addEventListener('keyup', () => {
                const q = input.value.toLowerCase();
                input.closest('.dailyops-filter-menu')?.querySelectorAll('.dailyops-filter-option').forEach((opt) => {
                    const text = opt.querySelector('.do-opt-text')?.textContent.toLowerCase() || '';
                    opt.style.display = text.includes(q) ? 'flex' : 'none';
                });
            });
        });

        document.getElementById('btnOpenAddProject')?.addEventListener('click', () => openProjectModal());
        document.querySelectorAll('[data-open-add-project]').forEach((el) => el.addEventListener('click', () => openProjectModal()));
        document.querySelectorAll('[data-close-project-modal]').forEach((el) => el.addEventListener('click', closeProjectModal));
        document.getElementById('projectForm')?.addEventListener('submit', handleProjectSubmit);

        document.getElementById('pjActionOpenBtn')?.addEventListener('click', () => {
            if (state.activeProject) window.location.href = `/projects/${state.activeProject.id}`;
        });
        document.getElementById('pjActionEditBtn')?.addEventListener('click', async () => {
            closeActions();
            try {
                const project = await fetchProject(state.activeProject.id);
                openProjectModal(project);
            } catch (e) {
                showToast('Unable to load project.');
            }
        });
        document.getElementById('pjActionArchiveBtn')?.addEventListener('click', askArchive);
        document.getElementById('pjActionDeleteBtn')?.addEventListener('click', askDelete);
        document.getElementById('pjArchiveCloseBtn')?.addEventListener('click', closeArchiveModal);
        document.getElementById('pjArchiveCancelBtn')?.addEventListener('click', closeArchiveModal);
        document.getElementById('pjArchiveConfirmBtn')?.addEventListener('click', confirmArchive);
        document.getElementById('pjDeleteCloseBtn')?.addEventListener('click', closeDeleteModal);
        document.getElementById('pjDeleteCancelBtn')?.addEventListener('click', closeDeleteModal);
        document.getElementById('pjDeleteConfirmBtn')?.addEventListener('click', confirmDelete);

        document.getElementById('pjGrid')?.addEventListener('click', (event) => {
            const menuBtn = event.target.closest('.project-card-menu-btn');
            if (menuBtn) {
                event.stopPropagation();
                const id = menuBtn.dataset.projectId;
                openActions(menuBtn, findProjectFromCard(id));
                return;
            }
            const card = event.target.closest('.project-card');
            if (card) window.location.href = `/projects/${card.dataset.projectId}`;
        });

        document.addEventListener('click', (event) => {
            document.querySelectorAll('.projects-page .dailyops-filter.is-open').forEach((el) => {
                if (!el.contains(event.target)) el.classList.remove('is-open');
            });
            const menu = document.getElementById('pjGlobalActionsMenu');
            if (menu?.classList.contains('is-open') && !menu.contains(event.target) && !event.target.closest('.project-card-menu-btn')) {
                closeActions();
            }
            const overlay = document.getElementById('projectModalOverlay');
            if (overlay?.classList.contains('is-open') && event.target === overlay) closeProjectModal();
        });

        loadProjects();
    }

    return { init, loadProjects, openProjectModal };
})();

function bootNavbarSearch() {
    const wrap = document.querySelector('.dp-search');
    const input = wrap?.querySelector('.dp-search-input');
    const icon = wrap?.querySelector('.dp-search-icon');
    if (!wrap || !input) return;

    icon?.addEventListener('click', () => input.focus());
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            input.value = '';
            input.blur();
        }
    });
}

function bootApp() {
    DevOSSidebar.init();
    LogoutSwipe.init();
    bootNavbarSearch();
    DevOSSounds.setPrefs(window.DevOSNotificationPrefs);
    DevOSAlert.init();
    DevOSNotifications.init();
    DailyOps.init();
    Projects.init();
    Transactions.init();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
} else {
    bootApp();
}
