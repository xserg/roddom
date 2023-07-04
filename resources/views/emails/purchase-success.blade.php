<x-mail::message>
    <img src="{{ $image }}" alt="">
    <h1>Покупка успешно совершена</h1>
    <p>{{ $text }}</p>
    <p><b>Приобретено</b>: {{ $entity }} <br>срок действия с {{ $dateStart }} до {{ $dateEnd }}.</p>
    Всегда Ваши,<br>
    {{ config('app.name') }}
</x-mail::message>
