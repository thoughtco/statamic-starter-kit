<?php
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StarterKitPostInstall
{
    public function handle($console)
    {
        $originalAppKey = env('APP_KEY');

        $appName = $console->ask('What should be your app name?');
        $appName = preg_replace('/([\'|\"|#])/m', '', $appName);
        $appURL = $console->ask('What is the app url?');
        $revisionPath = $console->ask('What path do you want to use for revisions?', 'content/revisions');
        $connectionType = $console->ask('What queue connection do you want?', 'redis');

        $env = app('files')->get(base_path('.env.thoughtco'));
        $env = str_replace("APP_NAME=", "APP_NAME=\"{$appName}\"", $env);
        $env = str_replace('APP_URL=', "APP_URL=\"{$appURL}\"", $env);
        $env = str_replace('APP_KEY=', "APP_KEY=\"{$originalAppKey}\"", $env);
        $env = str_replace('STATAMIC_REVISIONS_PATH=', "STATAMIC_REVISIONS_PATH=\"{$revisionPath}\"", $env);
        $env = str_replace('QUEUE_CONNECTION=sync', "QUEUE_CONNECTION=\"{$connectionType}\"", $env);

        app('files')->put(base_path('.env'), $env);
        $console->info('<info>[✓]</info> generate env');

        app('files')->delete(base_path('.env.thoughtco'));
        $console->info('<info>[✓]</info> .env.thoughtco deleted');

        app('files')->delete(base_path('composer.json.bak'));
        $console->info('<info>[✓]</info> composer.json.bak deleted');

        app('files')->deleteDirectory(public_path('css'));
        $console->info('<info>[✓]</info> css folder deleted');

        app('files')->deleteDirectory(public_path('img'));
        $console->info('<info>[✓]</info> img folder deleted');

        app('files')->deleteDirectory(public_path('js'));
        $console->info('<info>[✓]</info> js folder deleted');

        $this->runProcess(['php', 'artisan', 'statamic:glide:clear'], $console, 'statamic glide cache cleared');
        $this->runProcess(['php', 'artisan', 'statamic:stache:clear'], $console, 'statamic stache cleared');
        $this->runProcess(['php', 'artisan', 'route:clear'], $console, 'laravel routes cleared');
        $this->runProcess(['php', 'artisan', 'config:clear'], $console, 'laravel config cleared');
        $this->runProcess(['php', 'artisan', 'cache:clear'], $console, 'laravel cache cleared');

        $this->runProcess(['php', 'artisan', 'horizon:install'], $console, 'horizon assets published');

        $this->runProcess(['php', 'artisan', 'queue:restart'], $console, 'queues restarted');

        $this->runProcess(['npm', 'install', '--force'], $console, 'npm packages installed');

        $console->info('<info>[✓]</info> starter kit installed!');
    }

    private function runProcess(array $command, $console, string $successMessage): void
    {
        $process = new Process($command);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300);
        $process->mustRun();
        $console->info("<info>[✓]</info> {$successMessage}");
    }
}
