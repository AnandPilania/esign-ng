<?php

namespace Viettel\Middlewares;

use Admin\Events\OperationLogEvent;
use Core\Helpers\GuardUtils;
use Core\Helpers\IPHelper;
use Closure;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class RedirectIfVendorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $admin = Auth::guard('vendor')->user();
        $uri = $request->getRequestUri();
        $ip = IPHelper::getIp();
        DB::listen(function ($query) use ($admin, $ip, $uri) {
            $sql = $query->sql . ' [' . implode(', ', $query->bindings) . ']';
            if ($this->startsWith($sql, 'insert') || $this->startsWith($sql, 'update') || $this->startsWith($sql, 'delete'))
                event(new OperationLogEvent(GuardUtils::ADMIN_SITE, $admin->id, $admin->email, $uri, $sql, $ip));
        });

        return $next($request);
    }

    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (strtoupper(substr($string, 0, $len)) === strtoupper($startString));
    }
}
