@props([
    'actions',
    'alignment' => 'left',
    'record' => null,
    'wrap' => false,
])

<div {{ $attributes->class([
    'filament-tree-actions-container flex items-center gap-1',
    'flex-wrap' => $wrap,
    'md:flex-nowrap' => $wrap === '-md',
    match ($alignment) {
        'center' => 'justify-center',
        'left' => 'justify-start',
        'left md:right' => 'justify-start md:justify-end',
        default => 'justify-end',
    },
]) }}>
    @foreach ($actions as $action)
        @if (! $action->record($record)->isHidden())
            {{ $action }}
        @endif
    @endforeach
</div>
