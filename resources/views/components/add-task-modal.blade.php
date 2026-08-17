{{--
    ====================================================
    REUSABLE COMPONENT: Add Task Modal
    ====================================================
    Usage (include anywhere):
        @include('components.add-task-modal')

    Then trigger with:
        openAddTaskModal() or data-open-add-task
    ====================================================
--}}

<!-- ===== ADD TASK MODAL OVERLAY ===== -->
<div id="addTaskModalOverlay" class="do-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="addTaskModalTitle">
    <div class="do-modal-wrapper" id="addTaskModalWrapper">

        <!-- Modal Card (reusing .plan visual language) -->
        <div class="plan do-modal-card" role="document">

            <!-- Inner section -->
            <div class="inner do-modal-inner">

                <!-- Header -->
                <div class="do-modal-header">
                    <div>
                        <h2 class="title do-modal-title" id="addTaskModalTitle">Add New Task</h2>
                        <p class="info do-modal-subtitle">Create and organize a task for your DailyOps workflow.</p>
                    </div>
                    <button type="button" class="do-modal-close" data-close-add-task
                        aria-label="Close modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form id="addTaskForm" class="do-modal-form">
                    @csrf
                    <input type="hidden" id="taskEditId" name="task_id" value="" />

                    <!-- 1. Task Title -->
                    <div class="do-field-group">
                        <label class="do-field-label" for="taskTitle">
                            Task Title <span class="do-required">*</span>
                        </label>
                        <input type="text" id="taskTitle" name="title" class="do-field-input"
                            placeholder="e.g. Complete Laravel API" required autocomplete="off" />
                    </div>

                    <!-- 2. Description -->
                    <div class="do-field-group">
                        <label class="do-field-label" for="taskDescription">
                            Description <span class="do-optional">(optional)</span>
                        </label>
                        <textarea id="taskDescription" name="description" class="do-field-textarea"
                            placeholder="Add some details about this task..." rows="3"></textarea>
                    </div>

                    <!-- 3. Due Date & Due Time (side by side) -->
                    <div class="do-field-row">
                        <div class="do-field-group">
                            <label class="do-field-label" for="taskDueDate">
                                Due Date <span class="do-required">*</span>
                            </label>
                            <input type="date" id="taskDueDate" name="due_date" class="do-field-input" required />
                        </div>
                        <div class="do-field-group">
                            <label class="do-field-label" for="taskDueTime">
                                Due Time <span class="do-optional">(optional)</span>
                            </label>
                            <input type="time" id="taskDueTime" name="due_time" class="do-field-input" />
                        </div>
                    </div>

                    <!-- 4. Priority & Project (side by side) -->
                    <div class="do-field-row">
                        <div class="do-field-group">
                            <label class="do-field-label" for="taskPriority">Priority</label>
                            <select id="taskPriority" name="priority" class="do-field-select">
                                <option value="low">🟢 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                                <option value="urgent">🚨 Urgent</option>
                            </select>
                        </div>
                        <div class="do-field-group">
                            <label class="do-field-label" for="taskProject">Project</label>
                            <select id="taskProject" name="project_id" class="do-field-select">
                                <option value="">🧘 Personal Task</option>
                            </select>
                        </div>
                    </div>

                    <!-- 5. Focus Task Toggle -->
                    <div class="do-field-group">
                        <label class="do-focus-toggle" for="taskFocus">
                            <input type="checkbox" id="taskFocus" name="is_focus" class="do-focus-checkbox" />
                            <span class="do-toggle-track">
                                <span class="do-toggle-thumb"></span>
                            </span>
                            <div>
                                <span class="do-toggle-label">Focus Task</span>
                                <span class="do-toggle-hint">Mark this as an important task</span>
                            </div>
                        </label>
                    </div>

                    <!-- 6. Reminder -->
                    <div class="do-field-group">
                        <label class="do-field-label" for="taskReminder">Reminder</label>
                        <select id="taskReminder" name="reminder" class="do-field-select">
                            <option value="none" selected>🔕 No reminder</option>
                            <option value="10min">⏰ 10 minutes before</option>
                            <option value="30min">⏰ 30 minutes before</option>
                            <option value="1hour">⏰ 1 hour before</option>
                            <option value="1day">📅 1 day before</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="do-modal-actions">
                        <button type="button" class="do-btn-cancel" data-close-add-task>
                            Cancel
                        </button>
                        <button type="submit" class="button do-btn-create" id="addTaskSubmitBtn">
                            <svg class="do-btn-create-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span id="addTaskSubmitLabel">Create Task</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
