<?php

namespace App\Schema;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Mothers school API",
)]
#[OA\Contact(
    email: "vladimir.balin@bboom.pro"
)]
#[OA\Server(
    url: "https://мамы.online/api/v1/",
    description: "API Server",
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    description: "Authorization: Bearer {#api key}",
    name: "bearerAuth",
    in: "header",
    scheme: "bearer",
)]
class OpenApi
{
}
