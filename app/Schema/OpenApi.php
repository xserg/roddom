<?php

namespace App\Schema;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Mothers school API"
)]
#[OA\Contact(
    email: "vladimir.balin@bboom.pro"
)]
#[OA\Server(
    url: "https://мамы.online/api/v1/",
)]
#[OA\SecurityScheme(
    securityScheme: "api key",
    type: "Bearer token",
    description: "Authorization: Bearer {#api key}",
    name: "Bearer token",
    in: "headers"
)]
///**
// * @OA\SecurityScheme(
// *      securityScheme="xsrf",
// *      type="apiKey",
// *      in="cookie",
// *      name="XSRF-TOKEN"
// * ),
// * @OA\SecurityScheme(
// *      securityScheme="session-id",
// *      type="apiKey",
// *      in="cookie",
// *      name="notes_laravel_session"
// * ),
// * @OA\Schema(
// *      schema="note-resource",
// *      title="Note resource",
// *      type="object",
// *      @OA\Property(property="id", type="integer"),
// *      @OA\Property(property="title", type="string"),
// *      @OA\Property(property="content", type="string"),
// *      @OA\Property(property="created_at", type="string", format="date-time"),
// * )
// * @OA\Schema(
// *      title="User model",
// *      schema="user",
// *      @OA\Property(property="id", type="integer",),
// *      @OA\Property(property="name", type="string",),
// *      @OA\Property(property="email", type="string", format="email"),
// *      @OA\Property(property="email_verified_at", type="string", format="date-time",),
// *      @OA\Property(property="created_at", type="string", format="date-time",),
// *      @OA\Property(property="updated_at", type="string", format="date-time",),
// * )
// */
class OpenApi
{
}
