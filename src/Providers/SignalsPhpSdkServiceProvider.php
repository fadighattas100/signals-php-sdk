<?php


namespace Compredict\SignalsPhpSdk\Providers;


use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SignalsPhpSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(SignalsPhpSdkServiceProvider::class, function ($app) {
			return new SignalsPhpSdkServiceProvider($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [SignalsPhpSdkServiceProvider::class];
	}
}
