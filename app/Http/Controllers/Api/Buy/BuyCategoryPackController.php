<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyLectureRequest;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Subscription;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/',
    description: "Покупка подкатегории лекций(входящие все в нее лекции) на период 1, 14, 30 дней",
    summary: "Покупка подкатегории",
    security: [["bearerAuth" => []]],
    tags: ["buy"])
]
class BuyCategoryPackController extends Controller
{
    public function __construct(
    )
    {
    }

    public function __invoke(
        Request $request,
        int               $id,
        int               $period
    )
    {
        return response()->json([
            'res' => __CLASS__
        ]);
    }
}
