<?php

namespace App\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrors',
    title: 'ValidationErrors',
    example: '{
        "message": "The email has already been taken. (and 1 more error)",
        "errors": {
            "email": [
                "The email has already been taken."
            ],
            "password": [
                "The password field confirmation does not match."
            ]
        }
    }'
)]
class ValidationErrors
{
    #[OA\Property(
        property: 'message',
        description: 'summary всех ошибок',
        type: 'string',
        example: "The email has already been taken. (and 1 more error)"
    )]
    public string $message;

    #[OA\Property(
        property: 'errors',
        description: 'объект ошибок, где ключи - поля, в которых была ошибка, а значение - текст ошибки',
        type: 'object',
        example: '{
            "email": [
                "The email has already been taken."
            ],
            "password": [
                "The password field confirmation does not match."
            ]
        }'
    )]
    public array $errors;
}
