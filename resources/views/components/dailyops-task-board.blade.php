{{-- ============================================================
         DAILYOPS — TASK TOOLBAR
    ============================================================ --}}
    <div class="dailyops-toolbar" data-dailyops-root @if(!empty($projectId)) data-project-id="{{ $projectId }}" @endif>
        <div class="dailyops-toolbar-left">
            <!-- Search -->
            <div class="dailyops-search-wrap">
                <svg class="dailyops-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" class="dailyops-search-input" placeholder="Search tasks..." id="doSearchInput" autocomplete="off">
            </div>

            <!-- Status Filter -->
            <div class="dailyops-filter" id="doStatusFilter">
                <button type="button" class="dailyops-filter-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    <span class="dailyops-filter-label">Status</span>
                </button>
                <div class="dailyops-filter-menu" onclick="event.stopPropagation()">
                    <div class="dailyops-filter-search">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" placeholder="Search status..." autocomplete="off">
                    </div>
                    <div class="dailyops-filter-options">
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="backlog" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">◉</span>
                            <span class="do-opt-text">Backlog</span>
                            <span class="do-opt-count" data-status-count="backlog">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="todo" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">○</span>
                            <span class="do-opt-text">Todo</span>
                            <span class="do-opt-count" data-status-count="todo">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="in_progress" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">◷</span>
                            <span class="do-opt-text">In Progress</span>
                            <span class="do-opt-count" data-status-count="in_progress">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="done" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">✓</span>
                            <span class="do-opt-text">Done</span>
                            <span class="do-opt-count" data-status-count="done">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="canceled" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">⊘</span>
                            <span class="do-opt-text">Canceled</span>
                            <span class="do-opt-count" data-status-count="canceled">0</span>
                        </label>
                    </div>
                    <div class="dailyops-filter-footer">
                        <button type="button" class="dailyops-filter-clear" data-clear-filters>Clear</button>
                    </div>
                </div>
            </div>

            <!-- Priority Filter -->
            <div class="dailyops-filter" id="doPriorityFilter">
                <button type="button" class="dailyops-filter-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    <span class="dailyops-filter-label">Priority</span>
                </button>
                <div class="dailyops-filter-menu" onclick="event.stopPropagation()">
                    <div class="dailyops-filter-search">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" placeholder="Search priority..." autocomplete="off">
                    </div>
                    <div class="dailyops-filter-options">
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="urgent" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">○</span>
                            <span class="do-opt-text">Urgent</span>
                            <span class="do-opt-count" data-priority-count="urgent">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="high" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">↑</span>
                            <span class="do-opt-text">High</span>
                            <span class="do-opt-count" data-priority-count="high">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="medium" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">→</span>
                            <span class="do-opt-text">Medium</span>
                            <span class="do-opt-count" data-priority-count="medium">0</span>
                        </label>
                        <label class="dailyops-filter-option">
                            <x-checkbox-pro value="low" style="font-size: 9.375px; margin-right: 2px;" />
                            <span class="do-opt-icon">↓</span>
                            <span class="do-opt-text">Low</span>
                            <span class="do-opt-count" data-priority-count="low">0</span>
                        </label>
                    </div>
                    <div class="dailyops-filter-footer">
                        <button type="button" class="dailyops-filter-clear" data-clear-filters>Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dailyops-toolbar-right">
            <button type="button" id="btnOpenAddTask" class="dailyops-add-task" aria-label="Add new task">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Task
            </button>
        </div>
    </div>

    {{-- ============================================================
         PAGE CONTENT AREA (Tasks List)
    ============================================================ --}}
    <!-- Loading State -->
    <div id="doLoadingState" class="dp-card flex items-center justify-center" style="min-height: 260px;">
        <div class="text-center">
            <x-hourglass-loader />
            <p class="text-[var(--color-dp-text-muted)] mt-4" style="font-size:14px; font-weight: 500;">Loading tasks...</p>
        </div>
    </div>

    <!-- Task List UI -->
    <div class="dailyops-task-container" style="display: none;">
        <div class="dailyops-task-table-wrap">
            <table class="dailyops-task-table">
                <thead>
                    <tr>
                        <th class="do-col-check">
                            <x-checkbox-pro inputClass="dailyops-task-checkbox" id="doSelectAllCheckbox" style="font-size: 11.25px;" aria-label="Select all tasks" />
                        </th>
                        <th class="do-col-task">Task</th>
                        <th class="do-col-title">Title</th>
                        <th class="do-col-project">Project</th>
                        <th class="do-col-status">Status</th>
                        <th class="do-col-priority">Priority</th>
                        <th class="do-col-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Tasks will be populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State UI -->
    <div class="dailyops-empty-state dp-card items-center justify-center" style="min-height: 260px; display: none;">
        <div class="text-center">
            <div style="width:56px;height:56px;background:#f3f1ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6558d3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <p class="font-semibold text-[#1a1a24] mb-1" style="font-size:15px;">No tasks yet</p>
            <p class="text-[var(--color-dp-text-muted)]" style="font-size:13px;">Start by adding your first task for today.</p>
            <button
                type="button"
                data-open-add-task
                style="margin-top:18px;display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:#6558d3;color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;box-shadow:0 4px 14px rgba(101,88,211,0.3);transition:all 0.18s;"
                onmouseover="this.style.background='#4133B7'" onmouseout="this.style.background='#6558d3'"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Your First Task
            </button>
        </div>
    </div>


    {{-- ============================================================
         BULK ACTIONS MENU (appears when rows are selected)
    ============================================================ --}}
    <div id="doBulkActionsMenu" class="dailyops-bulk-menu" aria-hidden="true">
        <div class="dailyops-bulk-menu-header" title="Drag to move">
            <span id="doBulkSelectedCount">0 selected</span>
            <span class="dailyops-bulk-drag-hint" aria-hidden="true">⋮⋮</span>
        </div>
        <button type="button" class="dailyops-task-action" id="doBulkDeselectBtn">Deselect All</button>
        <button type="button" class="dailyops-task-action" id="doBulkDuplicateBtn">Duplicate Selected</button>
        <div class="dailyops-action-divider"></div>

        <div class="dailyops-bulk-group" data-bulk-group="progress">
            <button type="button" class="dailyops-task-action dailyops-bulk-group-btn" data-bulk-group-toggle="progress" aria-expanded="false">
                <span>Progress</span>
                <span class="dailyops-bulk-group-chevron" aria-hidden="true">›</span>
            </button>
            <div class="dailyops-bulk-submenu" role="menu">
                <button type="button" class="dailyops-task-action" data-bulk-status="todo">Mark as Todo</button>
                <button type="button" class="dailyops-task-action" data-bulk-status="in_progress">Mark as In Progress</button>
                <button type="button" class="dailyops-task-action" data-bulk-status="done">Mark as Done</button>
                <button type="button" class="dailyops-task-action" data-bulk-status="backlog">Mark as Backlog</button>
                <button type="button" class="dailyops-task-action" data-bulk-status="canceled">Mark as Canceled</button>
            </div>
        </div>

        <div class="dailyops-bulk-group" data-bulk-group="priority">
            <button type="button" class="dailyops-task-action dailyops-bulk-group-btn" data-bulk-group-toggle="priority" aria-expanded="false">
                <span>Priority</span>
                <span class="dailyops-bulk-group-chevron" aria-hidden="true">›</span>
            </button>
            <div class="dailyops-bulk-submenu" role="menu">
                <button type="button" class="dailyops-task-action" data-bulk-priority="urgent">Urgent</button>
                <button type="button" class="dailyops-task-action" data-bulk-priority="high">High</button>
                <button type="button" class="dailyops-task-action" data-bulk-priority="medium">Medium</button>
                <button type="button" class="dailyops-task-action" data-bulk-priority="low">Low</button>
            </div>
        </div>

        <div class="dailyops-action-divider"></div>
        <button type="button" class="dailyops-task-action dailyops-task-action-danger" id="doBulkDeleteBtn">Delete Selected</button>
    </div>

    {{-- ============================================================
         GLOBAL TASK ACTIONS MENU
    ============================================================ --}}
    <div id="doGlobalActionsMenu" class="dailyops-task-actions-menu">
        <button type="button" class="dailyops-task-action" id="doActionMarkDoneBtn">
            <span id="doActionDoneText">Mark as Done</span>
        </button>
        <button type="button" class="dailyops-task-action" id="doActionEditBtn">Edit Task</button>
        <button type="button" class="dailyops-task-action" id="doActionCopyBtn">Make a Copy</button>
        <div class="dailyops-action-divider"></div>
        <button type="button" class="dailyops-task-action dailyops-task-action-danger" id="doActionDeleteBtn">Delete Task</button>
    </div>

    {{-- ============================================================
         DELETE CONFIRMATION MODAL
    ============================================================ --}}
    <x-delete-popup
        overlay-id="doDeleteModalOverlay"
        title-id="doDeleteTaskTitle"
        cancel-id="doDeleteCancelBtn"
        confirm-id="doDeleteConfirmBtn"
        close-id="doDeleteCloseBtn"
        entity-label="task"
    />

    {{-- ============================================================
         REUSABLE ADD TASK MODAL
    ============================================================ --}}
    @include('components.add-task-modal')
