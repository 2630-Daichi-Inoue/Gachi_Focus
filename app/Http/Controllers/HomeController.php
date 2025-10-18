<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    private $space;
    private $user;


    public function __construct(Space $space)
    {
        $this->space = $space;
    }

    public function index()
    {
        $q = Space::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');


        // Default: rating high → low
        $this->applySort($q, 'rating_high_to_low');


        $home_spaces = $q->paginate(6);
        return view('users.home', compact('home_spaces'));
    }


    public function search(Request $request)
    {
        $request->validate([
            'name'     => 'nullable|string|max:50',
            'location' => 'nullable|string|max:50',
            'max_fee'  => ['nullable','numeric','min:0'],
            'capacity' => ['nullable','integer','min:1'],
            'sort'     => 'nullable|in:rating_high_to_low,price_high_to_low,price_low_to_high,capacity_high_to_low,capacity_low_to_high,newest',
        ]);


        $maxFee = $request->filled('max_fee')  ? max(0, (float)$request->max_fee) : null;
        $cap    = $request->filled('capacity') ? max(1, (int)$request->capacity) : null;


        $q = Space::query()
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
        if ($maxFee !== null) {
            $q->where('price','<=',$maxFee);
        }
        if ($cap !== null) {
            $q->where('min_capacity','<=',$cap)
            ->where('max_capacity','>=',$cap);
        }


        // Apply unified, multi-key sorting
        $this->applySort($q, $request->sort);


        $home_spaces = $q->paginate(6)->appends($request->query());
        return view('users.home', compact('home_spaces'));
    }


    private function applySort(\Illuminate\Database\Eloquent\Builder $q, ?string $sort): void
        {
            switch ($sort ?? 'rating_high_to_low') {
                // Rating: High → Low
                case 'rating_high_to_low':
                    $q->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')  // 1) rating
                    ->orderBy('reviews_count','desc')                    // 2) review count
                    ->latest('id');                                      // 3) newest
                    break;


                // Price
                case 'price_high_to_low':
                    $q->orderBy('price','desc')                            // 1) price
                    ->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')  // 2) rating
                    ->orderBy('reviews_count','desc')                    // 3) review count
                    ->latest('id');                                      // 4) newest
                    break;


                case 'price_low_to_high':
                    $q->orderBy('price','asc')
                    ->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')
                    ->orderBy('reviews_count','desc')
                    ->latest('id');
                    break;


                // Capacity
                case 'capacity_high_to_low':
                    $q->orderBy('max_capacity','desc')
                    ->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')
                    ->orderBy('reviews_count','desc')
                    ->latest('id');
                    break;


                case 'capacity_low_to_high':
                    $q->orderBy('max_capacity','asc')
                    ->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')
                    ->orderBy('reviews_count','desc')
                    ->latest('id');
                    break;


                // Newest first (id desc). id はユニークなので以降の orderBy は実質不要。
                case 'newest':
                    $q->latest('id');
                    break;


                default:
                    // Fallback = rating
                    $q->orderByRaw('COALESCE(reviews_avg_rating,0) DESC')
                    ->orderBy('reviews_count','desc')
                    ->latest('id');
            }
        }
}