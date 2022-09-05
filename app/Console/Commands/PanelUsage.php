<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class PanelUsage extends Command
{
        /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:usage {--count} {--panel=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of times a panel is used';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //get all panels
        $pages = Entry::query()
            ->where('collection', 'pages')
            ->where('published', true)
            ->get(['panels']);
        
        $panels = [];

        $checkMode = $this->option('panel') ? 'panel' : 'count';
        $panelWeWant = $this->option('panel') ?? false;

        foreach ($pages AS $page) {

            if ($page->panels < 1){
                continue;
            }

            // we are checking for a specific panel handle
            if ($checkMode == 'panel') {

                $pageHasPanel = false;
                foreach ($page->panels as $panel) {
                    if ($panel['type'] == $panelWeWant) {
                        $pageHasPanel = true;
                    }
                }

                if ($pageHasPanel) {
                    $panels[] = ['title' => $page->title, 'url' => url($page->url())];
                }

            // we are counting panels
            } else {
                
                foreach ($page->panels as $panel) {
                    $type = $panel['type'];
                    
                    if (!isset($panels[$type])) {
                        $panels[$type] = ['type' => $type, 'count' => 0];
                    }

                    $panels[$type]['count']++;
                }

            }
            
        }

        if ($checkMode == 'count') {

            $panels = collect($panels)->sortByDesc('count')->values();

            $this->table(['Type', 'Count'], $panels);
            return;
        }

        $panels = collect($panels)->sortBy('url')->values();
        $this->table(['Title', 'URL'], $panels);
    }
}