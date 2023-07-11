<?php

namespace Canvas\Http\Controllers;

use Canvas\Http\Requests\PostRequest;
use Canvas\Models\Post;
use Canvas\Models\Tag;
use Canvas\Models\Topic;
use Canvas\Services\StatsAggregator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $type = request()->query('type', 'approved');
        $selectUser = fn($q) => $q->select('name', 'username', 'avatar', 'id');
        $user = request()->user('canvas');
        $posts = Post::query()
            ->with([
                'user' => $selectUser,
                'approver' => $selectUser,
                'reviewer' => $selectUser
            ])
            ->select('id', 'uuid', 'title', 'summary', 'featured_image', 'published_at', 'created_at',
                'updated_at', 'view_count', 'blogger_id', 'approved_by', 'reviewed_by', 'approved_at'
            )
            ->when(
                $type !== 'relevant-draft' && ($type === 'my-draft' || $user->isContributor || request()->query('scope', 'user') != 'all'),
                fn (Builder $query) => $query->where('blogger_id', $user->id)
            )
            ->when($type == 'published', fn(Builder $query) => $query->published())
            ->when(str_ends_with($type, 'draft'), fn(Builder $query) => $query->draft())
            ->when($type === 'relevant-draft',
                $relevantDraftFilter = fn(Builder $query) =>
                    $query->where('blogger_id', '!=', $user->id)
                        ->where(fn(Builder $query) =>
                            $query->where('approved_by', $user->id)
                                ->orWhere('reviewed_by', $user->id)
                        )
            )
            ->when($type == 'approved', fn(Builder $query) => $query->approved())
            ->orderByDesc(match ($type) {
                'published' => 'published_at',
                'approved' => 'approved_at',
                default => 'created_at',
            })
            ->paginate();

        $basePostQuery =  Post::query()
            ->when($user->isContributor || request()->query('scope', 'user') != 'all', function (Builder $query) use($user) {
                return $query->where('blogger_id', $user->id);
            });
        $myDraftCount = $basePostQuery->clone()->draft()->where('blogger_id', $user->id)->count();
        $relevantDraftCount = $basePostQuery->clone()->draft()->when(true, $relevantDraftFilter)->count();
        $allDraftCount = $basePostQuery->clone()->draft()->count();
        $publishedCount = $basePostQuery->clone()->published()->count();
        $approvedCount = $basePostQuery->clone()->approved()->count();

        return response()->json([
            'posts' => $posts,
            'allDraftCount' => $allDraftCount,
            'myDraftCount' => $myDraftCount,
            'relevantDraftCount' => $relevantDraftCount,
            'publishedCount' => $publishedCount,
            'approvedCount' => $approvedCount,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $uuid = Uuid::uuid4();

        return response()->json([
            'post' => Post::query()->make([
                'uuid' => $uuid,
                'slug' => "post-{$uuid->toString()}",
            ]),
            'tags' => Tag::query()->get(['name', 'slug']),
            'topics' => Topic::query()->get(['name', 'slug']),
        ]);
    }

    /*
     * Store a newly created resource in storage.
     *
     * @param  PostRequest  $request
     * @param $id
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function store(PostRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user('canvas');

        $post = Post::query()
                    ->when($user->isContributor, function (Builder $query) use($user) {
                        return $query->where('blogger_id', $user->id);
                    })
                    ->with('tags', 'topic')
                    ->where('uuid', $id)
                    ->first();


        abort_if(!empty($post->approved_at) && $user->isContributor && $post !== null, 403, 'Contributors can\'t update approved posts.');
        abort_if(!empty($post->approved_at) && $request->filled('approved_at') && !$user->isAdmin && $post?->blogger_id === $user->id, 403, 'users can\'t review their own posts');
        abort_if(!empty($post->published_at) && $request->filled('published_at') && $user->isContributor, 403, 'contributors can\'t update published posts');

        // Post yayinlanmak uzere acildiktan sonra baska bir ekranda drafta cekilmis olabilir.
        $isChanged = $request->get('id') != $post?->id;
        abort_if($isChanged && empty($post->published_at) && $request->filled('approved_at'), 400, 'Gönderi yayınlanırken başkası tarafından taslağa çekilmiş, sayfayı yenileyip tekrar deneyiniz!');
        abort_if($isChanged && !empty($post->approved_at) && !$request->filled('published_at'), 400, 'Gönderi taslağa çevirilirken başkası tarafından yayınlanmıştır, sayfayı yenileyip tekrar deneyiniz!');

        if ($post?->published_at !== null) {
            $oldPost = $post;
            $post = $oldPost->replicate();
            if ($oldPost->blogger_id !== $user->id) {
                $post->reviewed_by = $user->id;
            }
            abort_unless($post->push(), 500, 'push failed');

            $oldPost->delete();
        } else if (! $post) {
            $post = new Post(['uuid' => $id]);
        }

        $slug = $data['slug'];
        $slug = mb_substr($slug, 0, 100 - 4); // random number suffix
        if ($post->title !== $data['title'] || str_starts_with($data['slug'], 'post-')) {
            $slug = Str::slug($data['title']);
            if (Post::where('id', '!=', $post->id)->where('slug', $slug)->exists()) {
                $slug .= '-' . rand(0, 9999);
            }
        }
        $slug = mb_substr($slug, 0, 100);

        // published_at and approved_at being broke from client due timezone issue db <-> laravel <-> client
        // this is the easy way to fix. ignore new value if published_at already not empty
        if (!empty($data['published_at']) && !empty($post->published_at)) {
            unset($data['published_at']);
        }
        if (!empty($data['approved_at']) && !empty($post->approved_at)) {
            unset($data['approved_at']);
        }
        $meta = $data['meta'] ?? [];
        if (array_key_exists('title', $meta) && ($post->title !== $data['title'] || empty($meta['title']))) {
            $meta['title'] = $data['title'];
        }
        if (array_key_exists('description', $meta) && ($post->summary !== $data['summary'] || empty($meta['description']))) {
            $meta['description'] = $data['summary'];
        }
        $data['meta'] = $meta;
        $post->fill(array_merge($data, [
            'slug' => $slug
        ]));

        $post->blogger_id ??= $user->id;
        $post->save();

        $tags = Tag::query()->get(['id', 'name', 'slug']);
        $topics = Topic::query()->get(['id', 'name', 'slug']);

        $tagsToSync = collect($request->input('tags', []))->map(function ($item) use ($tags) {
            $tag = $tags->firstWhere('slug', $item['slug']);

            if (! $tag) {
                $tag = Tag::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'blogger_id' => request()->user('canvas')->id,
                ]);
            }

            return (string) $tag->id;
        })->toArray();

        $topicToSync = collect($request->input('topic', []))->map(function ($item) use ($topics) {
            $topic = $topics->firstWhere('slug', $item['slug']);

            if (! $topic) {
                $topic = Topic::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'blogger_id' => request()->user('canvas')->id,
                ]);
            }

            return (string) $topic->id;
        })->toArray();

        $post->tags()->sync($tagsToSync);

        $post->topic()->sync($topicToSync);

        return response()->json($post->refresh(), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $post = Post::query()
                    ->when(request()->user('canvas')->isContributor, function (Builder $query) {
                        return $query->where('blogger_id', request()->user('canvas')->id);
                    })
                    ->with('tags:name,slug', 'topic:name,slug')
                    ->where('uuid', $id)
                    ->firstOrFail();
        return response()->json([
            'post' => $post,
            'tags' => Tag::query()->get(['name', 'slug']),
            'topics' => Topic::query()->get(['name', 'slug']),
        ]);
    }

    /**
     * Display stats for the specified resource.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function stats(string $id): JsonResponse
    {
        $post = Post::query()
                    ->when(request()->user('canvas')->isContributor, function (Builder $query) {
                        return $query->where('blogger_id', request()->user('canvas')->id);
                    }, function (Builder $query) {
                        return $query;
                    })
                    ->approved()
                    ->where('uuid', $id)
                    ->firstOrFail();

        $stats = new StatsAggregator(request()->user('canvas'));

        $results = $stats->getStatsForPost($post);

        return response()->json($results);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return mixed
     *
     * @throws Exception
     */
    public function destroy($id)
    {
        $post = Post::query()
                    ->when(request()->user('canvas')->isContributor, function (Builder $query) {
                        return $query->where('blogger_id', request()->user('canvas')->id);
                    }, function (Builder $query) {
                        return $query;
                    })
                    ->where('uuid', $id)
                    ->firstOrFail();

        $post->delete();

        return response()->json(null, 204);
    }
}
