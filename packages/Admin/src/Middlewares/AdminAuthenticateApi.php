<?php

namespace Admin\Middlewares;

use Closure;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Illuminate\Contracts\Auth\Factory as Auth;


class AdminAuthenticateApi
{
    private $baseApi;
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->baseApi = new eCBaseController();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string[] ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $message = $this->authenticate($guards);
        if (!empty($message)) {
            return $message;
        }
        $user = auth('admin')->user();
//        if ($user->delete_flag == 1) {
//            return $this->baseApi->sendError("Tài khoản của bạn chưa được kích hoạt.",
//                ApiHttpStatus::NOT_ACCEPTABLE);
//        }
        if (!$user) return $this->baseApi->sendError(ApiHttpStatus::UNAUTHORIZED,"Phiên đăng nhập đã hết hạn.");
        return $next($request);
    }

    protected function authenticate(array $guards)
    {
        if (empty($guards)) {
            return $this->baseApi->sendError('Bạn chưa đăng nhập hệ thống !',ApiHttpStatus::FORBIDDEN);
        }
        if (!empty($guards)) {
            foreach ($guards as $guard) {
                if ($this->auth->guard($guard)->check()) {
                    return $this->auth->shouldUse($guard);
                }
            }
        }
        return $this->baseApi->sendError(ApiHttpStatus::UNAUTHORIZED,"Phiên đăng nhập đã hết hạn.");
    }
}
