<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Tu Boletín Semanal</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
            padding-bottom: 40px;
        }

        .webkit {
            max-width: 600px;
            margin: 0 auto;
        }

        .outer {
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #1f2937;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
        }

        .header p {
            margin: 10px 0 0 0;
            color: #9ca3af;
            font-size: 14px;
        }

        .content {
            padding: 30px 20px;
        }

        .category-title {
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 8px 15px;
            border-radius: 6px;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            display: inline-block;
        }

        .article {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .article:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .article-title {
            margin: 0 0 10px 0;
            font-size: 18px;
            line-height: 1.4;
        }

        .article-title a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .article-summary {
            margin: 0 0 12px 0;
            color: #4b5563;
            font-size: 15px;
            line-height: 1.6;
        }

        .article-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 13px;
        }

        .mt-40 {
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <center class="wrapper">
        <div class="webkit">
            <div class="outer">
                <div class="header">
                    <h1>Tu Boletín Semanal</h1>
                    <p>El mejor contenido curado y resumido por IA</p>
                </div>

                <div class="content">
                    @forelse($articles->groupBy('category') as $category => $categoryArticles)
                        <div class="{{ $loop->first ? '' : 'mt-40' }}">
                            <h2 class="category-title">
                                {{ $category instanceof \App\Enums\CategoryArticle ? $category->value : $category }}
                            </h2>

                            @foreach ($categoryArticles as $article)
                                <div class="article">
                                    <h3 class="article-title">
                                        <a href="{{ $article->url }}" target="_blank">{{ $article->title }}</a>
                                    </h3>
                                    @if ($article->summary)
                                        <p class="article-summary">{{ $article->summary }}</p>
                                    @endif
                                    <div class="article-meta">
                                        <strong>Fuente:</strong> {{ $article->feed->name ?? 'Desconocida' }}
                                        @if ($article->published_at)
                                            &bull; {{ $article->published_at->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p style="text-align: center; color: #6b7280; padding: 40px 0;">
                            No hay artículos nuevos esta semana. ¡Vuelve a revisar la próxima!
                        </p>
                    @endforelse
                </div>

                <div class="footer">
                    <p>&copy; {{ date('Y') }} RSSReader. Todos los derechos reservados.</p>
                    <p>Generado automáticamente mediante inteligencia artificial.</p>
                </div>
            </div>
        </div>
    </center>
</body>

</html>
