<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * مثال استفاده در route: ->middleware('role:admin')
     * یا برای چند نقش مجاز: ->middleware('role:admin,trader')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! $request->user()->hasAnyRole($roles)) {
            abort(403, 'شما دسترسی لازم برای مشاهده این بخش را ندارید.');
        }

        return $next($request);
    }
}
