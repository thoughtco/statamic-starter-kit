<div>

    @if (!$paginator->onFirstPage())
        <a
            wire:click="previousPage('{{ $paginator->getPageName() }}')"
            wire:loading.attr="disabled"
            rel="prev"
            aria-label="Previous Page">
            &lt;
        </a>
    @endif

    <div>
        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())
                        <a>
                            {{ str_pad($page, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @else
                        @if (abs($paginator->currentPage() - $page) <= 2)
                            <a
                               wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                               href="{{ $paginator->url($page) }}">
                                {{ str_pad($page, 2, '0', STR_PAD_LEFT) }}
                            </a>
                        @endif
                    @endif

                @endforeach
            @endif
        @endforeach
    </div>

    @if ($paginator->hasMorePages())
        <a
           wire:loading.attr="disabled"
           dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
           rel="next"
           aria-label="Next Page">
            &gt;
        </a>
    @endif

</div>

<script>
    if (pagination = document.body.querySelector('.pagination')){
        document.body.querySelector('.pagination').addEventListener('click', (event) => {
            if (event.target.tagName.toLowerCase() == 'a') {
               document.getElementById('articles').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
</script>
