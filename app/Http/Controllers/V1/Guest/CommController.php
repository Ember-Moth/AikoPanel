<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Utils\Dict;
use Illuminate\Support\Facades\Http;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'tos_url' => config('aikopanel.tos_url'),
                'is_email_verify' => (int)config('aikopanel.email_verify', 0) ? 1 : 0,
                'is_invite_force' => (int)config('aikopanel.invite_force', 0) ? 1 : 0,
                'email_whitelist_suffix' => (int)config('aikopanel.email_whitelist_enable', 0)
                    ? $this->getEmailSuffix()
                    : 0,
                'is_recaptcha' => (int)config('aikopanel.recaptcha_enable', 0) ? 1 : 0,
                'recaptcha_site_key' => config('aikopanel.recaptcha_site_key'),
                'app_description' => config('aikopanel.app_description'),
                'app_url' => config('aikopanel.app_url'),
                'logo' => config('aikopanel.logo'),
            ]
        ]);
    }

    private function getEmailSuffix()
    {
        $suffix = config('aikopanel.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT);
        if (!is_array($suffix)) {
            return preg_split('/,/', $suffix);
        }
        return $suffix;
    }
}
