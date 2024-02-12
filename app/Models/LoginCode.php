<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class LoginCode extends Model
{
    protected $primaryKey = 'email';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'login_codes';

    protected $fillable = [
        'email',
        'code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * @throws Exception
     */
    public static function create(array $attributes): LoginCode
    {
        $instance = new self($attributes);
        $instance->fill($attributes);
        $instance->setCreatedAt(now());
        if (! $instance->save()) {
            throw new Exception('Could not save instance of ' . __CLASS__ . 'to table' . $instance->table);
        }

        return $instance;
    }
}
