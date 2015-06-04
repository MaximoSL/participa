<?php

namespace MXAbierto\Participa\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class RoleAuthentication
{
    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string                   $role
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($this->auth->check()) {
            $user = $this->auth->user();

            if (! $user->hasRole($role)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
