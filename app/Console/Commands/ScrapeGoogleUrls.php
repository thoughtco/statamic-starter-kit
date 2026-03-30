<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScrapeGoogleUrls extends Command
{
	protected $signature = 'site:google-urls
							{domain : The domain to search (e.g. example.com)}
							{--output= : Output CSV file path (defaults to domain-urls.csv)}
							{--limit=100 : Max number of URLs to retrieve}
							{--delay=1 : Delay in seconds between API requests}';

	protected $description = 'Fetch all Google-indexed URLs for a domain via SerpApi and export to CSV';

	private string $apiKey;
	private string $baseUrl = 'https://serpapi.com/search';

	public function handle(): int
	{
		$this->apiKey = config('services.serpapi.api_key');

		if (empty($this->apiKey)) {
			$this->error('Missing SERPAPI_API_KEY in your .env file.');
			return self::FAILURE;
		}

		$domain = $this->argument('domain');
		$limit  = (int) $this->option('limit');
		$delay  = (int) $this->option('delay');
		$output = $this->option('output') ?? Str::slug($domain) . '-urls.csv';

		$this->info("Searching Google for indexed URLs on: {$domain}");
		$this->info("Output file: {$output}");
		$this->newLine();

		$urls    = [];
		$start   = 0;
		$perPage = 10;

		$progress = $this->output->createProgressBar($limit);
		$progress->start();

		while (count($urls) < $limit) {
			$response = Http::get($this->baseUrl, [
				'api_key' => $this->apiKey,
				'engine'  => 'google',
				'q'       => "site:{$domain}",
				'start'   => $start,
				'num'     => $perPage,
			]);

			if ($response->failed()) {
				$this->newLine();
				$error = $response->json('error') ?? $response->status();
				$this->error("API request failed: {$error}");
				break;
			}

			$data = $response->json();

			// SerpApi returns an error key in the JSON for soft errors
			if (!empty($data['error'])) {
				$this->newLine();
				$this->error("SerpApi error: {$data['error']}");
				break;
			}

			$items = $data['organic_results'] ?? [];

			if (empty($items)) {
				$this->newLine();
				$this->info('No more results found.');
				break;
			}

			foreach ($items as $item) {
				$url = rtrim($item['link'], '/');

				if (!in_array($url, $urls)) {
					$urls[] = $url;
					$progress->advance();
				}

				if (count($urls) >= $limit) {
					break;
				}
			}

			$start += $perPage;

			if (count($items) < $perPage) {
				// Fewer results than requested — end of results
				break;
			}

			sleep($delay);
		}

		$progress->finish();
		$this->newLine(2);

		if (empty($urls)) {
			$this->error('No URLs were found. Check your API key or try a different domain.');
			return self::FAILURE;
		}

		$this->writeCsv($urls, $output);

		$this->info('✓ Exported ' . count($urls) . " URLs to {$output}");

		return self::SUCCESS;
	}

	private function writeCsv(array $urls, string $path): void
	{
		$dir = dirname($path);
	
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
	
		$handle = fopen($path, 'w');
	
		// UTF-8 BOM for Excel compatibility
		fwrite($handle, "\xEF\xBB\xBF");
	
		fputcsv($handle, ['Old URLs']);
	
		foreach ($urls as $url) {
			fputcsv($handle, [$url]);
		}
	
		fclose($handle);
	}
}