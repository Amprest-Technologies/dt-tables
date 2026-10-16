<div class="docs-code my-4 overflow-hidden rounded-md bg-secondary-dark">
    <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-2 text-xs">
        <span class="truncate font-medium text-gray-300">{{ $title ?? $label() }}</span>
        <span class="flex shrink-0 items-center gap-3">
            @if($title)
                <span class="text-gray-500">{{ $label() }}</span>
            @endif
            <button type="button" data-copy="#{{ $id }}" class="font-medium text-primary-light hover:text-primary-lighter">Copy</button>
        </span>
    </div>
    <pre class="overflow-x-auto p-4 text-[13px] leading-[1.75]"><code id="{{ $id }}" class="language-{{ $highlighter() }}">{{ $dedent((string) $slot) }}</code></pre>
</div>
