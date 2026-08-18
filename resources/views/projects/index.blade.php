@extends('layouts.app')

@section('content')
<div class="projects-page" data-projects-root>
    <div class="premium-shell-banner mb-6">
        <div>
            <p class="premium-shell-kicker">Projects</p>
            <h2 class="premium-shell-title">Track every project with cleaner momentum.</h2>
            <p class="premium-shell-copy">Filters, cards, and modals below keep their existing behavior while the page gets a lighter premium shell.</p>
        </div>
    </div>

    <div class="dailyops-toolbar">
        <div class="dailyops-toolbar-left">
            <div class="dailyops-search-wrap">
                <svg class="dailyops-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" class="dailyops-search-input" placeholder="Search projects..." id="pjSearchInput" autocomplete="off">
            </div>

            <div class="dailyops-filter" id="pjStatusFilter">
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
                        <label class="dailyops-filter-option"><input type="checkbox" value="planning"><span class="do-opt-icon">◉</span><span class="do-opt-text">Planning</span><span class="do-opt-count" data-pj-status-count="planning">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="active"><span class="do-opt-icon">○</span><span class="do-opt-text">Active</span><span class="do-opt-count" data-pj-status-count="active">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="on_hold"><span class="do-opt-icon">◷</span><span class="do-opt-text">On Hold</span><span class="do-opt-count" data-pj-status-count="on_hold">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="completed"><span class="do-opt-icon">✓</span><span class="do-opt-text">Completed</span><span class="do-opt-count" data-pj-status-count="completed">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="archived"><span class="do-opt-icon">⊘</span><span class="do-opt-text">Archived</span><span class="do-opt-count" data-pj-status-count="archived">0</span></label>
                    </div>
                    <div class="dailyops-filter-footer">
                        <button type="button" class="dailyops-filter-clear" data-pj-clear-filters>Clear</button>
                    </div>
                </div>
            </div>

            <div class="dailyops-filter" id="pjPriorityFilter">
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
                        <label class="dailyops-filter-option"><input type="checkbox" value="urgent"><span class="do-opt-icon">○</span><span class="do-opt-text">Urgent</span><span class="do-opt-count" data-pj-priority-count="urgent">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="high"><span class="do-opt-icon">↑</span><span class="do-opt-text">High</span><span class="do-opt-count" data-pj-priority-count="high">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="medium"><span class="do-opt-icon">→</span><span class="do-opt-text">Medium</span><span class="do-opt-count" data-pj-priority-count="medium">0</span></label>
                        <label class="dailyops-filter-option"><input type="checkbox" value="low"><span class="do-opt-icon">↓</span><span class="do-opt-text">Low</span><span class="do-opt-count" data-pj-priority-count="low">0</span></label>
                    </div>
                    <div class="dailyops-filter-footer">
                        <button type="button" class="dailyops-filter-clear" data-pj-clear-filters>Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dailyops-toolbar-right">
            <button type="button" id="btnOpenAddProject" class="dailyops-add-task" aria-label="Add new project">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                New Project
            </button>
        </div>
    </div>

    <div id="pjLoadingState" class="dp-card flex items-center justify-center" style="min-height: 260px;">
        <div class="text-center">
            <x-hourglass-loader />
            <p class="text-[var(--color-dp-text-muted)] mt-4" style="font-size:14px; font-weight: 500;">Loading projects...</p>
        </div>
    </div>

    <div id="pjGrid" class="projects-grid" style="display:none;"></div>

    <div id="pjEmptyState" class="dailyops-empty-state dp-card items-center justify-center" style="min-height: 260px; display: none;">
        <div class="text-center">
            <div style="width:56px;height:56px;background:#f3f1ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6558d3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                </svg>
            </div>
            <p class="font-semibold text-[var(--color-dp-text-main)] mb-1" style="font-size:15px;">No projects yet</p>
            <p class="text-[var(--color-dp-text-muted)]" style="font-size:13px;">Create your first project to organize your work.</p>
            <button type="button" data-open-add-project class="dailyops-add-task" style="margin-top:18px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                New Project
            </button>
        </div>
    </div>

    <div id="pjGlobalActionsMenu" class="dailyops-task-actions-menu">
        <button type="button" class="dailyops-task-action" id="pjActionOpenBtn">Open Project</button>
        <button type="button" class="dailyops-task-action" id="pjActionEditBtn">Edit Project</button>
        <button type="button" class="dailyops-task-action" id="pjActionArchiveBtn">Archive Project</button>
        <div class="dailyops-action-divider"></div>
        <button type="button" class="dailyops-task-action dailyops-task-action-danger" id="pjActionDeleteBtn">Delete Project</button>
    </div>

    <div id="pjArchiveModalOverlay" class="dailyops-delete-overlay">
        <div class="dailyops-task-delete-modal">
            <div class="do-delete-header">
                Archive Project?
                <button type="button" class="do-delete-close" id="pjArchiveCloseBtn">×</button>
            </div>
            <div class="do-delete-body">
                <p>Archive <br><strong id="pjArchiveProjectTitle"></strong>?</p>
                <p class="do-delete-warning">The project stays in your list as archived. Existing tasks are kept; new tasks cannot be added.</p>
            </div>
            <div class="do-delete-footer">
                <button type="button" class="do-btn-cancel" id="pjArchiveCancelBtn">Cancel</button>
                <button type="button" class="do-btn-danger" id="pjArchiveConfirmBtn">Archive</button>
            </div>
        </div>
    </div>

    <x-delete-popup
        overlay-id="pjDeleteModalOverlay"
        title-id="pjDeleteProjectTitle"
        cancel-id="pjDeleteCancelBtn"
        confirm-id="pjDeleteConfirmBtn"
        close-id="pjDeleteCloseBtn"
        entity-label="project"
    />

    @include('components.project-modal')
</div>
@endsection
