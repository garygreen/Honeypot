<?php namespace Msurguy\Honeypot;

use Closure, Validator;

class HoneypotMiddleware {

    /**
     * Honeypot
     *
     * @var Msurguy\Honeypot\Honeypot
     */
    protected $honeypot;

    /**
     * Start honeypot middleware
     *
     * @param  Msurguy\Honeypot\Honeypot  $honeypot
     * @return void
     */
    public function __construct(Honeypot $honeypot)
    {
        $this->honeypot = $honeypot;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tokenAttribute = $this->honeypot->getTokenAttribute();

        $validator = Validator::make($request->get($tokenAttribute), [
            $tokenAttribute => 'honeypot'
        ]);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator);
        }

        return $next($request);
    }

}