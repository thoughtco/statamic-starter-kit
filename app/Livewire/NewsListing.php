<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use MarcoRieser\Livewire\WithPagination;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class NewsListing extends Component
{
    use WithPagination;

    #[Locked]
    public int $pageLimit = 9;

    #[Locked]
    public string $site;

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'recent')]
    public string $sort = 'recent';

    public function mount(): void
    {
        $this->site = Site::current()->handle();
    }

    public function setCategory(string $key): void
    {
        $this->category = $key;
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    protected function entries()
    {
        $query = Entry::query()
            ->whereCollection('news')
            ->where('site', $this->site)
            ->whereStatus('published')
            ->when($this->category, fn ($q) => $q->whereTaxonomy("news_categories::{$this->category}"));

        match ($this->sort) {
            'alphabetical' => $query->orderBy('title', 'asc'),
            default => $query->orderBy('date', 'desc'),
        };

        $entries = $query->paginate($this->pageLimit)->onEachSide(1);

        return $this->withPagination('entries', $entries);
    }

    public function paginationView(): string
    {
        return 'livewire.pagination';
    }

    public function render()
    {
        return view('livewire.news_listing', [
            'results' => $this->entries(),
        ]);
    }
}
