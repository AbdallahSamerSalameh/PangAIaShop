@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - ' . $article->title)

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Read the Details</p>
                    <h1>{{ $article->title }}</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- single article section -->
<div class="mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="single-article-section">
                    <div class="single-article-text">
                        <div class="single-article-bg" style="background-image: url({{ asset($article->image) }})"></div>
                        <p class="blog-meta">
                            <span class="author"><i class="fas fa-user"></i> {{ $article->author }}</span>
                            <span class="date"><i class="fas fa-calendar"></i> {{ $article->created_at->format('d F, Y') }}</span>
                            <span class="category"><i class="fas fa-tags"></i> {{ $article->category }}</span>
                        </p>
                        <h2>{{ $article->title }}</h2>
                        <div class="single-article-content">
                            {!! $article->content !!}
                        </div>
                    </div>

                    <div class="comments-list-wrap">
                        <h3 class="comment-count-title">{{ count($article->comments) }} Comments</h3>
                        <div class="comment-list">
                            @foreach($article->comments as $comment)
                            <div class="single-comment-body">
                                <div class="comment-user-avater">
                                    <img src="{{ asset('assets/img/avaters/avatar1.png') }}" alt="">
                                </div>
                                <div class="comment-text-body">
                                    <h4>{{ $comment->name }} <span class="comment-date">{{ $comment->created_at->format('d F, Y') }}</span></h4>
                                    <p>{{ $comment->content }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="comment-template">
                        <h4>Leave a comment</h4>
                        <p>If you have a comment, don't hesitate to send us.</p>
                        <form action="{{ route('news.comment', $article->id) }}" method="POST">
                            @csrf
                            <p>
                                <input type="text" placeholder="Your Name" name="name" required>
                                <input type="email" placeholder="Your Email" name="email" required>
                            </p>
                            <p>
                                <textarea name="content" id="comment" cols="30" rows="10" placeholder="Your Message" required></textarea>
                            </p>
                            <p><input type="submit" value="Submit"></p>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sidebar-section">
                    <div class="recent-posts">
                        <h4>Recent Posts</h4>
                        <ul>
                            @foreach($recentArticles as $recentArticle)
                            <li><a href="{{ route('news.show', $recentArticle->id) }}">{{ $recentArticle->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="archive-posts">
                        <h4>Archive Posts</h4>
                        <ul>
                            @foreach($archiveMonths as $month)
                            <li><a href="{{ route('news', ['month' => $month->format('Y-m')]) }}">{{ $month->format('F Y') }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tag-section">
                        <h4>Tags</h4>
                        <ul>
                            @foreach($tags as $tag)
                            <li><a href="{{ route('news', ['tag' => $tag]) }}">{{ $tag }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end single article section -->
@endsection