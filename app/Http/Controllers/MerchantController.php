<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $ownedCommission = 0;
        $profit = 0;

        // Orders between date range
        $orders = Order::whereBetween('created_at', [$request->from, $request->to])->get();

        // order witout affiliate to get commission owned
        // bcz commissions_owed = $ownedCommission - $orderWithOutAffiliate->commission_owed,
        $orderWithOutAffiliate = Order::where('affiliate_id', null)->first();

        // calculating commission and profit
        foreach ($orders as $order) {
            $ownedCommission += $order->commission_owed;
            $profit += $order->subtotal;
        }

        return response()->json([
            'count' => count($orders),
            'commissions_owed' => $ownedCommission - $orderWithOutAffiliate->commission_owed,
            'revenue' => $profit
        ]);
    }
}
