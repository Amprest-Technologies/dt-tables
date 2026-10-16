<div @class([
    'my-4 rounded-md border-l-4 px-4 py-3 text-sm',
    'border-secondary-light bg-secondary-soft text-secondary' => $type === 'note',
    'border-primary-dark bg-primary-soft text-secondary' => $type === 'tip',
    'border-primary-darker bg-primary-soft text-primary-darker' => $type === 'warning',
])>
    <div class="mb-0.5 text-xs font-semibold uppercase tracking-wide">{{ $label() }}</div>
    <div class="docs-callout">{{ $slot }}</div>
</div>
