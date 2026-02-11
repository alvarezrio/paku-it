<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        @if($article->hasMedia('featured'))
            <img class="w-full h-64 object-cover" src="{{ $article->getFirstMediaUrl('featured') }}" alt="{{ $article->title }}">
        @endif
        
        <div class="p-8">
            <div class="flex items-center justify-between mb-4">
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    {{ $article->category->name ?? 'Tanpa Kategori' }}
                </span>
                <div class="text-sm text-gray-500 flex gap-4">
                    <span>📅 {{ $article->published_at->format('d M Y') }}</span>
                    <!-- Menampilkan Counter View -->
                    <span class="font-bold text-blue-600">👁️ {{ $article->views }} x Dilihat</span>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $article->title }}</h1>
            <p class="text-gray-600 mb-6 text-sm">Ditulis oleh: {{ $article->author_name }}</p>

            <div class="prose max-w-none text-gray-800 leading-relaxed">
                {!! $article->content !!}
            </div>
        </div>
    </div>
</body>
</html>
