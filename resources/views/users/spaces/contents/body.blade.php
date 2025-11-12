<div class="card-body border-start border-end border-dark">
    {{-- Image + quick facts --}}
    <div class="row mb-2">
        <div class="col-6">
            <img src="{{ $space->image }}" alt="space {{ $space->id }}"
                 class="w-100" style="height:100px; object-fit:cover;">
        </div>

        <div class="col-6">
            <p class="mb-1">{{ $space->location_for_overview }}</p>

            @php
                // --- Currency resolution ---
                $country  = strtoupper($space->country_code ?? '');
                $currency = strtoupper($space->currency ?? '');

                // Country -> Currency map (extend as needed)
                $countryToCurrency = [
                    // Asia
                    'JP'=>'JPY','CN'=>'CNY','HK'=>'HKD','TW'=>'TWD','KR'=>'KRW','TH'=>'THB','PH'=>'PHP','VN'=>'VND','MY'=>'MYR','SG'=>'SGD','ID'=>'IDR','IN'=>'INR',
                    // Europe (EUR block)
                    'FR'=>'EUR','DE'=>'EUR','ES'=>'EUR','IT'=>'EUR','NL'=>'EUR','BE'=>'EUR','IE'=>'EUR','PT'=>'EUR','FI'=>'EUR','AT'=>'EUR','GR'=>'EUR','EE'=>'EUR','LV'=>'EUR','LT'=>'EUR','SK'=>'EUR','SI'=>'EUR','LU'=>'EUR','MT'=>'EUR','CY'=>'EUR',
                    // Non-EUR Europe
                    'GB'=>'GBP','CH'=>'CHF','SE'=>'SEK','NO'=>'NOK','DK'=>'DKK','PL'=>'PLN','CZ'=>'CZK','HU'=>'HUF',
                    // Americas / Oceania / MEA
                    'US'=>'USD','CA'=>'CAD','MX'=>'MXN','BR'=>'BRL','AU'=>'AUD','NZ'=>'NZD','AE'=>'AED','SA'=>'SAR','ZA'=>'ZAR','TR'=>'TRY','IL'=>'ILS'
                ];

                if ($currency === '' && $country !== '' && isset($countryToCurrency[$country])) {
                    $currency = $countryToCurrency[$country];
                }
                if ($currency === '') {
                    $currency = 'USD'; // final fallback
                }

                // Symbol map
                $symbols = [
                    'JPY'=>'¥','USD'=>'$','EUR'=>'€','GBP'=>'£','CHF'=>'CHF','CNY'=>'¥','HKD'=>'HK$','TWD'=>'NT$','KRW'=>'₩',
                    'THB'=>'฿','PHP'=>'₱','VND'=>'₫','MYR'=>'RM','SGD'=>'S$','IDR'=>'Rp','INR'=>'₹',
                    'SEK'=>'kr','NOK'=>'kr','DKK'=>'kr','PLN'=>'zł','CZK'=>'Kč','HUF'=>'Ft',
                    'CAD'=>'C$','AUD'=>'A$','NZD'=>'NZ$','MXN'=>'MX$','BRL'=>'R$','ZAR'=>'R','AED'=>'AED','SAR'=>'SAR','TRY'=>'₺','ILS'=>'₪'
                ];
                $symbol = $symbols[$currency] ?? ($currency . ' ');

                // Zero-decimal currencies (Stripe reference)
                $zeroDecimals = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
                $decimals = in_array($currency, $zeroDecimals, true) ? 0 : 2;

                // Price source: min of weekday/weekend (fallback safe)
                $wday = is_numeric($space->weekday_price ?? null) ? $space->weekday_price : null;
                $wend = is_numeric($space->weekend_price ?? null) ? $space->weekend_price : null;
                $candidates = array_filter([$wday, $wend], fn($v) => $v !== null);
                $min_price = count($candidates) ? min($candidates) : (is_numeric($space->price_per_hour ?? null) ? $space->price_per_hour : 0);
            @endphp

            <p class="mb-1">
                Fee / h:
                {{ $symbol }}{{ number_format($min_price, $decimals) }}〜
            </p>

            <p class="mb-1">
                Capacity: {{ $space->min_capacity }} ~ {{ $space->max_capacity }}
            </p>

            <p class="mb-1">
                Rating: ★{{ $space->reviews_avg_rating ? number_format($space->reviews_avg_rating, 1) : '-' }}
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <a href="{{ route('space.detail', ['id' => $space->id]) }}"
               class="w-100 fw-bold text-dark border border-dark rounded d-flex align-items-center justify-content-center"
               style="background-color:#ffffff; text-decoration:none; height:100%;">
                Check details
            </a>
        </div>

        <div class="col-6">
            @if(auth()->check() && auth()->user()->role_id === 1)
                <a href="{{ route('admin.space.edit', ['id' => $space->id]) }}" 
                    class="w-100 fw-bold text-white border border-dark rounded d-inline-block text-center"
                    style="background-color:#757B9D; height:100%; line-height:45px; text-decoration:none;">
                    Edit Space
                </a>
            @else
                <a href="{{ route('rooms.reserve.form', $space) }}"
                class="w-100 fw-bold text-white border border-dark rounded d-inline-block text-center"
                style="background-color:#757B9D; height:100%; line-height:45px; text-decoration:none;">
                    Book now!
                </a>
            @endif
        </div>
    </div>
</div>
