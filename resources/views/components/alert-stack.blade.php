{{-- Bottom-right alert stack host + optional flash payload --}}
<div id="devosAlertStack" class="devos-alert-stack" aria-live="polite" aria-relevant="additions"></div>

@php
    $flashType = null;
    $flashTitle = null;
    $flashDesc = null;

    if (session('success')) {
        $flashType = 'success';
        $flashTitle = 'done successfully :)';
        $flashDesc = session('success');
    } elseif (session('error')) {
        $flashType = 'error';
        $flashTitle = 'Something went wrong';
        $flashDesc = session('error');
    } elseif (session('alert')) {
        $alert = session('alert');
        $flashType = $alert['type'] ?? 'info';
        $flashTitle = $alert['title'] ?? 'Alert';
        $flashDesc = $alert['description'] ?? '';
    }
@endphp

@if($flashType)
    <div id="devosAlertFlash"
         data-type="{{ $flashType }}"
         data-title="{{ $flashTitle }}"
         data-description="{{ $flashDesc }}"
         hidden></div>
@endif
