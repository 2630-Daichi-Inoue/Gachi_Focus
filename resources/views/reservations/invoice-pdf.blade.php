<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $reservation->id }}</title>
</head>

<body
    style="font-family: Helvetica, Arial, sans-serif; color:#333; margin:40px; font-size:13px; min-height:1000px; display:flex; flex-direction:column; justify-content:space-between;">

    {{-- ====== MAIN CONTENT WRAPPER ====== --}}
    <div style="flex:1;">

        {{-- Header --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px;">
            <div style="flex:1; text-align:left;">
                <img src="{{ public_path('images/GachiFocus_logo.png') }}" alt="Gachi Focus Logo"
                    style="width:120px; height:auto;">
            </div>
            <div style="flex:2; text-align:right;">
                <h4 style="margin:0; font-size:24px; color:#111;">Invoice</h4>
                <p style="margin:4px 0 0 0; color:#555; font-size:12px;">Issued on
                    {{ $issuedDate ?? \Carbon\Carbon::now('Asia/Tokyo')->format('F d, Y') }}</p>
            </div>
        </div>

        <hr style="border:none; border-top:1px solid #ccc; margin:15px 0 25px 0;">

        {{-- Customer Info --}}
        <div style="margin-bottom:25px;">
            <p style="margin:3px 0;"><strong style="display:inline-block; width:120px;">Reservation ID:</strong>
                {{ $reservation->id }}</p>
            <p style="margin:3px 0;"><strong style="display:inline-block; width:120px;">Customer:</strong>
                {{ $user->name }}</p>
            <p style="margin:3px 0;"><strong style="display:inline-block; width:120px;">Email:</strong>
                {{ $user->email }}</p>
        </div>

        {{-- Reservation Details --}}
        <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:13px;">
            <thead>
                <tr>
                    <th style="padding:10px; border:1px solid #ddd; background-color:#f8f8f8; text-align:left;">
                        Workspace</th>
                    <th style="padding:10px; border:1px solid #ddd; background-color:#f8f8f8; text-align:left;">Date
                    </th>
                    <th style="padding:10px; border:1px solid #ddd; background-color:#f8f8f8; text-align:left;">Time
                    </th>
                    <th style="padding:10px; border:1px solid #ddd; background-color:#f8f8f8; text-align:right;">Price
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:10px; border:1px solid #ddd;">
                        {{ $reservation->space->name ?? 'Gachi Focus Workspace' }}
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        {{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        {{ $reservation->start_time }} - {{ $reservation->end_time }}
                    </td>
                    <td style="padding:10px; border:1px solid #ddd; text-align:right;">
                        ${{ number_format($reservation->price, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Totals --}}
        @php
            $localSubtotal = $subtotalUSD * $exchangeRate;
            $localTax = $taxUSD * $exchangeRate;
            $localTotal = $totalUSD * $exchangeRate;
        @endphp

        <div style="margin-top:25px; width:300px; margin-left:auto;">
            <table style="width:100%; border:none; font-size:13px;">
                <tr>
                    <td style="text-align:left; color:#666; padding:6px 0;">Subtotal:</td>
                    <td style="text-align:right; font-weight:600; padding:6px 0;">
                        ${{ number_format($subtotalUSD, 2) }}
                        <span style="color:#777; font-size:11px;">
                            ({{ number_format($localSubtotal, 0) }} {{ $localCurrency }})
                        </span>
                    </td>
                </tr>

                <tr>
                    <td style="text-align:left; color:#666; padding:6px 0;">
                        Tax ({{ $vatRate }}%)
                        @if(isset($taxMethod) && $taxMethod === 'internal')
                            <span style="color:#999;">(included)</span>
                        @else
                            <span style="color:#999;">(added)</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-weight:600; padding:6px 0;">
                        ${{ number_format($taxUSD, 2) }}
                        <span style="color:#777; font-size:11px;">
                            ({{ number_format($localTax, 0) }} {{ $localCurrency }})
                        </span>
                    </td>
                </tr>

                <tr>
                    <td style="text-align:left; color:#111; font-weight:bold; padding:6px 0;">Total:</td>
                    <td style="text-align:right; font-weight:bold; padding:6px 0;">
                        ${{ number_format($totalUSD, 2) }}
                        <span style="color:#666; font-size:11px;">
                            ({{ number_format($localTotal, 0) }} {{ $localCurrency }})
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Tax location info --}}
        <p style="margin-top:20px; font-size:12px; color:#666; text-align:right;">
            Tax applied based on:
            {{ $space->city ?? '' }}
            @if(!empty($space->state))
                 {{ $space->state }}
            @endif
             {{ $space->country_code }}
        </p>
    </div>

    {{-- FOOTER --}}
    <div
        style="position:absolute; bottom:40px; left:0; right:0; text-align:center; font-size:11px; color:#777; border-top:1px solid #eee; padding-top:10px;">
        <p style="margin:2px 0;">{{ $company['name'] ?? 'Gachi Focus Inc.' }}</p>
        <p style="margin:2px 0;">{{ $company['address'] ?? 'Shibuya-ku, Tokyo, Japan' }}</p>
        <p style="margin:2px 0;">{{ $company['email'] ?? 'support@gachifocus.com' }}</p>
        <p style="margin:2px 0;">{{ $company['signature'] ?? 'Thank you for using Gachi Focus.' }}</p>
    </div>

</body>

</html>
