<?php


namespace App\Http\Controllers;


use App\Models\Space;
use App\Models\Amenity;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SpaceController extends Controller
{
    private $space;
    private $user;

    public function __construct(Space $space)
    {
        $this->space = $space;
    }

    public function index(Request $request)
    {
        $query = Space::query()
                        ->where('is_public', true)
                        ->with('amenities')
                        ->withCount(
                            ['reviews as public_reviews_count' => function ($q) {
                                $q->where('is_public', true);
                            },
                        ])
                        ->withAvg(
                            ['reviews as public_reviews_avg_rating' => function ($q) {
                                $q->where('is_public', true);
                            },
                        ], 'rating');

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }
        // Filter by prefecture
        if ($request->filled('prefecture')) {
            $query->where('prefecture', $request->prefecture);
        }
        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }
        // Filter by max_price
        if ($request->filled('max_price')) {
            $query->where('weekday_price_yen', '<=', $request->max_price);
        }

        // Default: rating high → low
        $this->applySort($query, $request->input('sort', 'rating_high_to_low'));

        $spaces = $query->paginate(6)->withQueryString();

        $prefectures = Space::where('is_public', true)
                            ->select('prefecture')
                            ->distinct()
                            ->orderBy('prefecture')
                            ->pluck('prefecture');

        return Inertia::render('Spaces/Index', [
            'spaces' => $spaces,
            'prefectures' => $prefectures,
            'filters' => [
                'name' => $request->name,
                'prefecture' => $request->prefecture,
                'city' => $request->city,
                'max_price' => $request->max_price,
                'sort' => $request->input('sort', 'rating_high_to_low'),
            ]
        ]);
    }

    private function applySort(\Illuminate\Database\Eloquent\Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'rating_high_to_low') {
            case 'rating_high_to_low':
                $q->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
                break;

            case 'price_low_to_high':
                $q->orderBy('weekday_price_yen', 'asc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
                break;

            case 'price_high_to_low':
                $q->orderBy('weekday_price_yen', 'desc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
                break;

            case 'capacity_high_to_low':
                $q->orderBy('capacity', 'desc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
                break;

            case 'capacity_low_to_high':
                $q->orderBy('capacity', 'asc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
                break;

            case 'newest':
                $q->latest('id');
                break;

            default:
                $q->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('id');
        }
    }

    public function show(Space $space)
    {

        if (!$space->is_public) {
            return redirect()->route('spaces.index')
                ->with('error', 'Sorry, but ' . $space->name . ' is not currently available.');
        }

        $space->load('amenities');

        $reviews = $space->reviews()
                            ->where('is_public', true)
                            ->with('user')
                            ->latest()
                            ->get();

        $reviewCount = $reviews->count();

        $avg = $reviews->avg('rating');
        $averageRating = is_null($avg) ? null : round($avg, 1);

        return Inertia::render('Spaces/Show', [
            'space' => $space,
            'reviewInfo' => [
                'reviews' => $reviews,
                'reviewCount' => $reviewCount,
                'averageRating' => $averageRating,
            ]
        ]);
    }
}
