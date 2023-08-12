<?php
namespace Elijahcruz\Livt;

use Elijahcruz\Livt\Console\InstallLivtCommand;
use Elijahcruz\Livt\Console\MakePageCommand;
use Illuminate\Support\ServiceProvider;

class LivtServiceProvider extends ServiceProvider
{
    public function register()
    {
        //...
    }

    public function boot()
    {
        if($this->app->runningInConsole()){
            $this->commands([
//                InstallLivtCommand::class,
            MakePageCommand::class
            ]);
        }
    }
}