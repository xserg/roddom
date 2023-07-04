<x-filament::widget>
    <x-filament::card>
        <x-filament::card.heading><span class="font-sans">количество платных лекций:</span> {{ $lectures_count }}
        </x-filament::card.heading>
        <x-filament::hr/>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left font-sans">
                <thead class="">
                <tr class="">
                    <th scope="col" class="px-6 py-3 font-medium">
                        Период, дней
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Суммарная стоимость, рублей
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Суммарная акционная стоимость, рублей
                    </th>
                </tr>
                </thead>
                <tbody class="">
                @foreach($form as $value)
                    <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                        <td class="px-6 py-4">
                            {{$value['period_length']}}
                        </td>
                        <td class="px-6 py-4 font-bold">
                            {{$value['price_for_catalog']}}
                        </td>
                        <td class="px-6 py-4 font-bold">
                            {{$value['price_for_catalog_promo']}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <br>
    </x-filament::card>
</x-filament::widget>
