<?php

namespace App\View\Components;

use App\BillingDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;
use Modules\Payment\Entities\Cart;
use Modules\Payment\Entities\Checkout;
use Modules\PaymentMethodSetting\Entities\PaymentMethod;

class CheckoutPageSection extends Component
{
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function render()
    {
        $type = $this->request->type;
        if (!empty($type)) {
            $current = BillingDetails::where('user_id', Auth::id())->latest()->first();
        } else {
            $current = '';
        }

        $profile = Auth::user();
        $profile->cityName = $profile->cityName();
        $bills = BillingDetails::with('country')->where('user_id', Auth::id())->latest()->get();

        $countries = DB::table('countries')->select('id', 'name')->get();
        $cities = DB::table('spn_cities')->where('country_id', $profile->country)->select('id', 'name')->get();


        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            $tracking = $cart->tracking;
        } else {
            $tracking = '';
        }

        if ($profile->role_id == 3) {

            /* if (isModuleActive('Subscription') && isSubscribe()) {
                 $total = 0;
             } else {
                 $total = Cart::where('user_id', Auth::user()->id)->sum('price');
             }*/
            $total = Cart::where('user_id', Auth::user()->id)->sum('price');
        }
        $checkout = Checkout::where('tracking', $tracking)->where('user_id', Auth::id())->latest()->first();
        if (!$checkout) {
            $checkout = new Checkout();
        }

        $checkout->discount = 0.00;

        $checkout->tracking = $tracking;
        $checkout->user_id = Auth::id();
        $checkout->price = $total;
        if (hasTax()) {
            $checkout->purchase_price = applyTax($total);
            $checkout->tax = taxAmount($total);
        } else {
            $checkout->purchase_price = $total;
        }
        $checkout->status = 0;
        $checkout->save();
        $methods = PaymentMethod::where('active_status', 1)->get(['method', 'logo']);

        $carts = Cart::where('user_id', Auth::id())->with('course', 'course.user')->get();
        return view(theme('components.checkout-page-section'), compact('checkout', 'carts', 'methods', 'current', 'bills', 'countries', 'cities', 'profile'));
    }
}
