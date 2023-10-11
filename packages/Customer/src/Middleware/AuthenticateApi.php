<?php

namespace Customer\Middleware;

use Closure;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Illuminate\Contracts\Auth\Factory as Auth;


class AuthenticateApi
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
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
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
        $user = \Auth::user();
        if ($user->status == 0) {
            return $this->baseApi->sendError( PTApiHttpStatus::NOT_ACCEPTABLE,"Tài khoản của bạn chưa được kích hoạt. Hãy nhận mã kich hoạt OTP từ tin nhắn kích hoạt tài khoản.");
        }
        return $next($request);
    }

    protected function authenticate(array $guards)
    {
        if (empty($guards)) {
            return $this->baseApi->sendError(ApiHttpStatus::UNAUTHORIZED, 'Bạn chưa đăng nhập hệ thống !');
        }
        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }
        return $this->baseApi->sendError( ApiHttpStatus::UNAUTHORIZED,'Bạn chưa đăng nhập hệ thống !');
    }
}
