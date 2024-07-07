<?php

namespace App\Schema;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: "Header 'Accept: application/json' in all requests required.
    Ко всем ссылкам картинок нужно добавить строку /storage - полная ссылка должна выглядить примерно так:
    https://api-url/storage/images/xyz.jpeg",
    title: 'Mothers school API',
)]
#[OA\Contact(
    email: 'vladimir.balin@bboom.pro'
)]
#[OA\Server(
    //url: 'https://api.roddom1.vip/v1/',
    url: 'http://roddom1.test/v1/',
    description: "Mother's school API Server",
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Authorization: Bearer {#api key here}',
    name: 'bearerAuth',
    in: 'header',
    scheme: 'bearer',
)]
#[OA\Tag(name: 'app', description: 'Динамические заголовки, текст соглашения, общие штуки, которые указываются
в админке и посылается на фронт')]
#[OA\Tag(name: 'category', description: 'Категории лекций. Имеют две ступени: Родительская -> Дочерняя')]
#[OA\Tag(name: 'lecture', description: 'Лекции')]
#[OA\Tag(name: 'lector', description: 'Лекторы')]
#[OA\Tag(name: 'user', description: 'Пользователь')]
#[OA\Tag(name: 'promo', description: 'Промо лекции, ими могут быть любые другие лекции')]
#[OA\Tag(name: 'threads', description: 'Обращения пользователей')]
#[OA\Tag(name: 'wizard', description: 'Форма беременности, заполняется и отсылается юзеру на почту, на которую зареган его акк')]
class OpenApi
{
}
