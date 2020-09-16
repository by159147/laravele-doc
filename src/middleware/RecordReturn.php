<?php


namespace Faed\Doc\middleware;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
class RecordReturn
{
    public function handle($request, Closure $next)
    {
        /**@var $request Request **/
        $resource = $next($request);
        /**@var $resource JsonResponse **/
        Cache::set($request->path(),$resource->getContent());
        return $resource;
    }
}
