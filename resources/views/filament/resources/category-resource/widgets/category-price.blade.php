<x-filament::widget>
    <x-filament::card>
        @foreach($form as $value)
            {{$value['length']}} дней - {{$value['price_for_category']}} р.
            <br>
        @endforeach
    </x-filament::card>
</x-filament::widget>
