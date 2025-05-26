<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $sentiments = [
        'positive' => [
            'weight' => 60,
            'rating_range' => [4, 5],
            'templates' => [
                'Excellent {product} that exceeded my expectations!',
                'Really happy with this {product}, great quality',
                'Best {product} I\'ve ever purchased',
                'High quality {product}, worth every penny',
                'Fantastic product, fast delivery',
            ],
            'keywords' => [
                'excellent', 'perfect', 'love', 'great', 'awesome',
                'amazing', 'fantastic', 'wonderful', 'best', 'satisfied',
            ],
        ],
        'neutral' => [
            'weight' => 25,
            'rating_range' => [3, 4],
            'templates' => [
                'Decent {product}, meets basic needs',
                'Average quality {product}',
                'Good but could be better',
                'Not bad, but a bit pricey',
                'Okay product, nothing special',
            ],
            'keywords' => [
                'okay', 'decent', 'average', 'good', 'fine',
                'fair', 'reasonable', 'acceptable', 'moderate', 'middle',
            ],
        ],
        'negative' => [
            'weight' => 15,
            'rating_range' => [1, 2],
            'templates' => [
                'Disappointed with this {product}',
                'Not worth the price',
                'Poor quality {product}',
                'Expected better quality',
                'Would not recommend',
            ],
            'keywords' => [
                'disappointed', 'poor', 'bad', 'terrible', 'waste',
                'avoid', 'regret', 'horrible', 'awful', 'unhappy',
            ],
        ],
    ];

    protected $aspects = [
        'quality' => [
            'positive' => [
                'High quality materials',
                'Well-made and durable',
                'Premium feel and finish',
                'Built to last',
            ],
            'neutral' => [
                'Average build quality',
                'Standard materials used',
                'Decent construction',
            ],
            'negative' => [
                'Poor quality materials',
                'Feels cheaply made',
                'Not durable at all',
            ],
        ],
        'value' => [
            'positive' => [
                'Great value for money',
                'Worth every penny',
                'Reasonably priced',
            ],
            'neutral' => [
                'Price is fair',
                'Expected more for the price',
                'Somewhat expensive',
            ],
            'negative' => [
                'Overpriced for what you get',
                'Not worth the money',
                'Too expensive',
            ],
        ],
        'shipping' => [
            'positive' => [
                'Fast delivery',
                'Well packaged',
                'Arrived earlier than expected',
            ],
            'neutral' => [
                'Standard shipping time',
                'Packaging was okay',
                'Delivery as expected',
            ],
            'negative' => [
                'Slow delivery',
                'Poor packaging',
                'Shipping took forever',
            ],
        ],
        'customer_service' => [
            'positive' => [
                'Excellent customer service',
                'Very helpful support team',
                'Quick response to queries',
            ],
            'neutral' => [
                'Customer service was okay',
                'Standard support experience',
                'Had to contact twice',
            ],
            'negative' => [
                'Poor customer service',
                'No response to queries',
                'Unhelpful support team',
            ],
        ],
    ];

    public function definition(): array
    {
        // Get or create related models
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $order = $this->getRelatedOrder($user, $product);
        
        // Determine review sentiment and generate content
        $sentiment = $this->getWeightedSentiment();
        
        // Calculate rating 
        $rating = $this->calculateRating($sentiment);
        
        // Generate dates ensuring proper order
        $purchaseDate = $order?->created_at ?? fake()->dateTimeBetween('-6 months', '-1 week');
        $reviewDate = fake()->dateTimeBetween($purchaseDate, 'now');
        
        // Get admin for moderation
        $admin = Admin::inRandomOrder()->first();
        $moderationStatus = fake()->randomElement(['pending', 'approved', 'rejected']);
        
        return [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $rating,
            'comment' => $this->generateComment($sentiment, $product),
            'sentiment_score' => $this->calculateSentimentScore($sentiment, $rating),
            'helpful_count' => fake()->numberBetween(0, 50),
            'moderation_status' => $moderationStatus,
            'moderated_by' => $moderationStatus !== 'pending' ? $admin?->id : null,
            'moderated_at' => $moderationStatus !== 'pending' ? fake()->dateTimeBetween($reviewDate, 'now') : null,
            'created_at' => $reviewDate,
        ];
    }

    protected function getWeightedSentiment(): string
    {
        $total = array_sum(array_column($this->sentiments, 'weight'));
        $random = fake()->numberBetween(1, $total);
        $sum = 0;

        foreach ($this->sentiments as $sentiment => $config) {
            $sum += $config['weight'];
            if ($random <= $sum) {
                return $sentiment;
            }
        }

        return 'neutral';
    }

    protected function getRelatedOrder(User $user, Product $product): ?Order
    {
        return Order::whereHas('items', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->where('user_id', $user->id)
          ->where('status', 'delivered')
          ->inRandomOrder()
          ->first();
    }

    protected function generateTitle(string $sentiment, Product $product): string
    {
        $template = fake()->randomElement($this->sentiments[$sentiment]['templates']);
        return strtr($template, ['{product}' => $product->name]);
    }
    
    // Helper to combine title and content for the comment field
    protected function generateComment(string $sentiment, Product $product): string 
    {
        $title = $this->generateTitle($sentiment, $product);
        $content = $this->generateContent($sentiment, $product);
        return $title . "\n\n" . $content;
    }

    protected function generateContent(string $sentiment, Product $product): string
    {
        $content = [];
        
        // Add main review body
        $content[] = $this->generateMainContent($sentiment, $product);
        
        // Add aspect-specific comments
        $aspects = fake()->numberBetween(1, 3);
        $selectedAspects = fake()->randomElements(array_keys($this->aspects), $aspects);
        
        foreach ($selectedAspects as $aspect) {
            $content[] = fake()->randomElement($this->aspects[$aspect][$sentiment]);
        }
        
        // Add experience details for longer reviews
        if (fake()->boolean(40)) {
            $content[] = $this->generateExperienceDetails($sentiment);
        }
        
        // Add recommendation statement
        if (fake()->boolean(60)) {
            $content[] = $this->generateRecommendation($sentiment);
        }
        
        return implode("\n\n", $content);
    }

    protected function generateMainContent(string $sentiment, Product $product): string
    {
        $keywords = $this->sentiments[$sentiment]['keywords'];
        $content = fake()->paragraph();
        
        // Insert sentiment keywords
        if (fake()->boolean(80)) {
            $keyword = fake()->randomElement($keywords);
            $content = "$keyword " . lcfirst($content);
        }
        
        return $content;
    }

    protected function generateExperienceDetails(string $sentiment): string
    {
        $templates = [
            'positive' => [
                'I\'ve been using this for {duration} and it\'s still working perfectly.',
                'After {duration} of use, I\'m still impressed.',
                'Been using this daily for {duration} - no issues at all.',
            ],
            'neutral' => [
                'Have been using this for {duration} with mixed results.',
                'Used it for {duration} - it\'s okay but not amazing.',
                'After {duration}, it serves its purpose but nothing more.',
            ],
            'negative' => [
                'Used it for {duration} before it started having issues.',
                'After only {duration}, problems started appearing.',
                'Barely lasted {duration} before disappointing.',
            ],
        ];

        $template = fake()->randomElement($templates[$sentiment]);
        $duration = fake()->randomElement(['a week', 'two weeks', 'a month', 'three months', 'six months']);
        
        return strtr($template, ['{duration}' => $duration]);
    }

    protected function generateRecommendation(string $sentiment): string
    {
        $recommendations = [
            'positive' => [
                'Highly recommend this to anyone looking for quality.',
                'Would definitely buy this again.',
                'Great product, you won\'t regret buying it.',
            ],
            'neutral' => [
                'Might recommend if on sale.',
                'Consider other options before buying.',
                'Decent choice if you don\'t mind the downsides.',
            ],
            'negative' => [
                'Would not recommend this product.',
                'Save your money and look elsewhere.',
                'Cannot recommend this to anyone.',
            ],
        ];

        return fake()->randomElement($recommendations[$sentiment]);
    }

    protected function generatePros(string $sentiment): array
    {
        $count = match($sentiment) {
            'positive' => fake()->numberBetween(2, 5),
            'neutral' => fake()->numberBetween(1, 3),
            'negative' => fake()->numberBetween(0, 1),
        };

        $possiblePros = [
            'Good quality',
            'Value for money',
            'Fast delivery',
            'Easy to use',
            'Excellent design',
            'Durable',
            'Great customer service',
            'Nice packaging',
            'As described',
            'Quick setup',
        ];

        return $count > 0 ? fake()->randomElements($possiblePros, $count) : [];
    }

    protected function generateCons(string $sentiment): array
    {
        $count = match($sentiment) {
            'positive' => fake()->numberBetween(0, 1),
            'neutral' => fake()->numberBetween(1, 2),
            'negative' => fake()->numberBetween(2, 4),
        };

        $possibleCons = [
            'Expensive',
            'Poor quality',
            'Slow delivery',
            'Difficult to use',
            'Bad design',
            'Not durable',
            'Poor customer service',
            'Damaged packaging',
            'Not as described',
            'Complicated setup',
        ];

        return $count > 0 ? fake()->randomElements($possibleCons, $count) : [];
    }

    protected function calculateRating(string $sentiment): int
    {
        return fake()->numberBetween(
            $this->sentiments[$sentiment]['rating_range'][0],
            $this->sentiments[$sentiment]['rating_range'][1]
        );
    }

    protected function calculateSentimentScore(string $sentiment, int $rating): float
    {
        $baseScore = match($sentiment) {
            'positive' => fake()->randomFloat(2, 0.7, 1.0),
            'neutral' => fake()->randomFloat(2, 0.4, 0.6),
            'negative' => fake()->randomFloat(2, 0.0, 0.3),
        };

        // Adjust based on rating
        $ratingAdjustment = ($rating - 3) * 0.1;
        
        return min(1.0, max(0.0, $baseScore + $ratingAdjustment));
    }

    protected function calculateHelpfulVotes(string $sentiment, string $content): int
    {
        $baseVotes = match($sentiment) {
            'positive' => fake()->numberBetween(5, 50),
            'neutral' => fake()->numberBetween(2, 20),
            'negative' => fake()->numberBetween(0, 10),
        };

        // Longer, more detailed reviews tend to get more helpful votes
        $lengthMultiplier = strlen($content) > 500 ? 1.5 : 1.0;
        
        return (int)($baseVotes * $lengthMultiplier);
    }

    protected function calculateNotHelpfulVotes(string $sentiment): int
    {
        return match($sentiment) {
            'positive' => fake()->numberBetween(0, 5),
            'neutral' => fake()->numberBetween(1, 10),
            'negative' => fake()->numberBetween(2, 20),
        };
    }

    protected function determineStatus(string $sentiment): string
    {
        if ($sentiment === 'negative' && fake()->boolean(20)) {
            return fake()->randomElement(['hidden', 'flagged', 'pending_review']);
        }
        
        return 'published';
    }

    protected function generateAdminResponse(string $sentiment): ?string
    {
        if ($sentiment !== 'negative' || fake()->boolean(70)) {
            return null;
        }

        $templates = [
            'We apologize for your experience. Please contact our support team at support@example.com for immediate assistance.',
            'Thank you for your feedback. We\'re sorry to hear about these issues and would like to make it right.',
            'We appreciate your honest feedback and are looking into the concerns you\'ve raised.',
            'Sorry to hear about your experience. Our customer service team will reach out to help resolve these issues.',
        ];

        return fake()->randomElement($templates);
    }

    public function verified(): static
    {
        // This method isn't needed as 'verified_purchase' doesn't exist in the table
        // But we'll keep it for compatibility, it just won't have any effect
        return $this;
    }

    public function negative(): static
    {
        return $this->state(function (array $attributes) {
            $sentiment = 'negative';
            $title = $this->generateTitle($sentiment, Product::find($attributes['product_id']));
            $content = $this->generateContent($sentiment, Product::find($attributes['product_id']));
            
            return [
                'rating' => fake()->numberBetween(1, 2),
                'comment' => $title . "\n\n" . $content,
                'sentiment_score' => fake()->randomFloat(2, 0.0, 0.3),
                'moderation_status' => fake()->randomElement(['pending', 'rejected', 'approved']),
            ];
        });
    }

    public function positive(): static
    {
        return $this->state(function (array $attributes) {
            $sentiment = 'positive';
            $title = $this->generateTitle($sentiment, Product::find($attributes['product_id']));
            $content = $this->generateContent($sentiment, Product::find($attributes['product_id']));
            
            return [
                'rating' => fake()->numberBetween(4, 5),
                'comment' => $title . "\n\n" . $content,
                'sentiment_score' => fake()->randomFloat(2, 0.7, 1.0),
                'moderation_status' => 'approved',
            ];
        });
    }
}
