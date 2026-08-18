@extends('layouts.app')

@section('header_title', 'Documentation')

@section('content')
<div class="docs-landing">
    <header class="docs-landing-bar">
        <span>Documentation</span>
        <span>{{ str_pad((string) count($sections), 2, '0', STR_PAD_LEFT) }} Sections</span>
    </header>

    <div class="docs-panels" role="list" data-docs-panels>
        @foreach ($sections as $index => $section)
            <article
                class="docs-panel{{ $index === 0 ? ' is-open' : '' }}"
                role="listitem"
                tabindex="0"
                data-docs-slug="{{ $section['slug'] }}"
                aria-label="{{ $section['title'] }}"
                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
            >
                <span class="docs-panel-index">{{ $section['index'] }}</span>
                <span class="docs-panel-spine">{{ $section['title'] }}</span>
                <span class="docs-panel-watermark" aria-hidden="true">{{ $section['title'] }}</span>

                <div class="docs-panel-body">
                    <div class="docs-panel-content docs-card">
                        {!! $section['html'] !!}
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
