<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/movies.json');

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        $movies = json_decode(file_get_contents($path), true);

        foreach ($movies as $m) {
            Movie::updateOrCreate(
                ['id' => $m['id']],
                [
                    'title'      => $m['title'],
                    'year'       => $m['year'],
                    'director'   => $m['director'],
                    'genres'     => $m['genres'],
                    'rating'     => $m['rating'],
                    'duration'   => $m['duration'],
                    'synopsis'   => $m['synopsis'],
                    'poster_url' => $m['poster_url'],
                    'cast'       => $m['cast'],
                ]
            );
        }

        $this->command->info('Berhasil memasukkan ' . count($movies) . ' film.');
    }
}