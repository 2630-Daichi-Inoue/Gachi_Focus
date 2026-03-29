<?php


namespace App\Http\Controllers;


use App\Models\Space;
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

        // Sort
        if ($request->filled('sort')) {
            $this->applySort($query, $request->sort);
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

    public function search(Request $request)
    {
        $request->validate([
            'name'     => 'nullable|string|max:50',
            'location' => 'nullable|string|max:50',
            'max_fee'  => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'sort'     => 'nullable|in:rating_high_to_low,price_high_to_low,price_low_to_high,capacity_high_to_low,capacity_low_to_high,newest',
        ]);

        $maxFee = $request->filled('max_fee')  ? max(0, (float)$request->max_fee) : null;
        $cap    = $request->filled('capacity') ? max(1, (int)$request->capacity) : null;

        // $q = Space::query()
        //     // ★ 最安/最高の計算列を付与（NULL安全）
        //     ->select('*')
        //     ->selectRaw('LEAST(COALESCE(weekday_price, 99999999), COALESCE(weekend_price, 99999999)) AS price_min')
        //     ->selectRaw('GREATEST(COALESCE(weekday_price, 0), COALESCE(weekend_price, 0)) AS price_max')
        //     ->withAvg('reviews', 'rating')
        //     ->withCount('reviews');

        $q = Space::query()
            ->select('*')
            ->selectRaw('LEAST(COALESCE(weekday_price, 99999999), COALESCE(weekend_price, 99999999)) AS price_min')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($kw = trim($request->name ?? '')) {
            $q->where('name', 'like', "%{$kw}%");
        }
        if ($loc = trim($request->location ?? '')) {
            $q->where(function ($qq) use ($loc) {
                $qq->where('location_for_overview', 'like', "%{$loc}%")
                    ->orWhere('location_for_details', 'like', "%{$loc}%");
            });
        }

        // ★ Max Fee は「最安が上限以下」で判定
        if ($maxFee !== null) {
            $q->whereRaw('LEAST(COALESCE(weekday_price, 99999999), COALESCE(weekend_price, 99999999)) <= ?', [$maxFee]);
        }

        if ($cap !== null) {
            $q->where('min_capacity', '<=', $cap)
                ->where('max_capacity', '>=', $cap);
        }

        $this->applySort($q, $request->sort);

        $home_spaces = $q->paginate(6)->appends($request->query());

        foreach ($home_spaces as $space) {
            $reviews = \App\Models\Review::where('space_id', $space->id)->get();

            $averageRating = round($reviews->avg(function ($r) {
                return ($r->cleanliness + $r->conditions + $r->facilities) / 3;
            }) ?? 0, 1);

            $space->rating = $averageRating;
        }

        return view('users.home', compact('home_spaces'));
    }

}
