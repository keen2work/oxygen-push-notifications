<?php

namespace EMedia\OxygenPushNotifications;

use EMedia\OxygenPushNotifications\Entities\PushNotifications\PushNotificationsRepository;
use ElegantMedia\OxygenFoundation\Facades\Navigator;
use ElegantMedia\OxygenFoundation\Navigation\NavItem;
use EMedia\OxygenPushNotifications\Commands\SendPushNotificationsQueueCommand;
use EMedia\OxygenPushNotifications\Commands\SubscribeDevicesToTopic;
use EMedia\OxygenPushNotifications\Commands\OxygenPushNotificationsInstallCommand;
use EMedia\OxygenPushNotifications\Commands\TestPushNotificationsCommand;
use Illuminate\Support\ServiceProvider;

class OxygenPushNotificationsServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'oxygen-push-notifications');

        $this->publishes([
            __DIR__ . '/../publish' => base_path(),
        ], 'oxygen::auto-publish');

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/oxygen-push-notifications'),
        ], 'views');

		/*$this->publishes([
			__DIR__ . '/../publish/app/Entities/PushNotifications' => app_path('Entities/PushNotifications'),
			__DIR__ . '/../publish/app/Http/Controllers/Manage' => app_path('Http/Controllers/Manage'),
		], 'package-required-files');*/

        $this->setupNavItem();
	}

	public function register()
	{
		if (!app()->environment('production')) {
			$this->commands(OxygenPushNotificationsInstallCommand::class);
			$this->commands(TestPushNotificationsCommand::class);
		}

		$this->mergeConfigFrom( __DIR__ . '/../config/features.php', 'features');

		$this->commands(SubscribeDevicesToTopic::class);

		if (class_exists(PushNotificationsRepository::class)) {
			$this->commands(SendPushNotificationsQueueCommand::class);
		}
	}

    protected function setupNavItem()
    {
        // register the menu items
        $navItem = new NavItem('Push Notifications');
        $navItem->setResource('manage.push-notifications.index')
            ->setIconClass('fas fa-comment');

        Navigator::addItem($navItem, 'sidebar.manage');
    }
}
