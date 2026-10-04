@props(['links' => []])
<nav class="flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-2 text-sm text-text-secondary">
        @foreach($links as $label => $url)
            <li>
                <div class="flex items-center">
                    @if(!$loop->first)
                        <svg class="h-4 w-4 flex-shrink-0 text-text-muted mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" /></svg>
                    @endif
                    @if($url)
                        <a href="{{ $url }}" class="hover:text-text-primary transition-colors">{{ $label }}</a>
                    @else
                        <span class="text-text-primary font-medium" aria-current="page">{{ $label }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
