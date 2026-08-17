{{-- Header notification bell + dropdown panel --}}
@php
    $notifAdmin = \Illuminate\Support\Facades\DB::table('admins')->where('id', session('admin_id'))->first()
        ?: \Illuminate\Support\Facades\DB::table('admins')->first();
    $notifAvatar = $notifAdmin && $notifAdmin->profile_photo
        ? asset('storage/' . $notifAdmin->profile_photo)
        : 'https://api.dicebear.com/7.x/notionists/svg?seed=devos';
@endphp

<div class="devos-notif-wrap" id="devosNotifWrap" data-avatar="{{ $notifAvatar }}">
    <button type="button" class="devos-notif-btn" id="devosNotifBtn" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
        <svg class="devos-notif-bell" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        <span class="devos-notif-badge" id="devosNotifBadge">0</span>
    </button>

    <div class="devos-notif-panel" id="devosNotifPanel" aria-hidden="true" role="dialog" aria-label="Notifications">
        <div class="devos-notif-header">
            <h2>Notifications</h2>
            <div class="devos-notif-tabs" role="tablist">
                <button type="button" class="devos-notif-tab is-active" data-filter="all" role="tab">All</button>
                <button type="button" class="devos-notif-tab" data-filter="unread" role="tab">Unread</button>
            </div>
        </div>
        <div class="devos-notif-actions">
            <button type="button" class="devos-notif-mark-all" id="devosNotifMarkAll">Mark all as read</button>
        </div>
        <div class="devos-notif-list-wrap" id="devosNotifListWrap">
            <ul class="devos-notif-list" id="devosNotifList"></ul>
        </div>
    </div>
</div>
