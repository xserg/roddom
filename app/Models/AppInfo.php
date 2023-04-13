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
        'tarif_title_1',
        'tarif_title_2',
        'tarif_title_3',
        'free_lecture_hours',
        'validation_wrong_credentials',
        'reset_code_sent',
        'added_to_saved',
        'removed_from_saved',
        'added_to_watched',
        'removed_from_watched',
        'message_sent',
        'message_sent_error',
        'thanks_for_rate',
        'thanks_for_feedback',
    ];

    public $timestamps = false;
}
