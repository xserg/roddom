<x-filament::widget>
    <x-filament::card>
        <p>
            количество лекций: {{ $lectures_count }}
        </p>
        @foreach($form as $value)
            {{$value['length']}} дней - {{$value['price_for_category']}} р.
            <br>
        @endforeach
    </x-filament::card>
</x-filament::widget>
