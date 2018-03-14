<?php

namespace App\Http\Middleware;

use App\Services\SiteService;
use Closure;
use Illuminate\Http\JsonResponse;

class IdentifySite
{
    protected $siteService;

    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->siteService->fromRequest($request);
        if (!$this->siteService->isValid()) {
            return JsonResponse::create(['status' => 0, 'msg' => $this->siteService->getMsg()]);
//            abort('410', $site->getMsg());
        }
        $this->siteService->shareConfigWithViews();
        return $next($request);
    }
}
