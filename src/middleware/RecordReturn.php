<?php


namespace Faed\Doc\middleware;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordReturn
{
    public function handle($request, Closure $next)
    {
        /**@var $request Request **/
        $resource = $next($request);
        try {
            /**@var $resource JsonResponse **/
            Cache::set($request->route()->uri(),$resource->getContent());
        }catch (Exception $exception){
            Log::error("保存返回缓存:".$exception->getMessage());
        }

        return $resource;
    }
}
