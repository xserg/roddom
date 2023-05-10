<x-mail::message>
    <h1>Покупка успешно совершена</h1>
    <p><b>Приобретено</b>: {{ $entity }},<br>срок действия с {{ $dateStart }} до {{ $dateEnd }}.</p>
    <p>{{ $text }}</p>
    Всегда Ваши,<br>
    {{ config('app.name') }}
</x-mail::message>
