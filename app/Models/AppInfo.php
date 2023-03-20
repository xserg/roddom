<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInfo extends Model
{
    protected $table = 'app_info';
    protected $fillable = [
        'agreement_title',
        'agreement_text',
        'recommended_title',
        'recommended_subtitle',
        'lectures_catalog_title',
        'about_app',
        'about_lector_title',
        'more_in_the_collection',
        'app_author_name',
        'diplomas_title',
        'app_link_share_title',
        'app_title',
        'lectors_videos',
        'app_show_qr_link',
        'not_viewed_yet_title',
        'lectures_catalog_subtitle',
        'app_show_qr_title',
        'app_link_share_link',
        'out_lectors_title',
    ];
}
