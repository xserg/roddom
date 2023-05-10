<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LectureContentType extends Model
{
    const KINESCOPE = 1;

    const PDF = 2;

    const EMBED = 3;

    protected $table = 'lecture_content_types';
}
