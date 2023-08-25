<x-mail::message>
    <h1>Мы получили ваш запрос на логин</h1>
    <p>Используйте пожалуйста следующий код для логина вашего аккаунта</p>
        <h2>{{ $code }}</h2>
    <p>Срок действия кода - один час, после того как это сообщение было отправлено</p>
    Всегда Ваши,<br>
    <a style="text-decoration: none; color: rgb(113, 128, 150);" href="{{config('app.frontend_url')}}">{{ config('app.name') }}</a>
</x-mail::message>
