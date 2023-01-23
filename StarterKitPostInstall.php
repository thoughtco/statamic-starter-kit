<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StarterKitPostInstall
{

    public function handle($console)
    {

		// original variables
        $originalAppUrl = env('APP_URL');
        $originalAppKey = env('APP_KEY');

        $appName = $console->ask('What should be your app name?');
        $appName = preg_replace('/([\'|\"|#])/m', '', $appName);

        $appURL = $console->ask('What is the app url?');

		$appKey = \Illuminate\Support\Facades\Artisan::call('key:generate');

        $env = app('files')->get(base_path('.env.example'));
        $env = str_replace("APP_NAME=", "APP_NAME=\"{$appName}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appURL}\"", $env);
        $env = str_replace('APP_KEY=', "APP_KEY=\"{$appKey}\"", $env);

        app('files')->put(base_path('.env'), $env);

        $console->info('<info>[✓]</info> Starter kit installed!');
        // $console->newline();
        // $console->warn('Run `php please peak:clear-site` to get rid of default content.');
        // $console->newline();
        // $console->warn('Run `php please peak:install-preset` to install premade sets onto your website.');
        // $console->newline();
        // $console->warn('Run `php please peak:install-block` to install premade blocks onto your page builder.');
        // $console->newline();
    }
}