<?php

namespace App\Services;

use App\Exceptions\FailedCreateFeedbackException;
use App\Models\Feedback;
use Exception;

class FeedbackService
{
    public function __construct()
    {
    }

    /**
     * @throws FailedCreateFeedbackException
     */
    public function create(
        int|string $user_id,
        int|string $lecture_id,
        int|string $lector_id,
        string $content,
    ): Feedback {
        try {
            $feedback = Feedback::create([
                'user_id' => $user_id,
                'lecture_id' => $lecture_id,
                'lector_id' => $lector_id,
                'content' => $content,
            ]);
        } catch (Exception $exception) {
            throw new FailedCreateFeedbackException($exception->getMessage());
        }

        return $feedback;
    }
}
