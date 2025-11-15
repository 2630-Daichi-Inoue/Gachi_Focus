<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| このファイルのルートはすべて "api" ミドルウェアグループに属します。
| そのため、CSRF 保護は適用されません。
| Stripe Webhook のような外部サービスからの POST を受けるのに最適です。
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook (CSRF なし)
|--------------------------------------------------------------------------
| Webhook は CSRF チェックが不要なので、web.php ではなく
| API グループで受けるほうが安全で確実です。
|
| 実際に Stripe がアクセスするパスは：
|   /api/stripe/webhook
| となります。
|--------------------------------------------------------------------------
*/
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
    ->name('api.stripe.webhook');