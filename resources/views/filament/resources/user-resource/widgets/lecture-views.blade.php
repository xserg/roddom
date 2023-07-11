<x-filament::widget>
    <x-filament::card>
        <x-filament::card.heading><span class="font-sans"></span>
        </x-filament::card.heading>
        <x-filament::hr/>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left font-sans">
                <thead class="">
                <tr class="">
                    <th scope="col" class="px-6 py-3 font-medium">

                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Количество
                    </th>
                </tr>
                </thead>
                <tbody class="">
                <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                    <td class="px-6 py-3 font-medium">
                        Просмотров, всего
                    </td>
                    <td class="px-6 py-4">
                        {{$totalViewsCount}}
                    </td>
                </tr>
                <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                    <td class="px-6 py-3 font-medium">
                        Просмотров, сегодня
                    </td>
                    <td class="px-6 py-4">
                        {{$totalViewsTodayCount}}
                    </td>
                </tr>
                <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                    <td class="px-6 py-3 font-medium">
                        Просмотренных лекций, всего
                    </td>
                    <td class="px-6 py-4">
                        {{$lecturesViewedCount}}
                    </td>
                </tr>
                <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                    <td class="px-6 py-3 font-medium">
                        Просмотренных лекций, сегодня
                    </td>
                    <td class="px-6 py-4">
                        {{$lecturesViewedTodayCount}}
                    </td>
                </tr>
                <tr class="text-base bg-white border-t dark:bg-gray-800 dark:border-gray-700 font-light">
                    <td class="px-6 py-3 font-medium">
                        Чаще всего просмотрена лекция
                    </td>
                    <td class="px-6 py-4">
                        @if($mostViewed)
                            <a href="{{ url(route('filament.resources.lectors.edit', $mostViewed->id)) }}">
                                {{$mostViewed->title}} - {{$mostViewedCount}}
                            </a>
                        @else
                            отсутсвует
                        @endif
                    </td>
                </tr>


                </tbody>
            </table>


        </div>
        <br>
    </x-filament::card>
</x-filament::widget>
