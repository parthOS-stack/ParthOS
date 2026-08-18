@extends('layouts.app')

@section('content')
    <div class="premium-shell-banner mb-6">
        <div>
            <p class="premium-shell-kicker">DailyOps</p>
            <h2 class="premium-shell-title">Stay locked on today’s execution.</h2>
            <p class="premium-shell-copy">Your task board below stays untouched, but the shell now matches the upgraded dashboard language.</p>
        </div>
    </div>
    @include('components.dailyops-task-board')
@endsection
