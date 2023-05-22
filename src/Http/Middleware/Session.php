<?php

namespace Canvas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Session
{
    /**
     * Handle the incoming request.
     *
     * @param $request
     * @param $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $viewedPosts = collect(session()->get('viewed_posts'));

        if ($viewedPosts->isNotEmpty()) {
            $viewedPosts->each(function ($timestamp, $id) {
                if ($timestamp < now()->subSeconds(3600)->timestamp) {
                    session()->forget("viewed_posts.{$id}");
                }
            });
        }

        return $next($request);
    }
}
