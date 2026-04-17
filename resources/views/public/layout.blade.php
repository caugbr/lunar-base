<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $page->excerpt ?? $page->title }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .widget-badge {
            text-align: right;
            font-size: 1.2em;
            font-weight: 600;
            color: #3381d5;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1a1a1a;
            margin: 0;
        }

        .page-header .excerpt {
            font-size: 1.1rem;
            color: #666;
            font-style: italic;
        }

        .page-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-content h1, .page-content h2, .page-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }

        .page-content h1 { font-size: 1.8rem; }
        .page-content h2 { font-size: 1.5rem; }
        .page-content h3 { font-size: 1.3rem; }

        .page-content p {
            margin-bottom: 1rem;
        }

        .page-content ul, .page-content ol {
            margin: 1rem 0 1rem 2rem;
        }

        .page-content li {
            margin-bottom: 0.25rem;
        }

        .page-content a {
            color: #0066cc;
            text-decoration: none;
        }

        .page-content a:hover {
            text-decoration: underline;
        }

        .page-content img {
            max-width: 100%;
            height: auto;
        }

        .page-footer {
            text-align: right;
            padding: 1rem;
            color: #999;
            font-size: 0.875rem;
            border-top: 1px solid #e0e0e0;
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            .page-content {
                padding: 1rem;
            }
            .page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            @if(isset($widget))
            <div class="widget-badge">
                {{ $widget->name }}
            </div>
            @endif
            <h1>{{ $page->title }}</h1>
            @if($page->excerpt)
                <div class="excerpt">{{ $page->excerpt }}</div>
            @endif
        </div>

        <div class="page-content">
            {!! $page->content !!}
        </div>

        <div class="page-footer">
            <p>Última atualização: {{ $page->updated_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
    </div>
</body>
</html>
