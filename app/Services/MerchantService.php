<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        // creating user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type' => User::TYPE_MERCHANT,
            'password' => $data['api_key']
        ]);

        // creating merchant
        $merchant = new Merchant;
        $merchant->domain = $data['domain'];
        $merchant->display_name = $data['name'];
        $user->merchant()->save($merchant);
        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        // Updating merchant
        User::find($user->id)->merchant()
            ->update([
                'domain' => $data['domain'],
                'display_name' => $data['name']
            ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        // checking if email exist
        if (!is_null($email)) {
            $user = User::where('email', $email)->first();
            // if user is found getting merchant data
            if ($user) {
                return $user->merchant;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        // Getting affiliate order aginst unpaid status
        $orders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        // dispatching payout order job
        foreach ($orders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }
}
