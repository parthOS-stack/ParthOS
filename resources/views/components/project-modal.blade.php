{{-- Project Create / Edit Modal — matches Add Task modal styling --}}
<div id="projectModalOverlay" class="do-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="projectModalTitle">
    <div class="do-modal-wrapper" id="projectModalWrapper">
        <div class="plan do-modal-card" role="document">
            <div class="inner do-modal-inner">
                <div class="do-modal-header">
                    <div>
                        <h2 class="title do-modal-title" id="projectModalTitle">New Project</h2>
                        <p class="info do-modal-subtitle">Organize related work into a personal project.</p>
                    </div>
                    <button type="button" class="do-modal-close" data-close-project-modal aria-label="Close modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <form id="projectForm" class="do-modal-form">
                    @csrf
                    <input type="hidden" id="projectEditId" name="id" value="">

                    <div class="do-field-group">
                        <label class="do-field-label" for="projectName">Project Name <span class="do-required">*</span></label>
                        <input type="text" id="projectName" name="name" class="do-field-input" placeholder="e.g. Doctor Appointment Platform" required maxlength="255" autocomplete="off" />
                    </div>

                    <div class="do-field-group" id="projectKeyGroup" style="display: none;">
                        <label class="do-field-label" for="projectKeyDisplay">Project Key / Code</label>
                        <input type="text" id="projectKeyDisplay" class="do-field-input do-field-readonly" readonly tabindex="-1" />
                        <p class="do-optional" id="projectKeyHint" style="margin:4px 0 0;">Generated automatically. Cannot be changed after creation.</p>
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="projectDescription">Short Description <span class="do-optional">(optional)</span></label>
                        <textarea id="projectDescription" name="description" class="do-field-textarea" placeholder="What is this project about?" rows="3"></textarea>
                    </div>

                    <div class="do-field-row">
                        <div class="do-field-group">
                            <label class="do-field-label" for="projectStatus">Status</label>
                            <select id="projectStatus" name="status" class="do-field-select">
                                <option value="planning">Planning</option>
                                <option value="active" selected>Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="do-field-group">
                            <label class="do-field-label" for="projectPriority">Priority</label>
                            <select id="projectPriority" name="priority" class="do-field-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="do-field-row">
                        <div class="do-field-group">
                            <label class="do-field-label" for="projectStartDate">Start Date <span class="do-optional">(optional)</span></label>
                            <input type="date" id="projectStartDate" name="start_date" class="do-field-input" />
                        </div>
                        <div class="do-field-group">
                            <label class="do-field-label" for="projectDueDate">Due Date <span class="do-optional">(optional)</span></label>
                            <input type="date" id="projectDueDate" name="due_date" class="do-field-input" />
                            <p class="do-optional" id="projectEditLockHint" style="display:none;margin:4px 0 0;">
                                Only due date can be changed after the project is created.
                            </p>
                        </div>
                    </div>

                    <div class="do-modal-actions">
                        <button type="button" class="do-btn-cancel" data-close-project-modal>Cancel</button>
                        <button type="submit" class="button do-btn-create" id="projectSubmitBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
