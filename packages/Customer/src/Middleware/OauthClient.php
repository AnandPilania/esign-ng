<?php

namespace Customer\Middleware;

use Closure;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Illuminate\Support\Facades\DB;

class OauthClient
{
    private $baseApi;

    public function __construct(eCBaseController $baseApi)
    {
        $this->baseApi = $baseApi;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $clientId = $request->header('client-id');
        $clientSecret = $request->header('client-secret');

        if (empty($clientId) || empty($clientSecret)) {
            return $this->baseApi->sendError('Missing request param: client-id or client-secret', ApiHttpStatus::NOT_FOUND);
        }

        $client = DB::table('oauth_clients')->where('name', $clientId)->where('secret', $clientSecret)->first();
        if ($client) {
            return $next($request);
        } else {
            return $this->baseApi->sendError('client is not valid', ApiHttpStatus::FORBIDDEN);
        }
    }
}
