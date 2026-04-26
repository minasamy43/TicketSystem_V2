@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kb-content.css') }}">
@endpush

@section('content')
    <script>
        function toggleArticle(el) {
            const wrapper = el.closest('.article-wrapper');
            const isActive = wrapper.classList.contains('active');

            // Close all others
            document.querySelectorAll('.article-wrapper').forEach(item => {
                item.classList.remove('active');
            });

            if (!isActive) {
                wrapper.classList.add('active');
            }
        }
    </script>

    <div class="kb-container">
        <a href="{{ route('knowledge.base') }}" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Knowledge Base
        </a>

        <div class="cat-hero">
            <div class="cat-icon-wrap">{{ $category->icon }}</div>
            <div>
                <h1>{{ $category->title }}</h1>
                <p>{{ $category->description }}</p>
            </div>
        </div>

        <div class="article-list">
            @forelse($category->articles as $article)
                <div class="article-wrapper" style="margin-bottom: 1rem;">
                    <div class="article-item" onclick="toggleArticle(this)" style="cursor: pointer;">
                        <h3 class="article-title">
                            <span style="color: #d4af53; opacity: 0.8;">📄</span>
                            {{ $article->title }}
                        </h3>
                        <div class="article-arrow" style="transition: transform 0.3s ease;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="article-content-container">
                        @if($article->content)
                            {!! $article->content !!}
                        @else
                            <p class="text-muted" style="margin-bottom: 0; font-style: italic;">This article doesn't have any content yet.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 4rem 1rem; color: #777;">
                    <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">📭</div>
                    <p>No articles available in this category yet.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
