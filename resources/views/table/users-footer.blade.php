<x-tables::row>
    <x-tables::cell>
        {{-- for the checkbox column --}}
    </x-tables::cell>

    @foreach ($columns as $column)
        <x-tables::cell
            wire:loading.remove.delay
            wire:target="{{ implode(',', \Filament\Tables\Table::LOADING_TARGETS) }}"
        >
            @for ($i = 0; $i < count($calc_columns); $i++ )
                @if ($column->getName() == $calc_columns[$i])
                    <div class="filament-tables-column-wrapper">
                        <div class="filament-tables-text-column px-4 py-2 flex flex-col w-full justify-start text-start">
                            <div class="items-center space-x-1 rtl:space-x-reverse">
                                <div class="font-medium">
                                    <span class="font-light">Страница: </span> {{ $records->sum($calc_columns[$i]) / 100 }}
                                </div>
                                <div class="font-medium">
                                    <span class="font-light">Все: </span>{{ $common_babycoins / 100 }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endfor
        </x-tables::cell>
    @endforeach
</x-tables::row>
