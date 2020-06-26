<?php

namespace ShibuyaKosuke\LaravelDatabaseValidator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use ShibuyaKosuke\LaravelDatabaseValidator\Console\RulePublishCommand;
use ShibuyaKosuke\LaravelDatabaseValidator\Console\TransPublishCommand;
use ShibuyaKosuke\LaravelDatabaseValidator\Rule\Repository;

/**
 * Class ValidationServiceProvider
 * @package ShibuyaKosuke\LaravelDatabaseValidator
 */
class ValidationServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->registerCommands();
        $this->registerPath('rules');
    }

    public function provides()
    {
        return [
            'rules',
            'command.shibuyakosuke.publish.rule'
        ];
    }

    protected function registerCommands()
    {
        $this->app->singleton('command.shibuyakosuke.publish.trans', function () {
            return new TransPublishCommand();
        });

        $this->app->singleton('command.shibuyakosuke.publish.rule', function () {
            return new RulePublishCommand();
        });

        $this->commands([
            'command.shibuyakosuke.publish.rule',
            'command.shibuyakosuke.publish.trans'
        ]);
    }

    protected function registerPath($name)
    {
        if (!file_exists(base_path($name))) {
            mkdir(base_path($name));
        }

        $this->app->alias($name, Repository::class);
        $this->app->singleton($name, function ($app) use ($name) {
            $items = [];
            foreach (File::allFiles(base_path($name)) as $splFileInfo) {
                $path = $splFileInfo->getRealPath();
                $items[$splFileInfo->getFilenameWithoutExtension()] = array_merge(require $path, []);
            }
            return new Repository($items);
        });
        $this->app->instance($name, $this->app->$name);
    }
}