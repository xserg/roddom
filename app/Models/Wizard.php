<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Wizard extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'form' => 'array',
    ];

    protected function formWithIndexes(): Attribute
    {
        return Attribute::make(
            get: function () {
                $index = 1;
                return Arr::map($this->form, function ($form) use (&$index) {
                    $form['index'] = $index++;
                    return $form;
                });
            },
        );
    }
}
