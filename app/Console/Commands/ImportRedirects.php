<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class ImportRedirects extends Command
{
    protected $signature = 'site:redirects:import
                            {file : Path to the CSV file}
                            {--collection=redirects : The collection handle used by thoughtco/statamic-redirects}
                            {--site= : Site handle for multi-site installs (leave blank for single-site)}
                            {--dry-run : Preview what would be imported without saving}
                            {--skip-existing : Skip rows where an entry with the same slug already exists}';

    protected $description = 'Import redirects from a CSV file into the thoughtco/statamic-redirects collection';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $collection = $this->option('collection');
        $site = $this->option('site') ?? \Statamic\Facades\Site::default()->handle();
        $dryRun = $this->option('dry-run');
        $skip = $this->option('skip-existing');

        [$headers, $rows] = $this->parseCsv($file);

        if (empty($rows)) {
            $this->warn('No data rows found in the CSV.');
            return self::SUCCESS;
        }

        $map = $this->buildColumnMap($headers);

        if ($map['source'] === null || $map['target'] === null) {
            $this->error('Could not find required columns. Expected: "Old URL" and "New URL".');
            $this->line('Found headers: '.implode(', ', $headers));
            return self::FAILURE;
        }

        $this->info("Importing from: {$file}");
        $this->info("Collection: {$collection} | Site: {$site}" . ($dryRun ? ' [DRY RUN]' : ''));
        $this->newLine();

        $created = $skipped = $failed = 0;

        foreach ($rows as $lineNumber => $row) {
            $sourceUrl = trim($row[$map['source']] ?? '');
            $targetUrl = trim($row[$map['target']] ?? '');
            $code = isset($map['code']) ? (int) trim($row[$map['code']] ?? '301') : 301;

            if ($sourceUrl === '' || $targetUrl === '') {
                $this->warn("Line {$lineNumber}: skipping — empty source or target.");
                $failed++;
                continue;
            }

            $slug = $this->toSlug($sourceUrl);

            if ($skip && Entry::query()->where('collection', $collection)->where('slug', $slug)->exists()) {
                $this->line("Skipping existing: {$slug}");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] {$sourceUrl} → {$targetUrl} ({$code})");
                $created++;
                continue;
            }

            try {
                Entry::make()
                    ->collection($collection)
                    ->locale($site)
                    ->slug($slug)
                    ->published(true)
                    ->data([
                        'title' => $sourceUrl,
                        'to'    => $targetUrl,
                        'code'  => $code,
                    ])
                    ->save();

                $this->line("Created: {$sourceUrl} → {$targetUrl}");
                $created++;
            } catch (\Throwable $e) {
                $this->error("Line {$lineNumber}: failed [{$sourceUrl}]: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->table(
            ['Created', 'Skipped', 'Failed'],
            [[$created, $skipped, $failed]]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function toSlug(string $url): string
    {
        $path = ltrim(urldecode($url), '/');
        $slug = preg_replace('/[^a-zA-Z0-9\-]/', '-', $path);
        $slug = preg_replace('/-+/', '-', $slug);

        return strtolower(trim($slug, '-'));
    }

    private function parseCsv(string $file): array
    {
        $handle  = fopen($file, 'r');
        $headers = [];
        $rows = [];
        $line = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if ($line === 1) {
                $headers = array_map('trim', $data);
                continue;
            }
            if (count(array_filter($data)) === 0) {
                continue;
            }
            $rows[$line] = $data;
        }

        fclose($handle);

        return [$headers, $rows];
    }

    private function buildColumnMap(array $headers): array
    {
        $map = ['source' => null, 'target' => null, 'code' => null];

        $aliases = [
            'source' => ['old url', 'source', 'from', 'from url', 'old_url', 'source_url'],
            'target' => ['new url', 'target', 'to', 'to url', 'new_url', 'destination', 'redirect_to'],
            'code'   => ['code', 'status', 'status code', 'status_code', 'http code', 'type'],
        ];

        foreach ($headers as $index => $header) {
            $lower = strtolower($header);
            foreach ($aliases as $key => $list) {
                if (in_array($lower, $list)) {
                    $map[$key] = $index;
                }
            }
        }

        return $map;
    }
}