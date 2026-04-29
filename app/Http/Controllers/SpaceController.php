<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Space;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SpaceController extends Controller
{

    public function index(Request $request)
    {
        $prefectureList = array_merge(
            config('constants.prefectures.Major Prefectures'),
            config('constants.prefectures.Other Prefectures')
        );

        $request->validate([
            'name' => ['nullable','string','max:50'],
            'prefecture' => ['nullable', Rule::in(array_merge(['all'], $prefectureList))],
            'city' => ['nullable','string','max:50'],
            'rows_per_page' => ['nullable', 'integer', 'in:1,2,3,4,5']
        ]);

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
        if ($request->filled('prefecture') && $request->prefecture !== 'all') {
            $query->where('prefecture', $request->prefecture);
        }
        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }
        // Filter by max_price
        if ($request->filled('max_price')) {
            $query->where('weekend_price_yen', '<=', $request->max_price);
        }

        // Default: rating high → low
        $this->applySort($query, $request->input('sort', 'favorite_first'));

        $spaces = $query
                    ->paginate($request->input('rows_per_page', 3) * 3)
                    ->withQueryString();

        $prefectures = Space::where('is_public', true)
                            ->select('prefecture')
                            ->distinct()
                            ->orderBy('prefecture')
                            ->pluck('prefecture');

        $favoriteSpaceIds = Favorite::where('user_id', Auth::id())
                                    ->pluck('space_id');

        return Inertia::render('Spaces/Index', [
            'spaces' => $spaces,
            'favoriteSpaceIds' => $favoriteSpaceIds,
            'prefectures' => $prefectures,
            'filters' => [
                'name'          => $request->name,
                'prefecture'    => $request->prefecture,
                'city'          => $request->city,
                'max_price'     => $request->max_price,
                'sort'          => $request->input('sort', 'favorite_first'),
                'rows_per_page' => (int) $request->input('rows_per_page', 3),
            ]
        ]);
    }

    private function applySort(Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'rating_high_to_low') {
            case 'rating_high_to_low':
                $q->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            case 'price_low_to_high':
                $q->orderBy('weekday_price_yen', 'asc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            case 'price_high_to_low':
                $q->orderBy('weekday_price_yen', 'desc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            case 'capacity_high_to_low':
                $q->orderBy('capacity', 'desc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            case 'capacity_low_to_high':
                $q->orderBy('capacity', 'asc')
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            case 'newest':
                $q->latest('created_at');
                break;

            case 'favorite_first':
                $q->orderByRaw("CASE WHEN spaces.id IN (SELECT space_id FROM favorites WHERE user_id = ?) THEN 0 ELSE 1 END", [Auth::id()])
                    ->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
                break;

            default:
                $q->orderByRaw('COALESCE(public_reviews_avg_rating,0) DESC')
                    ->orderBy('public_reviews_count', 'desc')
                    ->latest('created_at');
        }
    }

    public function show(Space $space)
    {
        if (!$space->is_public) {
            return redirect()->route('spaces.index')
                            ->with('error', 'Sorry, but ' . $space->name . ' is not currently available.');
        }

        $space->load('amenities');

        $isFavorite = $space->isFavorite();

        $reviews = $space->reviews()
                            ->where('is_public', true)
                            ->with(['user' => fn($q) => $q->withTrashed()])
                            ->latest()
                            ->get();

        $reviewCount = $reviews->count();

        $avg = $reviews->avg('rating');
        $averageRating = is_null($avg) ? null : round($avg, 1);

        return Inertia::render('Spaces/Show', [
            'space' => $space,
            'isFavorite' => $isFavorite,
            'reviewInfo' => [
                'reviews'       => $reviews,
                'reviewCount'   => $reviewCount,
                'averageRating' => $averageRating,
            ]
        ]);
    }

    public function reviewIndex(Space $space, Request $request)
    {

        if (!$space->is_public) {
            return redirect()->route('spaces.index')
                            ->with('error', 'Sorry, but ' . $space->name . ' is not currently available.');
        }

        $sortList = [
            'rating_high_to_low',
            'rating_low_to_high',
            'newest',
        ];

        $request->validate([
            'stars' => ['nullable', 'in:all,1,2,3,4,5'],
            'sort' => ['nullable', Rule::in($sortList)],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $baseQuery = $space->reviews()
                            ->where('is_public', true)
                            ->with(['user' => fn($q) => $q->withTrashed()]);

        $allReviews = (clone $baseQuery)->get();

        $filteredQuery = clone $baseQuery;

        if ($request->filled('stars') && $request->stars !== 'all') {
            $filteredQuery->where('rating', $request->stars);
        }

        $this->applyReviewSort($filteredQuery, $request->input('sort', 'rating_high_to_low'));

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $filteredReviews = $filteredQuery
                            ->paginate($rowsPerPage)
                            ->withQueryString();

        $reviewCount = $allReviews->count();
        $avg = $allReviews->avg('rating');
        $averageRating = is_null($avg) ? null : round($avg, 1);

        return Inertia::render('Spaces/ReviewIndex', [
            'space' => $space,
            'reviewInfo' => [
                'filteredReviews' => $filteredReviews,
                'reviewCount'     => $reviewCount,
                'averageRating'   => $averageRating,
            ],
            'filters' => [
                'stars' => $request->input('stars', 'all'),
                'sort' => $request->input('sort', 'rating_high_to_low'),
                'rows_per_page' => $rowsPerPage,
            ]
        ]);
    }

    public function applyReviewSort($q, ?string $sort): void
    {
        switch ($sort ?? 'rating_high_to_low') {
            case 'rating_high_to_low':
                $q->orderBy('rating', 'desc')
                    ->latest('created_at');
                break;

            case 'rating_low_to_high':
                $q->orderBy('rating', 'asc')
                    ->latest('created_at');
                break;

            case 'newest':
                $q->latest('created_at');
                break;

            default:
                $q->orderBy('rating', 'desc')
                    ->latest('created_at');
        }
    }
}
