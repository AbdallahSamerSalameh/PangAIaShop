<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Display a listing of the news/blog articles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check if the Article model/table exists
        try {
            $articles = Article::where('status', 'published')
                              ->orderBy('created_at', 'desc')
                              ->paginate(9);
        } catch (\Exception $e) {
            // If Article model/table doesn't exist yet, use dummy data
            $articles = collect([
                (object)[
                    'id' => 1,
                    'title' => 'The Benefits of Organic Products',
                    'excerpt' => 'Discover why organic products are better for your health and the environment.',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, urna eu tincidunt consectetur, nisl nunc euismod nisi, eu porttitor nisl nisl euismod nisi.',
                    'image' => 'assets/img/latest-news/news-bg-1.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(5),
                    'category' => 'Health',
                    'comments_count' => 5
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Seasonal Fruits and Their Benefits',
                    'excerpt' => 'Learn about seasonal fruits and how they can boost your immunity.',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, urna eu tincidunt consectetur, nisl nunc euismod nisi, eu porttitor nisl nisl euismod nisi.',
                    'image' => 'assets/img/latest-news/news-bg-2.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(10),
                    'category' => 'Nutrition',
                    'comments_count' => 3
                ],
                (object)[
                    'id' => 3,
                    'title' => 'How to Store Fresh Vegetables',
                    'excerpt' => 'Tips and tricks to keep your vegetables fresh for longer periods.',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, urna eu tincidunt consectetur, nisl nunc euismod nisi, eu porttitor nisl nisl euismod nisi.',
                    'image' => 'assets/img/latest-news/news-bg-3.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(15),
                    'category' => 'Tips',
                    'comments_count' => 2
                ]
            ]);
            
            // Create a paginator from the collection
            $articles = new \Illuminate\Pagination\LengthAwarePaginator(
                $articles,
                $articles->count(),
                9,
                1,
                ['path' => request()->url()]
            );
        }
        
        return view('frontend.pages.news', [
            'articles' => $articles
        ]);
    }
    
    /**
     * Display the specified article.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Check if the Article model/table exists
        try {
            $article = Article::with('comments')->findOrFail($id);
            
            // Get related articles
            $relatedArticles = Article::where('id', '!=', $id)
                                     ->where('status', 'published')
                                     ->take(3)
                                     ->get();
        } catch (\Exception $e) {
            // If Article model/table doesn't exist yet, use dummy data
            $article = (object)[
                'id' => $id,
                'title' => 'Sample Article',
                'content' => 'This is a sample article with detailed content. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, urna eu tincidunt consectetur, nisl nunc euismod nisi, eu porttitor nisl nisl euismod nisi.',
                'image' => 'assets/img/latest-news/news-bg-' . min((int)$id, 3) . '.jpg',
                'author' => 'Admin',
                'created_at' => now()->subDays(rand(1, 20)),
                'category' => ['Health', 'Nutrition', 'Tips'][rand(0, 2)],
                'comments' => collect([
                    (object)[
                        'id' => 1,
                        'author' => 'John Doe',
                        'content' => 'Great article! Very informative.',
                        'created_at' => now()->subDays(2),
                    ],
                    (object)[
                        'id' => 2,
                        'author' => 'Jane Smith',
                        'content' => 'I learned a lot from this. Thanks for sharing!',
                        'created_at' => now()->subDays(1),
                    ]
                ])
            ];
            
            // Generate related articles
            $relatedArticles = collect([
                (object)[
                    'id' => $id == 1 ? 2 : 1,
                    'title' => 'The Benefits of Organic Products',
                    'excerpt' => 'Discover why organic products are better for your health and the environment.',
                    'image' => 'assets/img/latest-news/news-bg-1.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(5),
                    'category' => 'Health'
                ],
                (object)[
                    'id' => $id == 2 ? 3 : 2,
                    'title' => 'Seasonal Fruits and Their Benefits',
                    'excerpt' => 'Learn about seasonal fruits and how they can boost your immunity.',
                    'image' => 'assets/img/latest-news/news-bg-2.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(10),
                    'category' => 'Nutrition'
                ],
                (object)[
                    'id' => $id == 3 ? 1 : 3,
                    'title' => 'How to Store Fresh Vegetables',
                    'excerpt' => 'Tips and tricks to keep your vegetables fresh for longer periods.',
                    'image' => 'assets/img/latest-news/news-bg-3.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(15),
                    'category' => 'Tips'
                ]
            ]);
            
            // Filter out the current article from related articles
            $relatedArticles = $relatedArticles->filter(function($article) use ($id) {
                return $article->id != $id;
            });
        }
        
        return view('frontend.pages.single-news', [
            'article' => $article,
            'relatedArticles' => $relatedArticles
        ]);
    }
    
    /**
     * Add a comment to an article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);
        
        try {
            $article = Article::findOrFail($id);
            
            Comment::create([
                'article_id' => $id,
                'name' => $request->name,
                'email' => $request->email,
                'content' => $request->message,
                'status' => 'pending',  // Pending approval
                'created_at' => now(),
            ]);
            
            return redirect()->back()->with('success', 'Your comment has been submitted and is awaiting approval.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to submit comment. Please try again later.');
        }
    }
}