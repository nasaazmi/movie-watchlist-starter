<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index(): JsonResponse
    {
        $movies = Watchlist::with('movie')
            ->where('user_id', Auth::guard('api')->id())
            ->latest()
            ->get()
            ->pluck('movie')
            ->values();

        return response()->json($movies, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'movieId' => ['required', 'integer'],
        ]);

        $userId = Auth::guard('api')->id();

        $movie = Movie::find($data['movieId']);

        if (! $movie) {
            return response()->json([
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        $exists = Watchlist::where('user_id', $userId)
            ->where('movie_id', $movie->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Film sudah ada di watchlist.',
            ], 409);
        }

        Watchlist::create([
            'user_id' => $userId,
            'movie_id' => $movie->id,
        ]);

        return response()->json([
            'message' => 'Film ditambahkan ke watchlist.',
            'movie' => $movie,
        ], 201);
    }

    public function destroy(int $movieId): JsonResponse
    {
        $deleted = Watchlist::where('user_id', Auth::guard('api')->id())
            ->where('movie_id', $movieId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Film tidak ada di watchlist.',
            ], 404);
        }

        return response()->json([
            'message' => 'Film dihapus dari watchlist.',
        ], 200);
    }
}