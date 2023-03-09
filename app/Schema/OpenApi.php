<?php

namespace App\Schema;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "1. Header 'Accept: application/json' in all requests required;\n2. Bearer token for test purposes: 2|Z5UQcrN2vnXSUfc8KoNh5xgEeipB2gyobh5Ms7IO",
    title: "Mothers school API",
)]
#[OA\Contact(
    email: "vladimir.balin@bboom.pro"
)]
#[OA\Server(
    url: "https://api.мамы.online/v1/",
    description: "Mother's school API Server",
)]
#[OA\Server(
    url: "http://mothers-school.local/v1/",
    description: "local API Server for test purposes",
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    description: "Authorization: Bearer {#api key here}",
    name: "bearerAuth",
    in: "header",
    scheme: "bearer",
)]
class OpenApi
{
}
