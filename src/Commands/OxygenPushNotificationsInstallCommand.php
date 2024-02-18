<?php

namespace EMedia\OxygenPushNotifications\Commands;

use ElegantMedia\OxygenFoundation\Console\Commands\ExtensionInstallCommand;
use EMedia\OxygenPushNotifications\OxygenPushNotificationsServiceProvider;
use ElegantMedia\PHPToolkit\FileEditor;

class OxygenPushNotificationsInstallCommand extends ExtensionInstallCommand
{
    protected $signature = 'oxygen:push-notifications:install {--install_dependencies=true}';

    protected $description = 'Setup the Oxygen Push notifications management package';

    protected $requiredServiceProviders = [
        'Kreait\Laravel\Firebase\ServiceProvider',
    ];

    public function getExtensionServiceProvider(): string
    {
        return OxygenPushNotificationsServiceProvider::class;
    }

    public function getExtensionDisplayName(): string
    {
        return 'Push Notifications';
    }

    /**
     * TODO: This overwrites the function at super class.
     * This is added beacause the main package function had an issue.
     * This should be removed after the issue fixed in ElegantMedia\OxygenFoundation
     *
     * Install the Fortify service providers in the application configuration file.
     *
     * @return void
     * @throws FileNotFoundException
     */
    protected function appendServiceProviders(): void
    {
        if (count($this->requiredServiceProviders) === 0) {
            return;
        }

        $path = config_path('app.php');

        $shortlisted = [];
        foreach ($this->requiredServiceProviders as $serviceProvider) {
            if (!FileEditor::isTextInFile($path, $serviceProvider)) {
                $shortlisted[] = $serviceProvider.'::class';
            }
        }

        if (count($shortlisted) === 0) {
            return;
        }

        array_unshift($shortlisted, 'App\Providers\RouteServiceProvider::class');

        $append = implode(','.PHP_EOL."\t\t", $shortlisted) . ",";

        FileEditor::findAndReplace(
            $path,
            "App\\Providers\RouteServiceProvider::class,",
            $append
        );
    }

    /**
     * @return bool
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    protected function installRequiredDependencies(): bool
    {
        if (!$this->hasOption('install_dependencies')) {
            return false;
        }

        if (!filter_var($this->option('install_dependencies'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $this->appendServiceProviders();

        return true;
    }
}
