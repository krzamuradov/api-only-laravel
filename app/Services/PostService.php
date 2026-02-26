<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PostService
{
    /**
     * @param array{per_page:int,search:?string,sort:string} $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        [$column, $direction] = $this->parseSort($filters['sort']);

        return Post::query()
            ->search($filters['search'])
            ->orderBy($column, $direction)
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    /**
     * @param array{title:string,content:string} $data
     */
    public function create(User $user, array $data): Post
    {
        return DB::transaction(static fn (): Post => Post::query()->create([
            ...$data,
            'user_id' => $user->id,
        ]));
    }

    /**
     * @param array{title?:string,content?:string} $data
     */
    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $post->update($data);

            return $post->refresh();
        });
    }

    public function delete(Post $post): void
    {
        DB::transaction(static fn () => $post->delete());
    }

    /**
     * @return array{0:string,1:'asc'|'desc'}
     */
    private function parseSort(string $sort): array
    {
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        return [$column, $direction];
    }
}
