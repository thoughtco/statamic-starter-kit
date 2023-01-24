<?php

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StarterKitPostInstall
{
    public function handle($console)
    {

        $originalAppUrl = env('APP_URL');

        $appName = $console->ask('What should be your app name?');
        $appName = preg_replace('/([\'|\"|#])/m', '', $appName);

        $appURL = $console->ask('What is the app url?');

        $appKey = Artisan::call('key:generate');

        $env = app('files')->get(base_path('.env.example'));
        $env = str_replace("APP_NAME=", "APP_NAME=\"{$appName}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appURL}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appKey}\"", $env);

        app('files')->put(base_path('.env'), $env);

        $console->info('<info>[✓]</info> Starter kit installed!');
    }
}
