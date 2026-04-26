@extends('layouts.app')

@section('title', 'Knowledge Base')
@section('breadcrumb', 'Knowledge Base')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/User-knowledge-base.css') }}">
@endpush

@section('content')
    <div class="kb-container">
        {{-- Hero --}}
        <header class="kb-hero">
            <h1>Knowledge Base</h1>
            <p>Search our comprehensive library of guides and answers or browse by category to find exactly what you need.
            </p>

            <div class="search-wrap">
                <span class="search-icon-fixed">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" class="search-input-prem" id="kbSearchInput"
                    placeholder="Search for articles, topics or keywords..." onkeyup="filterKB()">
            </div>
        </header>

        {{-- Categories --}}
        <div class="category-grid" id="kbGrid">
            @foreach($categories as $cat)
                <a href="{{ route('knowledge.category', $cat->slug) }}" class="category-card"
                    data-title="{{ strtolower($cat->title) }}" data-desc="{{ strtolower($cat->description) }}">
                    <div class="cat-icon-wrap">{{ $cat->icon }}</div>
                    <h3>{{ $cat->title }}</h3>
                    <p>{{ $cat->description }}</p>
                    <div class="article-count">
                        <span>{{ count($cat->articles) }} Articles</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- FAQ --}}
        <section class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="max-width: 750px; margin: 0 auto;">
                @foreach($faqs as $index => $faq)
                    <div class="faq-item" onclick="toggleFaq(this)">
                        <div class="faq-question">
                            {{ $faq->question }}
                            <span class="faq-toggle-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </div>
                        <div class="faq-answer">
                            {{ $faq->answer }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <script>
        function filterKB() {
            const input = document.getElementById('kbSearchInput').value.toLowerCase();

            // Filter Categories
            const cards = document.querySelectorAll('.category-card');
            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const desc = card.getAttribute('data-desc');

                if (title.includes(input) || desc.includes(input)) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                } else {
                    card.style.display = 'none';
                    card.style.opacity = '0';
                }
            });

            // Filter FAQs
            const faqs = document.querySelectorAll('.faq-item');
            faqs.forEach(faq => {
                const questionElement = faq.querySelector('.faq-question');
                const answerElement = faq.querySelector('.faq-answer');

                const question = questionElement ? questionElement.innerText.toLowerCase() : '';
                const answer = answerElement ? answerElement.innerText.toLowerCase() : '';

                if (question.includes(input) || answer.includes(input)) {
                    faq.style.display = '';
                } else {
                    faq.style.display = 'none';
                }
            });
        }

        function toggleFaq(el) {
            const isActive = el.classList.contains('active');

            // Close all others
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });

            if (!isActive) {
                el.classList.add('active');
            }
        }
    </script>
@endsection