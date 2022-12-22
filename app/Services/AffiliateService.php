<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // checking if email exist
        if (!is_null($email)) {
            // if user already found
            $userFound = User::where('email', $email)->first();
            if ($userFound !== null && $userFound->type == User::TYPE_MERCHANT) {
                throw new AffiliateCreateException();
            }
            if ($userFound !== null && $userFound->type == User::TYPE_AFFILIATE) {
                throw new AffiliateCreateException();
            }
        } else {
            throw new AffiliateCreateException();
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE
        ]);

        // setting discount
        $discountCode = $this->apiService->createDiscountCode($merchant);
        $affiliate = new Affiliate;
        $affiliate->merchant_id = $merchant->id;
        $affiliate->commission_rate = $commissionRate;
        $affiliate->discount_code = $discountCode['code'];

        $user->affiliate()->save($affiliate);

        // sending email
        Mail::to($email)
            ->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
