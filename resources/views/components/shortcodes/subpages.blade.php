@props([
    "title" => "Subpáginas",
    "title_icon" => "link-tree",
    "item_icon" => "chevron-right",
])

@if(isset($page) && $page->children->isNotEmpty())

@once
<style>
    .subpages-navigation {
        margin: 2rem 0;
        padding: 1.5rem;
        background: var(--color-bg-dark, #12152b);
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        border-radius: 12px;
    }

    .subpages-navigation h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        color: var(--color-primary, #c8b6ff);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .subpages-navigation h3 .lucid-icon {
        width: 18px;
        height: 18px;
    }

    .subpages-navigation ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }

    .subpages-navigation li {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .subpages-navigation li .lucid-icon {
        width: 14px;
        height: 14px;
        color: var(--color-text-muted, #8a87a8);
        flex-shrink: 0;
    }

    .subpages-navigation a {
        font-weight: 500;
        color: var(--color-text, #e8e6f0);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .subpages-navigation a:hover {
        color: var(--color-primary, #c8b6ff);
    }
</style>
@endonce

<div class="subpages-navigation">
    <h3>
        @if($title_icon)
        <x-dynamic-component component="lucide-{{ $title_icon }}" class="lucid-icon" />
        @endif
        {{ $title }}
    </h3>
    <ul>
        @foreach($page->children as $subpage)
            <li>
                @if($item_icon)
                <x-dynamic-component component="lucide-{{ $item_icon }}" class="lucid-icon" />
                @endif
                <a href="{{ $subpage->url }}">
                    {{ $subpage->title }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endif
