@extends('dt-tables::pages.layouts.app')
@section('title', $page['title'].' — Docs')
@section('content')
    <div class="flex flex-col gap-6 lg:flex-row">
        {{-- Navigation --}}
        <nav class="lg:w-56 lg:shrink-0" aria-label="Documentation">
            <div class="space-y-5 lg:sticky lg:top-20 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto lg:pr-2">
                @foreach($sections as $section)
                    <div>
                        <h2 class="px-3 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $section['title'] }}</h2>
                        <ul class="mt-1">
                            @foreach($section['pages'] as $item)
                                <li>
                                    <a href="{{ route('dt-tables.docs.show', ['page' => $item['slug']]) }}"
                                       aria-current="{{ $item['slug'] === $page['slug'] ? 'page' : 'false' }}"
                                       class="block rounded-sm px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-200 aria-[current=page]:bg-primary-soft aria-[current=page]:font-medium aria-[current=page]:text-primary-darker">
                                        {{ $item['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        {{-- Page --}}
        <article class="docs-prose min-w-0 max-w-4xl flex-1" data-docs-article>
            <p class="text-xs font-semibold uppercase tracking-wide text-primary-darker">{{ $page['section'] }}</p>
            <h1 class="mt-1">{{ $page['title'] }}</h1>
            <p class="text-lg text-gray-500">{{ $page['description'] }}</p>

            @include('dt-tables::pages.docs.pages.'.$page['slug'])

            {{-- Previous and next --}}
            <div class="mt-12 grid gap-3 border-t border-gray-200 pt-6 sm:grid-cols-2">
                @if($previous)
                    <a href="{{ route('dt-tables.docs.show', ['page' => $previous['slug']]) }}" class="rounded-md border border-gray-200 bg-white px-4 py-3 no-underline hover:border-primary-dark">
                        <span class="block text-xs text-gray-400">Previous</span>
                        <span class="font-semibold">{{ $previous['title'] }}</span>
                    </a>
                @else
                    <span></span>
                @endif
                @if($next)
                    <a href="{{ route('dt-tables.docs.show', ['page' => $next['slug']]) }}" class="rounded-md border border-gray-200 bg-white px-4 py-3 text-right no-underline hover:border-primary-dark">
                        <span class="block text-xs text-gray-400">Next</span>
                        <span class="font-semibold">{{ $next['title'] }}</span>
                    </a>
                @endif
            </div>
        </article>

        {{-- On this page --}}
        <aside class="hidden w-48 shrink-0 xl:block">
            <div class="sticky top-20 text-sm">
                <h2 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">On this page</h2>
                <ul data-docs-toc></ul>
            </div>
        </aside>
    </div>
@endsection
