<?php namespace Msurguy\Honeypot;

use Closure, Validator;
use Illuminate\Translation\Translator;

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
        $nameAttribute = $this->honeypot->getNameAttribute();
        $timeAttribute = $this->honeypot->getTimeAttribute();

        $validator = Validator::make($request->only([$nameAttribute, $timeAttribute]), [
            $nameAttribute => 'honeypot',
            $timeAttribute => 'required|honeytime'
        ]);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator);
        }

        return $next($request);
    }

}