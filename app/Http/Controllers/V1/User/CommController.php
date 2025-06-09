<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sni;
use App\Utils\Dict;
use App\Utils\CustomSni;
use Illuminate\Http\Request;

class CommController extends Controller
{
    public function config(Request $request)
    {
        $config = [
            'aikopanel' => config('aikopanel.app_url'),
            'is_telegram' => (int) config('aikopanel.telegram_bot_enable', 0),
            'telegram_discuss_link' => config('aikopanel.telegram_discuss_link'),
            'zalo_discuss_link' => config('aikopanel.zalo_discuss_link'),
            'stripe_pk' => config('aikopanel.stripe_pk_live'),
            'withdraw_methods' => config('aikopanel.commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
            'withdraw_close' => (int) config('aikopanel.withdraw_close_enable', 0),
            'currency' => config('aikopanel.currency', 'VND'),
            'currency_symbol' => config('aikopanel.currency_symbol', '₫'),
            'naptien_on' => (int) config('aikopanel.naptien_on', 0),
            'min_recharge_amount' => (int) config('aikopanel.min_recharge_amount', 1000),
            'max_recharge_amount' => (int) config('aikopanel.max_recharge_amount', 100000),
            'commission_distribution_enable' => (int) config('aikopanel.commission_distribution_enable', 0),
            'commission_distribution_l1' => config('aikopanel.commission_distribution_l1'),
            'commission_distribution_l2' => config('aikopanel.commission_distribution_l2'),
            'commission_distribution_l3' => config('aikopanel.commission_distribution_l3'),
            'show_total_user_enable' => (int) config('aikopanel.show_total_user_enable', 0),
            'app_windows_enable' => (int) config('aikopanel.app_windows_enable', 1),
            'app_macos_enable' => (int) config('aikopanel.app_macos_enable', 1),
            'app_ios_enable' => (int) config('aikopanel.app_ios_enable', 1),
            'app_android_enable' => (int) config('aikopanel.app_android_enable', 1),
        ];

        $sniData = Sni::where('show', 1)->get();

        $config['sni'] = $sniData->map(function ($item) {
            return [
                'value' => $item->value,
                'lable' => $item->label,
                'abbreviation' => $item->abbreviation,
                'content' => $item->content,
            ];
        })->toArray();

        $url = $request->getHost();
        $maindomain = parse_url(config('aikopanel.app_url'), PHP_URL_HOST);

        if ($url === $maindomain) {
            $config = array_merge($config, [
                'collaborator_enable' => (int) config('aikopanel.collaborator_enable', 0),
                'cloudflare_ns_1' => config('aikopanel.cloudflare_ns_1'),
                'cloudflare_ns_2' => config('aikopanel.cloudflare_ns_2'),
            ]);
        }

        // kiểm tra xem config('aikopanel.appleid_custom_url') có null không nếu có thì gán chuyển tiếp tới /appleid
        if (config('aikopanel.appleid_custom_url') === null && config('aikopanel.appleid_api') === null) {
            $config['appleid_custom_url'] = '/appleid';
        } else {
            $config['appleid_custom_url'] = config('aikopanel.appleid_custom_url');
        }

        return response([
            'data' => $config
        ]);
    }

    public function getStripePublicKey(Request $request)
    {
        $payment = Payment::where('id', $request->input('id'))
            ->where('payment', 'StripeCredit')
            ->first();
        if (!$payment)
            abort(500, 'payment is not found');
        return response([
            'data' => $payment->config['stripe_pk_live']
        ]);
    }
}