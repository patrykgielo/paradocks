@props(['data' => []])

@php
    $content = $data['content'] ?? '';
    $layout = $data['layout'] ?? 'default';
    $backgroundColor = $data['background_color'] ?? 'white';

    $bgClass = match($backgroundColor) {
        'neutral-50' => 'bg-neutral-50',
        'primary-50' => 'bg-primary-50',
        default => 'bg-white',
    };

    $containerClass = match($layout) {
        'full-width' => 'w-full',
        'narrow' => 'max-w-3xl mx-auto',
        default => 'container mx-auto',
    };
@endphp

<section class="relative py-24 px-4 md:px-6 {{ $bgClass }} scroll-reveal">
    <div class="{{ $containerClass }}">
        <div class="prose prose-lg prose-gray max-w-none
                    prose-headings:font-light prose-headings:tracking-tight
                    prose-h2:text-4xl prose-h2:mb-4
                    prose-h3:text-2xl prose-h3:mb-3
                    prose-p:text-gray-600 prose-p:leading-relaxed
                    prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 prose-strong:font-semibold
                    prose-ul:list-disc prose-ul:ml-6
                    prose-ol:list-decimal prose-ol:ml-6
                    prose-li:text-gray-600
                    prose-blockquote:border-l-4 prose-blockquote:border-primary-500 prose-blockquote:pl-4 prose-blockquote:italic
                    prose-img:rounded-lg prose-img:shadow-lg
                    prose-table:border-collapse prose-table:w-full
                    prose-th:bg-neutral-100 prose-th:p-3 prose-th:text-left
                    prose-td:p-3 prose-td:border-t prose-td:border-neutral-200">
            {!! $content !!}
        </div>
    </div>
</section>
