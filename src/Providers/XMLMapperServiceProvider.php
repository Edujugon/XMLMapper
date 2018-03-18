<?php
namespace Edujugon\XMLMapper\Providers;

use Edujugon\XMLMapper\XMLMapper;
use Illuminate\Support\ServiceProvider;

class XMLMapperServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(XMLMapper::class, function ($app) {
            return new XMLMapper();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [XMLMapper::class];
    }

}
