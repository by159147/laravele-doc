<?php


namespace Faed\Doc;

use Faed\Doc\commands\ApiDoc;
use Illuminate\Support\ServiceProvider;
class DocServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('doc.php'),
        ]);

        if (config('doc.is_migration')){
            //迁移文件
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }


        if ($this->app->runningInConsole()) {
            $this->commands([
                ApiDoc::class,
            ]);
        }

        //路由
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->loadViewsFrom(__DIR__.'/views','doc');

        $this->publishes([
            __DIR__.'/static' => public_path('static'),
        ], 'public');
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'doc');
    }



    /**
     * Set the config path
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/config/doc.php';
    }
}
