<?php

namespace App\Jobs;

use App\Mail\SendCodeResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendResetPasswordCodeJob extends ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string     $email,
        private string|int $code
    )
    {
    }

    public function handle(): void
    {
        Mail::to($this->email)
            ->send(
                new SendCodeResetPassword($this->code)
            );
    }
}
