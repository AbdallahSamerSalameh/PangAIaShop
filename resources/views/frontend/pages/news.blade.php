@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - News')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Organic Information</p>
                    <h1>News Articles</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- latest news -->
<div class="latest-news mt-150 mb-150">
    <div class="container">
        <div class="row">
            @foreach($news as $article)
            <div class="col-lg-4 col-md-6">
                <div class="single-latest-news">
                    <a href="{{ route('news.show', $article->id) }}">
                        <div class="latest-news-bg" style="background-image: url({{ asset($article->image) }})"></div>
                    </a>
                    <div class="news-text-box">
                        <h3><a href="{{ route('news.show', $article->id) }}">{{ $article->title }}</a></h3>
                        <p class="blog-meta">
                            <span class="author"><i class="fas fa-user"></i> {{ $article->author }}</span>
                            <span class="date"><i class="fas fa-calendar"></i> {{ $article->created_at->format('d F, Y') }}</span>
                        </p>
                        <p class="excerpt">{{ Str::limit($article->excerpt, 100) }}</p>
                        <a href="{{ route('news.show', $article->id) }}" class="read-more-btn">read more <i class="fas fa-angle-right"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="pagination-wrap">
                            {{ $news->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end latest news -->
@endsection