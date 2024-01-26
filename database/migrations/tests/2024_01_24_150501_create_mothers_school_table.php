<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_token_id')->unique();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('access_token_id')->on('personal_access_tokens')->references('id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('app_help_page', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('text');
        });

        Schema::create('app_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('agreement_title')->default('Прочтите соглашение');
            $table->text('agreement_text')->nullable();
            $table->string('recommended_title')->default('Рекомендуем');
            $table->string('recommended_subtitle')->default('Не пропустите новые лекции!');
            $table->string('lectures_catalog_title')->default('Каталог лекций');
            $table->string('lectures_catalog_subtitle')->default('Выберите тему, которая вас интересует');
            $table->string('out_lectors_title')->default('Наши лекторы');
            $table->string('not_viewed_yet_title')->default('Вы ещё не смотрели');
            $table->string('more_in_the_collection')->default('Ещё в подборке');
            $table->string('about_lector_title')->default('О лекторе');
            $table->string('diplomas_title')->default('Дипломы и сертификаты');
            $table->string('lectors_videos')->default('Видео от лектора');
            $table->string('app_title')->default('Школа мам и пап «Нежность»');
            $table->text('about_app')->nullable();
            $table->string('app_author_name')->default('Сергей Тарасов');
            $table->string('app_link_share_title')->default('Поделиться ссылкой');
            $table->string('app_link_share_link')->default('https://xn--80axb4d.online');
            $table->string('app_show_qr_title')->default('Показать QR-код');
            $table->string('tarif_title_1')->default('tarif-1');
            $table->string('tarif_title_2')->default('tarif-2');
            $table->string('tarif_title_3')->default('tarif-3');
            $table->unsignedInteger('free_lecture_hours')->default(24);
            $table->string('validation_wrong_credentials')->default('Неправильный логин/пароль. Повторите попытку.');
            $table->string('reset_code_sent')->default('Код подтверждения отправлен');
            $table->string('added_to_saved')->default('Добавили в «Сохранённые»');
            $table->string('removed_from_saved')->default('Удалили из «Сохранённых»');
            $table->string('added_to_watched')->default('Добавили в «Просмотренные»');
            $table->string('removed_from_watched')->default('Удалили из «Просмотренных»');
            $table->string('message_sent')->default('Ваше сообщение успешно отправлено.');
            $table->string('message_sent_error')->default('Во время отправки сообщения произошла ошибка.');
            $table->string('thanks_for_rate')->default('Спасибо за вашу оценку!');
            $table->string('thanks_for_feedback')->default('Спасибо за обратную связь! Ваше сообщение успено отправлено.');
            $table->text('successful_purchase_text')->nullable();
            $table->string('buy_page_under_btn_description')->default('Выбранный материал будет доступен Вам для просмотра в течение x дней с момента покупки.');
            $table->string('buy_page_description', 510)->default('Вы можете приобрести доступ к этому материалу на необходимый Вам промежуток времени.');
            $table->string('buy_category')->default('Купить категорию со скидкой');
            $table->string('buy_subcategory')->default('Купить подкатегорию со скидкой');
            $table->string('view_schedule')->default('График просмотра');
            $table->string('watched_already')->default('Вы уже посмотрели материал на сегодня');
            $table->string('next_free_lecture_available_at')->default('Следующий бесплатный будет доступен через');
            $table->string('buy_all')->default('Купить весь каталог со скидкой');
            $table->string('watch_from')->default('Смотреть от');
            $table->string('chosen_category_contains_lectures')->default('Выбранная категория содержит х лекций.');
            $table->string('your_profit_is_roubles')->default('Ваша экономия составит х рублей.');
            $table->string('ref_system_title')->default('Партнерская программа');
            $table->text('ref_system_description')->nullable();
            $table->string('successful_purchase_image')->nullable();
            $table->string('user_invites_you_to_join')->nullable()->default('x приглашает Вас присоединиться к интересным материалам в Школе Мам и Пап');
            $table->string('ref_system_preview_picture')->nullable();
            $table->string('category_special_price_text')->default('Вы также можете приобрести полностью категорию по специальной цене');
            $table->unsignedInteger('credit_minimal_sum')->default(3000);
        });

        Schema::create('average_lector_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('lector_id')->primary();
            $table->unsignedDecimal('rating')->nullable();
        });

        Schema::create('average_lecture_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('lecture_id')->primary();
            $table->unsignedDecimal('rating')->nullable();
        });

        Schema::create('category_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->index('category_prices_category_id_foreign');
            $table->unsignedBigInteger('period_id')->index('category_prices_period_id_foreign');
            $table->unsignedBigInteger('price_for_pack')->nullable();
            $table->unsignedBigInteger('price_for_one_lecture');
            $table->unsignedBigInteger('price_for_one_lecture_promo')->nullable();
        });

        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('text');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_name');
            $table->timestamp('last_used_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'device_name']);
        });

        Schema::create('diplomas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('preview_picture');
            $table->unsignedBigInteger('lector_id')->index('diplomas_lector_id_foreign');
            $table->timestamps();
        });

        Schema::create('everything_pack', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('price')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('feedback_user_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('feedback_lecture_id_foreign');
            $table->unsignedBigInteger('lector_id')->index('feedback_lector_id_foreign');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('full_catalog_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('price_for_one_lecture');
            $table->unsignedBigInteger('price_for_one_lecture_promo');
            $table->boolean('is_promo')->default(false);
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('lector_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('lector_rates_user_id_foreign');
            $table->unsignedBigInteger('lector_id')->index('lector_rates_lector_id_foreign');
            $table->unsignedInteger('rating')->nullable();
            $table->timestamps();
        });

        Schema::create('lectors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('position');
            $table->text('description');
            $table->date('career_start');
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('lecture_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('parent_id')->default(0);
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('info')->nullable();
            $table->string('preview_picture')->nullable();
            $table->boolean('is_promo')->default(false);
            $table->timestamps();
        });

        Schema::create('lecture_content_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('title_ru');
            $table->timestamps();
        });

        Schema::create('lecture_payment_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('title_ru');
            $table->timestamps();
        });

        Schema::create('lecture_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('lecture_rates_user_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('lecture_rates_lecture_id_foreign');
            $table->unsignedInteger('rating')->nullable();
            $table->timestamps();
        });

        Schema::create('lectures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('preview_picture')->nullable();
            $table->string('content', 767)->unique('lectures_video_id_unique');
            $table->unsignedBigInteger('lector_id')->nullable()->index('lectures_lector_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->index('lectures_category_id_foreign');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('content_type_id')->default(1)->index('lectures_content_type_id_foreign');
            $table->unsignedBigInteger('payment_type_id')->default(1)->index('lectures_payment_type_id_foreign');
            $table->boolean('show_tariff_1')->default(true);
            $table->boolean('show_tariff_2')->default(true);
            $table->boolean('show_tariff_3')->default(true);
        });

        Schema::create('lectures_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lecture_id')->index('lectures_prices_lecture_id_foreign');
            $table->unsignedBigInteger('period_id')->index('lectures_prices_period_id_foreign');
            $table->unsignedBigInteger('price');
        });

        Schema::create('lectures_to_promo', function (Blueprint $table) {
            $table->unsignedBigInteger('promo_id');
            $table->unsignedBigInteger('lecture_id')->index('lectures_to_promo_lecture_id_foreign');

            $table->primary(['promo_id', 'lecture_id']);
        });

        Schema::create('login_codes', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->unsignedBigInteger('thread_id')->index('messages_thread_id_foreign');
            $table->unsignedBigInteger('author_id')->index('messages_author_id_foreign');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('price_to_pay')->nullable();
            $table->unsignedBigInteger('points')->nullable();
            $table->string('subscriptionable_type');
            $table->unsignedBigInteger('subscriptionable_id');
            $table->unsignedInteger('period');
            $table->unsignedSmallInteger('lectures_count');
            $table->longText('exclude')->nullable();
            $table->string('description')->nullable();
            $table->enum('status', ['CREATED', 'FAILED', 'CONFIRMED'])->default('CREATED');
            $table->string('code', 36);
            $table->string('code_succeeded')->nullable();
            $table->timestamps();

            $table->index(['subscriptionable_type', 'subscriptionable_id']);
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('thread_id')->index('participants_thread_id_foreign');
            $table->unsignedBigInteger('user_id')->index('participants_user_id_foreign');
            $table->timestamp('read_at')->nullable();
            $table->boolean('opened')->default(false);
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('password_resets_with_code', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });

        Schema::create('promo_lectures_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lecture_id')->index('promo_lectures_prices_lecture_id_foreign');
            $table->unsignedBigInteger('promo_id')->index('promo_lectures_prices_promo_id_foreign');
            $table->unsignedBigInteger('period_id')->index('promo_lectures_prices_period_id_foreign');
            $table->unsignedBigInteger('price');
        });

        Schema::create('promo_pack_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('promo_id');
            $table->unsignedBigInteger('period_id')->index('promo_pack_prices_period_id_foreign');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('price_for_one_lecture');

            $table->primary(['promo_id', 'period_id']);
        });

        Schema::create('promos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ref_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('depth_level');
            $table->integer('percent');
        });

        Schema::create('ref_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('ref_points_user_id_foreign');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('ref_points_gain_onces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type')->unique();
            $table->unsignedInteger('points_gains');
        });

        Schema::create('ref_points_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('ref_points_payments_user_id_foreign');
            $table->unsignedBigInteger('payer_id')->index('ref_points_payments_payer_id_foreign');
            $table->string('reason')->nullable();
            $table->unsignedSmallInteger('depth_level')->nullable();
            $table->unsignedSmallInteger('percent')->nullable();
            $table->unsignedBigInteger('ref_points');
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('price_to_pay')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id')->index('subscription_items_subscription_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('subscription_items_lecture_id_foreign');
        });

        Schema::create('subscription_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->unsignedInteger('length');
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('subscriptions_user_id_foreign');
            $table->string('subscriptionable_type');
            $table->unsignedBigInteger('subscriptionable_id');
            $table->unsignedBigInteger('period_id')->nullable()->index('subscriptions_period_id_foreign');
            $table->smallInteger('lectures_count')->default(0);
            $table->longText('exclude')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
            $table->unsignedBigInteger('total_price')->nullable();
            $table->unsignedBigInteger('price_to_pay')->nullable();
            $table->unsignedBigInteger('points')->nullable();
            $table->string('entity_title')->nullable();

            $table->index(['subscriptionable_type', 'subscriptionable_id']);
        });

        Schema::create('threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status', 10)->default('open');
            $table->timestamps();
        });

        Schema::create('user_to_free_watched_lectures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_to_free_watched_lectures_user_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('user_to_free_watched_lectures_lecture_id_foreign');
            $table->dateTime('available_until');
            $table->timestamps();
        });

        Schema::create('user_to_list_watched_lectures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_to_list_watched_lectures_user_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('user_to_list_watched_lectures_lecture_id_foreign');
            $table->timestamps();
        });

        Schema::create('user_to_saved_lectures', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lecture_id')->index('user_to_saved_lectures_lecture_id_foreign');
            $table->timestamps();

            $table->primary(['user_id', 'lecture_id']);
        });

        Schema::create('user_to_watched_lectures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_to_watched_lectures_user_id_foreign');
            $table->unsignedBigInteger('lecture_id')->index('user_to_watched_lectures_lecture_id_foreign');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->date('birthdate')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_mother')->default(false);
            $table->date('pregnancy_start')->nullable();
            $table->date('baby_born')->nullable();
            $table->string('photo')->nullable();
            $table->string('photo_small')->nullable();
            $table->unsignedBigInteger('referrer_id')->nullable()->index('users_referrer_id_foreign');
            $table->boolean('to_delete')->default(false);
            $table->dateTime('next_free_lecture_available')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('ref_token');
            $table->string('ref_type', 15)->default('vertical');
            $table->timestamps();
            $table->boolean('is_admin')->default(false);
            $table->timestamp('profile_fulfilled_at')->nullable();
            $table->boolean('can_get_referrals_bonus')->default(true);
            $table->boolean('can_get_referrers_bonus')->default(true);
            $table->boolean('is_notification_read')->default(false);
        });

        Schema::create('wizard_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->string('readable_key')->nullable();
        });

        Schema::create('wizards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('form');
            $table->string('title');
            $table->unsignedSmallInteger('order');
        });

        Schema::table('average_lector_rates', function (Blueprint $table) {
            $table->foreign(['lector_id'])->references(['id'])->on('lectors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('average_lecture_rates', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('category_prices', function (Blueprint $table) {
            $table->foreign(['category_id'])->references(['id'])->on('lecture_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['period_id'])->references(['id'])->on('subscription_periods')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('diplomas', function (Blueprint $table) {
            $table->foreign(['lector_id'])->references(['id'])->on('lectors')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->foreign(['lector_id'])->references(['id'])->on('lectors')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });

        Schema::table('lector_rates', function (Blueprint $table) {
            $table->foreign(['lector_id'])->references(['id'])->on('lectors')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('lecture_rates', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->foreign(['category_id'])->references(['id'])->on('lecture_categories')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['content_type_id'])->references(['id'])->on('lecture_content_types')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['lector_id'])->references(['id'])->on('lectors')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['payment_type_id'])->references(['id'])->on('lecture_payment_types')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('lectures_prices', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['period_id'])->references(['id'])->on('subscription_periods')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('lectures_to_promo', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['promo_id'])->references(['id'])->on('promos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign(['author_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['thread_id'])->references(['id'])->on('threads')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->foreign(['thread_id'])->references(['id'])->on('threads')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('promo_lectures_prices', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['period_id'])->references(['id'])->on('subscription_periods')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['promo_id'])->references(['id'])->on('promos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('promo_pack_prices', function (Blueprint $table) {
            $table->foreign(['period_id'])->references(['id'])->on('subscription_periods')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['promo_id'])->references(['id'])->on('promos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('ref_points', function (Blueprint $table) {
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('ref_points_payments', function (Blueprint $table) {
            $table->foreign(['payer_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['subscription_id'])->references(['id'])->on('subscriptions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign(['period_id'])->references(['id'])->on('subscription_periods')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('user_to_free_watched_lectures', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('user_to_list_watched_lectures', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('user_to_saved_lectures', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('user_to_watched_lectures', function (Blueprint $table) {
            $table->foreign(['lecture_id'])->references(['id'])->on('lectures')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['referrer_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(DB::getDriverName() !== 'sqlite'){
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_referrer_id_foreign');
            });

            Schema::table('user_to_watched_lectures', function (Blueprint $table) {
                $table->dropForeign('user_to_watched_lectures_lecture_id_foreign');
                $table->dropForeign('user_to_watched_lectures_user_id_foreign');
            });

            Schema::table('user_to_saved_lectures', function (Blueprint $table) {
                $table->dropForeign('user_to_saved_lectures_lecture_id_foreign');
                $table->dropForeign('user_to_saved_lectures_user_id_foreign');
            });

            Schema::table('user_to_list_watched_lectures', function (Blueprint $table) {
                $table->dropForeign('user_to_list_watched_lectures_lecture_id_foreign');
                $table->dropForeign('user_to_list_watched_lectures_user_id_foreign');
            });

            Schema::table('user_to_free_watched_lectures', function (Blueprint $table) {
                $table->dropForeign('user_to_free_watched_lectures_lecture_id_foreign');
                $table->dropForeign('user_to_free_watched_lectures_user_id_foreign');
            });

            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropForeign('subscriptions_period_id_foreign');
                $table->dropForeign('subscriptions_user_id_foreign');
            });

            Schema::table('subscription_items', function (Blueprint $table) {
                $table->dropForeign('subscription_items_lecture_id_foreign');
                $table->dropForeign('subscription_items_subscription_id_foreign');
            });

            Schema::table('ref_points_payments', function (Blueprint $table) {
                $table->dropForeign('ref_points_payments_payer_id_foreign');
                $table->dropForeign('ref_points_payments_user_id_foreign');
            });

            Schema::table('ref_points', function (Blueprint $table) {
                $table->dropForeign('ref_points_user_id_foreign');
            });

            Schema::table('promo_pack_prices', function (Blueprint $table) {
                $table->dropForeign('promo_pack_prices_period_id_foreign');
                $table->dropForeign('promo_pack_prices_promo_id_foreign');
            });

            Schema::table('promo_lectures_prices', function (Blueprint $table) {
                $table->dropForeign('promo_lectures_prices_lecture_id_foreign');
                $table->dropForeign('promo_lectures_prices_period_id_foreign');
                $table->dropForeign('promo_lectures_prices_promo_id_foreign');
            });

            Schema::table('participants', function (Blueprint $table) {
                $table->dropForeign('participants_thread_id_foreign');
                $table->dropForeign('participants_user_id_foreign');
            });

            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign('messages_author_id_foreign');
                $table->dropForeign('messages_thread_id_foreign');
            });

            Schema::table('lectures_to_promo', function (Blueprint $table) {
                $table->dropForeign('lectures_to_promo_lecture_id_foreign');
                $table->dropForeign('lectures_to_promo_promo_id_foreign');
            });

            Schema::table('lectures_prices', function (Blueprint $table) {
                $table->dropForeign('lectures_prices_lecture_id_foreign');
                $table->dropForeign('lectures_prices_period_id_foreign');
            });

            Schema::table('lectures', function (Blueprint $table) {
                $table->dropForeign('lectures_category_id_foreign');
                $table->dropForeign('lectures_content_type_id_foreign');
                $table->dropForeign('lectures_lector_id_foreign');
                $table->dropForeign('lectures_payment_type_id_foreign');
            });

            Schema::table('lecture_rates', function (Blueprint $table) {
                $table->dropForeign('lecture_rates_lecture_id_foreign');
                $table->dropForeign('lecture_rates_user_id_foreign');
            });

            Schema::table('lector_rates', function (Blueprint $table) {
                $table->dropForeign('lector_rates_lector_id_foreign');
                $table->dropForeign('lector_rates_user_id_foreign');
            });

            Schema::table('feedback', function (Blueprint $table) {
                $table->dropForeign('feedback_lector_id_foreign');
                $table->dropForeign('feedback_lecture_id_foreign');
                $table->dropForeign('feedback_user_id_foreign');
            });

            Schema::table('diplomas', function (Blueprint $table) {
                $table->dropForeign('diplomas_lector_id_foreign');
            });

            Schema::table('devices', function (Blueprint $table) {
                $table->dropForeign('devices_user_id_foreign');
            });

            Schema::table('category_prices', function (Blueprint $table) {
                $table->dropForeign('category_prices_category_id_foreign');
                $table->dropForeign('category_prices_period_id_foreign');
            });

            Schema::table('average_lecture_rates', function (Blueprint $table) {
                $table->dropForeign('average_lecture_rates_lecture_id_foreign');
            });

            Schema::table('average_lector_rates', function (Blueprint $table) {
                $table->dropForeign('average_lector_rates_lector_id_foreign');
            });
        }

        Schema::dropIfExists('wizards');

        Schema::dropIfExists('wizard_info');

        Schema::dropIfExists('users');

        Schema::dropIfExists('user_to_watched_lectures');

        Schema::dropIfExists('user_to_saved_lectures');

        Schema::dropIfExists('user_to_list_watched_lectures');

        Schema::dropIfExists('user_to_free_watched_lectures');

        Schema::dropIfExists('threads');

        Schema::dropIfExists('subscriptions');

        Schema::dropIfExists('subscription_periods');

        Schema::dropIfExists('subscription_items');

        Schema::dropIfExists('ref_points_payments');

        Schema::dropIfExists('ref_points_gain_onces');

        Schema::dropIfExists('ref_points');

        Schema::dropIfExists('ref_info');

        Schema::dropIfExists('promos');

        Schema::dropIfExists('promo_pack_prices');

        Schema::dropIfExists('promo_lectures_prices');

        Schema::dropIfExists('personal_access_tokens');

        Schema::dropIfExists('password_resets_with_code');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('participants');

        Schema::dropIfExists('orders');

        Schema::dropIfExists('notifications');

        Schema::dropIfExists('messages');

        Schema::dropIfExists('login_codes');

        Schema::dropIfExists('lectures_to_promo');

        Schema::dropIfExists('lectures_prices');

        Schema::dropIfExists('lectures');

        Schema::dropIfExists('lecture_rates');

        Schema::dropIfExists('lecture_payment_types');

        Schema::dropIfExists('lecture_content_types');

        Schema::dropIfExists('lecture_categories');

        Schema::dropIfExists('lectors');

        Schema::dropIfExists('lector_rates');

        Schema::dropIfExists('jobs');

        Schema::dropIfExists('full_catalog_prices');

        Schema::dropIfExists('feedback');

        Schema::dropIfExists('failed_jobs');

        Schema::dropIfExists('everything_pack');

        Schema::dropIfExists('diplomas');

        Schema::dropIfExists('devices');

        Schema::dropIfExists('custom_notifications');

        Schema::dropIfExists('category_prices');

        Schema::dropIfExists('average_lecture_rates');

        Schema::dropIfExists('average_lector_rates');

        Schema::dropIfExists('app_info');

        Schema::dropIfExists('app_help_page');
    }
};
