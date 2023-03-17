<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $primaryKey = 'email';
    protected $keyType = 'string';

    public $timestamps = false;

    protected $table = 'password_resets_with_code';

    protected $fillable = [
        'email',
        'code',
        'created_at',
    ];

    /**
     * @throws Exception
     */
    public static function create(array $attributes): PasswordReset
    {
        $instance = new self($attributes);
        $instance->fill($attributes);
        $instance->setCreatedAt(now());
        if (!$instance->save()) {
            throw new Exception('Could not save instance of ' . __CLASS__ . 'to table' . $instance->table);
        }

        return $instance;
    }
}
