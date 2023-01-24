<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StarterKitPostInstall
{
    public function handle($console)
    {

        $originalAppName = env('APP_URL');
        $originalAppUrl = env('APP_URL');
        $originalAppKey = env('APP_KEY');

        $appName = $console->ask('What should be your app name?');
        $appName = preg_replace('/([\'|\"|#])/m', '', $appName);

        $appURL = $console->ask('What is the app url?');

        $env = app('files')->get(base_path('.env.thoughtco'));
        $env = str_replace("APP_NAME=", "APP_NAME=\"{$appName}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appURL}\"", $env);
        $env = str_replace('APP_KEY=', "APP_KEY=\"{$originalAppKey}\"", $env);

        app('files')->put(base_path('.env'), $env);

        // delete starter kit .env
        app('files')->delete(base_path('.env.thoughtco'));

        // delete .env.example
        app('files')->delete(base_path('.env.example'));

        // delete composer.json.bak
        app('files')->delete(base_path('composer.json.bak'));

        $console->info('<info>[✓]</info> Starter kit installed!');
    }
}
