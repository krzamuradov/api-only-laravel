<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create();

        Post::query()->create([
            'user_id' => $author->id,
            'title' => 'Welcome to API First',
            'content' => 'This is a seeded demo post for testing API responses.',
        ]);

        Post::query()->create([
            'user_id' => $author->id,
            'title' => 'Laravel API Best Practices',
            'content' => 'Use FormRequest, Resource, Service layer and Feature tests.',
        ]);

        Post::factory()->count(8)->for($author)->create();
    }
}
