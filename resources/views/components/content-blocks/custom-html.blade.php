@props(['data' => []])

@php
    $html = $data['html'] ?? '';
    $containerWrapper = $data['container_wrapper'] ?? true;
@endphp

<section class="relative py-24 px-4 md:px-6 scroll-reveal">
    @if($containerWrapper)
        <div class="container mx-auto">
            {!! $html !!}
        </div>
    @else
        {!! $html !!}
    @endif
</section>
