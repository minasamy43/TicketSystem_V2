@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kb-content.css') }}">
@endpush

@section('content')
    <div class="kb-container">
        <a href="{{ route('knowledge.category', $article->category->slug) }}" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to {{ $article->category->title }}
        </a>

        <div class="article-content-wrapper">
            <div class="article-header">
                <div class="cat-badge">{{ $article->category->icon }} {{ $article->category->title }}</div>
                <h1>{{ $article->title }}</h1>
            </div>

            <div class="article-body">
                @if($article->content)
                    {!! $article->content !!}
                @else
                    <p class="text-muted" style="font-style: italic;">This article doesn't have any content yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
