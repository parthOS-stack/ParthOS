{{--
  Dark delete confirmation popup (Uiverse-inspired)
  Buttons are side-by-side (Cancel | Confirm)

  Props:
    overlayId, titleId, cancelId, confirmId, closeId (optional)
    entityLabel — e.g. "project" / "task" (used in helper text)
--}}
@props([
    'overlayId' => 'devosDeleteOverlay',
    'titleId' => 'devosDeleteTitle',
    'cancelId' => 'devosDeleteCancelBtn',
    'confirmId' => 'devosDeleteConfirmBtn',
    'closeId' => null,
    'entityLabel' => 'item',
])

<div id="{{ $overlayId }}" class="devos-delete-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="devos-delete-popup group select-none" role="document">
        @if($closeId)
            <button type="button" class="devos-delete-x" id="{{ $closeId }}" aria-label="Close">×</button>
        @endif

        <div class="devos-delete-body">
            <svg
                fill="currentColor"
                viewBox="0 0 20 20"
                class="devos-delete-icon group-hover:animate-bounce"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <path
                    clip-rule="evenodd"
                    fill-rule="evenodd"
                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                ></path>
            </svg>

            <h2 class="devos-delete-heading">Are you sure?</h2>
            <p class="devos-delete-text">
                Do you really want to delete
                <strong id="{{ $titleId }}"></strong>?
                This process cannot be undone.
            </p>
            <p class="devos-delete-hint">
                @if($entityLabel === 'project')
                    Project tasks will become personal DailyOps tasks.
                @elseif($entityLabel === 'task')
                    This task will be permanently removed.
                @endif
            </p>
        </div>

        <div class="devos-delete-actions">
            <button type="button" class="devos-delete-btn devos-delete-btn-cancel" id="{{ $cancelId }}">
                Cancel
            </button>
            <button type="button" class="devos-delete-btn devos-delete-btn-confirm" id="{{ $confirmId }}">
                Confirm
            </button>
        </div>
    </div>
</div>
