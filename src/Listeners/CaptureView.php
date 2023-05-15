<?php

namespace Canvas\Listeners;

use Canvas\Canvas;
use Canvas\Events\PostViewed;
use Canvas\Models\Post;
use Illuminate\Support\Facades\Cache;

class CaptureView
{
    /**
     * A view is captured when a user loads a post for the first time in a given
     * hour. The ID of the post is stored in session to be validated against
     * until it "expires" and is pruned by the Session middleware class.
     *
     * @param  PostViewed  $event
     * @return void
     */
    public function handle(PostViewed $event): void
    {
        if (! $this->wasRecentlyViewed($event->post)) {
            $data = [
                'post_id' => $event->post->id,
                'ip' => request()->getClientIp(),
                'agent' => request()->header('user_agent'),
                'referer' => Canvas::parseReferer(request()->header('referer')),
            ];

            $event->post->views()->create($data);

            $this->storeInSession($event->post);
        }
    }

    private function cacheKey(Post $post): string {
        return 'canvas_post_view:'. md5(request()->ip() . $post->id);
    }

    /**
     * Check if a given post exists in the session.
     *
     * @param  Post  $post
     * @return bool
     */
    private function wasRecentlyViewed(Post $post): bool
    {
        return Cache::has($this->cacheKey($post));
    }

    /**
     * Add a given post to the session.
     *
     * @param  Post  $post
     * @return void
     */
    private function storeInSession(Post $post): void
    {
        Cache::put($this->cacheKey($post), 1, now()->addDay());
    }
}
