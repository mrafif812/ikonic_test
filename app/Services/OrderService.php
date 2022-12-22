<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        if (!is_null($data['order_id'])) {
            $order = Order::find($data['order_id']);
            // checking for duplicate orders
            if ($order) {
                return;
            } else {
                $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
                $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
                $affiliate = Affiliate::where('discount_code', $data['discount_code'])->first();

                Order::create([
                    'merchant_id' => $merchant->id,
                    'affiliate_id' => $affiliate->id,
                    'subtotal' => $data['subtotal_price'],
                    'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
                    'payout_status' => Order::STATUS_UNPAID,
                    'external_order_id' => $data['order_id'],
                    'discount_code' => $data['discount_code'],
                ]);
            }
        } else {
            return;
        }
    }
}
