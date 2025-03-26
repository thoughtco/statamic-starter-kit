<?php

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StarterKitPostInstall
{
    public function handle($console)
    {
        $originalAppName = env('APP_URL');
        $originalAppUrl = env('APP_URL');
        $originalAppKey = env('APP_KEY');
        $originalRevisionsPath = env('STATAMIC_REVISIONS_PATH');

        $appName = $console->ask('What should be your app name?');
        $appName = preg_replace('/([\'|\"|#])/m', '', $appName);

        $appURL = $console->ask('What is the app url?');

        $env = app('files')->get(base_path('.env.thoughtco'));
        $env = str_replace("APP_NAME=", "APP_NAME=\"{$appName}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appURL}\"", $env);
        $env = str_replace('APP_KEY=', "APP_KEY=\"{$originalAppKey}\"", $env);
        $env = str_replace('STATAMIC_REVISIONS_PATH=', "STATAMIC_REVISIONS_PATH=\"{$originalRevisionsPath}\"", $env);

        // output to console
        $console->info('<info>[✓]</info> generate env');
        app('files')->put(base_path('.env'), $env);

        // success of starter kit installed
        $console->info('<info>[✓]</info> starter kit installed!');

        // delete starter kit .env
        app('files')->delete(base_path('.env.thoughtco'));
        $console->info('<info>[✓]</info> .env.thoughtco deleted');

        // delete composer.json.bak
        app('files')->delete(base_path('composer.json.bak'));
        $console->info('<info>[✓]</info> composer.json.bak deleted');

        // delete public/css
        app('files')->deleteDirectory(public_path('css'));
        $console->info('<info>[✓]</info> css folder deleted');

        // delete public/img
        app('files')->deleteDirectory(public_path('img'));
        $console->info('<info>[✓]</info> img folder deleted');

        // delete public/js
        app('files')->deleteDirectory(public_path('js'));
        $console->info('<info>[✓]</info> js folder deleted');

        // to be super sure we're clear on everything
        // we don't need to do all of this stuff below,
        // but just trying to head off some issues
        Artisan::call('statamic:glide:clear');
        $console->info('<info>[✓]</info> statamic glide cache cleared');

        Artisan::call('statamic:stache:clear');
        $console->info('<info>[✓]</info> statamic stache cleared');

        Artisan::call('route:clear');
        $console->info('<info>[✓]</info> laravel routes cleared');

        Artisan::call('config:clear');
        $console->info('<info>[✓]</info> laravel config cleared');

        Artisan::call('cache:clear');
        $console->info('<info>[✓]</info> laravel cache cleared');

        (new Process(['npm', '-i --force']))->start();
        $console->info('<info>[✓]</info> running npm i --force');
    }
}
