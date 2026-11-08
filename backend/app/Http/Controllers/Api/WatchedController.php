<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchedController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::with('movie')
            ->where('user_id', Auth::guard('api')->id())
            ->latest()
            ->get()
            ->map(fn (Review $r) => $this->format($r))
            ->values();

        return response()->json($reviews, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'movieId' => ['required', 'integer'],
            'rating'  => ['required', 'integer', 'between:1,5'],
            'review'  => ['nullable', 'string', 'max:2000'],
        ]);

        $movie = Movie::find($data['movieId']);

        if (! $movie) {
            return response()->json([
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        $review = Review::updateOrCreate(
            [
                'user_id'  => Auth::guard('api')->id(),
                'movie_id' => $movie->id,
            ],
            [
                'rating' => $data['rating'],
                'review' => $data['review'] ?? '',
            ]
        );

        $review->load('movie');

        return response()->json(
            $this->format($review),
            $review->wasRecentlyCreated ? 201 : 200
        );
    }

    private function format(Review $review): array
    {
        return [
            'id'         => $review->id,
            'movie_id'   => $review->movie_id,
            'rating'     => $review->rating,
            'review'     => $review->review,
            'watched_at' => $review->updated_at,
            'movie'      => $review->movie,
        ];
    }
}