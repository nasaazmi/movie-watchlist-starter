<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $params = $request->validate([
            'genre'  => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'random' => ['nullable', 'boolean'],
        ]);

        $query = Movie::query();

        if (! empty($params['search'])) {
            $term = '%' . $params['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('director', 'like', $term);
            });
        }

        if (! empty($params['random'])) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('id');
        }

        $movies = $query->get();

        if (! empty($params['genre'])) {
            $genre = $params['genre'];

            $movies = $movies->filter(function (Movie $movie) use ($genre) {
                foreach ((array) $movie->genres as $g) {
                    if (strcasecmp($g, $genre) === 0) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        return response()->json($movies, 200);
    }

    public function show(int $id): JsonResponse
    {
        $movie = Movie::find($id);

        if (! $movie) {
            return response()->json([
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        return response()->json($movie, 200);
    }
}