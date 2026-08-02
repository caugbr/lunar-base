<x-hook name="public.featured_posts">
    @if($featuredPosts->count())
    @php
        $title = setting('reading.featured_posts_title', '');
    @endphp
        <div class="posts-grid home-grid">
            @if($title)
                <h2 class="posts-grid-title">{{ $title }}</h2>
            @endif
            @foreach($featuredPosts as $post)
                <article class="post-card">
                    @if($post->thumbnail)
                    <div class="thumbnail">
                        <a href="{{ $post->url }}" class="card-thumbnail">
                            <img src="{{ $post->thumbnail->thumb_url }}" alt="{{ $post->title }}">
                        </a>
                    </div>
                    @endif
                    <div class="post-data">
                        <h2><a href="{{ $post->url }}">{{ $post->title }}</a></h2>
                        <p>{{ $post->excerpt }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-hook>

@if($featuredPosts->count())
<style>
/* Título da Seção de Destaques */
.posts-grid.home-grid .posts-grid-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--color-text, #1e293b);
    margin: 0 0 0.5rem 0;
}

/* Container da Grade de Destaques na Home */
.posts-grid.home-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin: 1.5rem 0;
}

/* Card Horizontal do Post */
.post-card {
    position: relative; /* Necessário para o Stretched Link funciona no card todo */
    display: flex;
    flex-direction: row;
    align-items: center;
    background-color: var(--color-bg-card, #ffffff);
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.1));
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.post-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md, 0 4px 12px rgba(0, 0, 0, 0.15));
    border-color: var(--color-primary, #3b82f6);
}

/* Dimensões da Thumbnail (Lado Esquerdo) */
.post-card .thumbnail {
    flex-shrink: 0;
    width: 180px;  /* Largura fixa da foto */
    height: 130px; /* Altura fixa da foto */
    overflow: hidden;
    background-color: var(--color-bg-dark, #f1f5f9);
}

.post-card .card-thumbnail {
    display: block;
    width: 100%;
    height: 100%;
}

.post-card .thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Recorta a foto sem esticar ou distorcer */
    transition: transform 0.3s ease;
}

/* Zoom suave na foto ao passar o mouse */
.post-card:hover .thumbnail img {
    transform: scale(1.05);
}

/* Dados do Post (Lado Direito) */
.post-card .post-data {
    flex: 1;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    text-align: left;
    min-width: 0; /* Previne quebras de layout por textos longos */
}

.post-card .post-data h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.3;
}

.post-card .post-data h2 a {
    color: var(--color-text, #1e293b);
    text-decoration: none;
    transition: color 0.2s ease;
}

/* Stretched Link: Torna o card inteiro clicável */
.post-card .post-data h2 a:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    z-index: 1;
}

.post-card .post-data h2 a:hover {
    color: var(--color-primary, #2563eb);
}

/* Resumo (Excerpt) limitado a 2 linhas com reticências */
.post-card .post-data p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--color-text-muted, #64748b);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsividade: Empilha em celulares pequenos (< 576px) */
@media (max-width: 576px) {
    .post-card {
        flex-direction: column;
        align-items: stretch;
    }

    .post-card .thumbnail {
        width: 100%;
        height: 180px;
    }
}
</style>
@endif
