@if ($paginator->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between py-2 px-3">
        <div class="text-muted mb-2 mb-md-0" style="font-size: 13px;">
            Showing
            <span class="font-weight-bold text-dark">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-weight-bold text-dark">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-weight-bold text-dark">{{ $paginator->total() }}</span>
            results
        </div>

        <nav aria-label="Table navigation">
            <ul class="pagination pagination-sm m-0 justify-content-center">
                {{-- First / Start Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" title="First Page">
                        <span class="page-link" aria-hidden="true">&laquo; First</span>
                    </li>
                @else
                    <li class="page-item" title="First Page">
                        <a class="page-link" href="{{ $paginator->url(1) }}" rel="first">&laquo; First</a>
                    </li>
                @endif

                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link" aria-hidden="true">&lsaquo; Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo; Prev</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @if (isset($elements) && is_array($elements))
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link" style="background-color: #5B4FE9; border-color: #5B4FE9; color: #fff;">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @else
                    @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                        @if ($i == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link" style="background-color: #5B4FE9; border-color: #5B4FE9; color: #fff;">{{ $i }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                            </li>
                        @endif
                    @endfor
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">Next &rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link" aria-hidden="true">Next &rsaquo;</span>
                    </li>
                @endif

                {{-- Last / End Page Link --}}
                @if ($paginator->hasMorePages() && $paginator->currentPage() < $paginator->lastPage())
                    <li class="page-item" title="Last Page">
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last">Last &raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" title="Last Page">
                        <span class="page-link" aria-hidden="true">Last &raquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@elseif ($paginator->total() > 0)
    <div class="py-2 px-3 text-center text-muted" style="font-size: 13px;">
        Showing
        <span class="font-weight-bold text-dark">{{ $paginator->firstItem() }}</span>
        to
        <span class="font-weight-bold text-dark">{{ $paginator->lastItem() }}</span>
        of
        <span class="font-weight-bold text-dark">{{ $paginator->total() }}</span>
        results
    </div>
@endif