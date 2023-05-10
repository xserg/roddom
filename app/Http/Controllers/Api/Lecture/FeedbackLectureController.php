<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Requests\FeedbackRequest;
use App\Repositories\LectureRepository;
use App\Services\FeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lecture/{id}/feedback',
    description: 'Оставить отзыв о лекции',
    summary: 'Оставить отзыв о лекции',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\RequestBody(
    description: 'Данные, необходимые для отзыва',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/FeedbackRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/FeedbackRequest')),
    ]
)]
#[OA\Response(response: Response::HTTP_CREATED, description: 'CREATED',
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthenticated'
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]
class FeedbackLectureController
{
    public function __construct(
        private FeedbackService $feedbackService,
        private LectureRepository $lectureRepository
    ) {
    }

    public function __invoke(
        FeedbackRequest $request,
        int $lectureId
    ): JsonResponse {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        $feedback = $this->feedbackService->create(
            $request->input('user_id'),
            $lectureId,
            $request->input('lector_id'),
            $request->input('content'),
        );

        return response()->json([
            'message' => 'Feedback created',
        ], Response::HTTP_CREATED);
    }
}
