
@if ($paginator->hasPages())
    <div class="flex items-center justify-between px-4 py-3 sm:px-6 mt-4">
        <!-- Mobile View -->
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-[#161b22] border border-[#30363d] rounded-md cursor-default leading-5">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-[#c9d1d9] bg-[#161b22] border border-[#30363d] rounded-md leading-5 hover:text-white focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-[#c9d1d9] bg-[#161b22] border border-[#30363d] rounded-md leading-5 hover:text-white focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-[#161b22] border border-[#30363d] rounded-md cursor-default leading-5">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <!-- Desktop View -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
            <div class="flex items-center gap-4">
                <!-- Pagination Info Text -->
                <div>
                    <p class="text-sm text-[#8b949e]">
                        Showing
                        <span class="font-medium text-white">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-medium text-white">{{ $paginator->lastItem() }}</span>
                        of
                        <span class="font-medium text-white">{{ $paginator->total() }}</span>
                        results
                    </p>
                </div>

                <!-- Pagination Buttons -->
                <div>
                    <span class="relative z-0 inline-flex shadow-sm rounded-md ml-4">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-[#161b22] border border-[#30363d] cursor-default rounded-l-md leading-5" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @else
                            <button type="button" wire:click="previousPage" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-[#c9d1d9] bg-[#161b22] border border-[#30363d] rounded-l-md leading-5 hover:text-white focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('pagination.previous') }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-[#161b22] border border-[#30363d] cursor-default leading-5">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-[#238636] border border-[#238636] cursor-default leading-5">{{ $page }}</span>
                                        </span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-[#c9d1d9] bg-[#161b22] border border-[#30363d] leading-5 hover:text-white focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <button type="button" wire:click="nextPage" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-[#c9d1d9] bg-[#161b22] border border-[#30363d] rounded-r-md leading-5 hover:text-white focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('pagination.next') }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-[#161b22] border border-[#30363d] cursor-default rounded-r-md leading-5" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif
