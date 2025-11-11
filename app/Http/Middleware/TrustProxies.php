<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = self::HEADER_X_FORWARDED_ALL;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Set trusted proxies dynamically based on the environment.
        // In production, we'll trust the IP(s) defined in the .env file.
        // For local development (DDEV), we trust all proxies.
        if (config('app.env') === 'production') {
            $this->proxies = env('TRUSTED_PROXIES', '127.0.0.1');
        } else {
            $this->proxies = '*';
        }

        return parent::handle($request, $next);
    }
}
