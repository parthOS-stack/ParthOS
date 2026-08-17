@extends('layouts.app')

@section('content')
<div class="project-detail-page" data-project-detail-root data-project-id="{{ $project->id }}">
    <div class="project-detail-header dp-card">
        <div class="project-detail-header-top">
            <div class="project-detail-identity">
                <a href="{{ url('/projects') }}" class="project-back-btn group" type="button">
                    <span class="project-back-btn-slide">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" height="14" width="14" aria-hidden="true">
                            <path d="M224 480h640a32 32 0 1 1 0 64H224a32 32 0 0 1 0-64z" fill="currentColor"></path>
                            <path d="m237.248 512 265.408 265.344a32 32 0 0 1-45.312 45.312l-288-288a32 32 0 0 1 0-45.312l288-288a32 32 0 1 1 45.312 45.312L237.248 512z" fill="currentColor"></path>
                        </svg>
                    </span>
                    <span class="project-back-btn-label">Go Back</span>
                </a>

                <div class="project-detail-info-card">
                    <div class="project-detail-field">
                        <span class="project-detail-field-label">App name:</span>
                        <h1 class="project-detail-title">{{ $project->name }}</h1>
                    </div>
                    <div class="project-detail-field project-detail-field-key">
                        <span class="project-detail-field-label">Key:</span>
                        <span class="project-key-badge">{{ $project->key }}</span>
                    </div>
                </div>
            </div>

            <div class="project-detail-progress-wrap">
                <div class="project-progress-kicker">Project Progress</div>
                <div class="project-progress-percent">{{ $projectPayload['progress'] }}%</div>
                <div class="project-progress-bar">
                    <span style="width: {{ $projectPayload['progress'] }}%"></span>
                </div>
                <div class="project-detail-stats">
                    {{ $projectPayload['completed_tasks_count'] }} of {{ $projectPayload['tasks_count'] }} tasks completed
                </div>
            </div>
        </div>
        @if($project->description)
            <p class="project-detail-desc">{{ $project->description }}</p>
        @endif
    </div>

    @include('components.dailyops-task-board', ['projectId' => $project->id])
</div>
@endsection
