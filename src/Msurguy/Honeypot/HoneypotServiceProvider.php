<?php namespace Msurguy\Honeypot;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HoneypotServiceProvider extends ServiceProvider {

    /**
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
    * Register the service provider.
    *
    * @return void
    */
    public function register()
    {
        $this->app->alias('honeypot', 'Msurguy\Honeypot\Honeypot');
        
        $this->app->bindShared('honeypot', function($app)
        {
            $honeypot = new Honeypot($app['encrypter']);
            $honeypot->setNameAttribute(config('honeypot.name_attribute'))
                     ->setTimeAttribute(config('honeypot.time_attribute'));
            return $honeypot;
        });
    }

    /**
    * Bootstrap the application events.
    *
    * @return void
    */
    public function boot()
    {
        if ($this->isLaravelVersion('4'))
        {
            $this->package('msurguy/honeypot');
        }
        elseif ($this->isLaravelVersion('5'))
        {
            $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'honeypot');

            $honeypotConfig = __DIR__ . '/../../config/honeypot.php';

            $this->mergeConfigFrom($honeypotConfig, 'honeypot');

            $this->publishes([
               $honeypotConfig => config_path('honeypot.php'),
            ]);
        }

        $this->app->booted(function($app) {

            // Get validator and translator
            $validator = $app['validator'];
            $translator = $app['translator'];

            // Add honeypot and honeytime custom validation rules
            $validator->extend('honeypot', function($attribute, $value) {
                return $this->app['honeypot']->validateHoneypot($value);
            }, $translator->get('honeypot::validation.honeypot'));

            $validator->extend('honeytime', function($attribute, $value, $parameters) {
                $honeypot = $this->app['honeypot'];

                if (isset($parameters[0]))
                {
                    $honeypot->speed($parameters[0]);
                }

                return $honeypot->validateHoneytime($value);
            }, $translator->get('honeypot::validation.honeytime'));

            // Register the honeypot form macros
            $this->registerFormMacro();
        });
    }

    /**
    * Get the services provided by the provider.
    *
    * @return array
    */
    public function provides()
    {
        return array('honeypot');
    }

    /**
    * Register the honeypot form macro
    *
    * @param  Illuminate\Html\FormBuilder|null $form
    * @return void
    */
    public function registerFormMacro(FormBuilder $form = null)
    {
        $honeypotMacro = function() {
            return app('honeypot')->html();
        };
        
        if (class_exists('\Illuminate\Html\FormBuilder'))
        {
            \Illuminate\Html\FormBuilder::macro('honeypot', $honeypotMacro);
        }
        elseif (class_exists('\Collective\Html\HtmlBuilder'))
        {
            \Collective\Html\HtmlBuilder::macro('honeypot', $honeypotMacro);
        }
    }


    /**
     * Determine if laravel starts with any of the given version strings
     * 
     * @param  string|array  $startsWith
     * @return boolean
     */
    protected function isLaravelVersion($startsWith)
    {
        return Str::startsWith(Application::VERSION, $startsWith);
    }
}
