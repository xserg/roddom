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
    url: 'https://api.roddom1.vip/v1/',
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
class OpenApi
{
}
