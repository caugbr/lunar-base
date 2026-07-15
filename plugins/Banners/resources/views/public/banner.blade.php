@php
$bannerClasses = $class ?? '';
$targetAttr = $banner->target ?? '_self';
@endphp

<div class="banner-wrapper {{ $bannerClasses }}">
    <a href="{{ route('banner.click', $banner->id) }}"
       target="{{ $targetAttr }}"
       class="banner-link"
       data-banner-id="{{ $banner->id }}">
        <img src="{{ $banner->image_url }}"
             alt="{{ $banner->title }}"
             class="banner-image"
             loading="lazy">
    </a>
</div>
