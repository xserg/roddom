<x-filament::widget>
    <x-filament::card>
        <x-filament::card.heading><span class="text-gray-500">количество лекций:</span> {{ $lectures_count }}</x-filament::card.heading>
        <x-filament::hr/>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Период, дней
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Суммарная стоимость
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Суммарная стоимость, промо
                    </th>
                </tr>
                </thead>
                <tbody class="text-lg font-mono">
                @foreach($form as $value)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">
                            {{$value['length']}}
                        </td>
                        <td class="px-6 py-4">
                            {{$value['price_for_category']}} р.
                        </td>
                        <td class="px-6 py-4">
                            {{$value['price_for_category_promo']}} р.
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <br>
    </x-filament::card>
</x-filament::widget>
