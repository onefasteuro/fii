<?php

namespace FII;


class ServiceProvider extends \WPBootstrapper\Providers\AbstractServiceProvider
{
    public function boot()
    {
        // intentionally left blank
    }

    public function register()
    {
        $this->container->singleton('fii', function($container){
            $config = $container->make('config')->get('fii');
            $cache = $container->make('cache');
            $provider = new Provider($config, $cache);
            return $provider;
        });
    }
}