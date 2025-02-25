<?php

namespace App\Http\Controllers\V1\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ConfigSave;
use App\Utils\Helper;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ConfigController extends Controller
{
    public function setTelegramWebhook(Request $request)
    {
        if (!Helper::checklicense()) {
            return abort(500, 'License illegal Please contact https://t.me/Tele_Aiko to purchase copyright.');
        }
        $id = $request->user['id'];
        $app_url = 'https://' . $request->server('HTTP_HOST');
        if (blank($app_url))
            return abort(500, 'Vui lòng cấu hình app_url');
        $hookUrl = $app_url . '/api/v1/guest/telegram/webhook?' . http_build_query([
            'access_token' => md5(config('staff.aikopanel-id-' . $id . '.telegram_bot_token', $request->input('telegram_bot_token')))
        ]);
        $telegramService = new TelegramService($request->input('telegram_bot_token'));
        $telegramService->getMe();
        $telegramService->setWebhook($hookUrl);
        return response([
            'data' => true
        ]);
    }

    public function fetch(Request $request)
    {
        $id = $request->user['id'];
        $key = $request->input('key');
        $data = [
            'site' => [
                'app_name' => config('staff.aikopanel-id-' . $id . '.app_name', 'AikoPanel'),
                'app_description' => config('staff.aikopanel-id-' . $id . '.app_description', 'AikoPanel of AikoCute!'),
                'logo' => config('staff.aikopanel-id-' . $id . '.logo'),
                'background_url' => config('staff.aikopanel-id-' . $id . '.background_url'),
                'custom_html' => config('staff.aikopanel-id-' . $id . '.custom_html'),
            ],
            'connect' => [
                'telegram_bot_enable' => config('staff.aikopanel-id-' . $id . '.telegram_bot_enable', 0),
                'telegram_bot_token' => config('staff.aikopanel-id-' . $id . '.telegram_bot_token'),
                'telegram_discuss_link' => config('staff.aikopanel-id-' . $id . '.telegram_discuss_link'),
                'zalo_discuss_link' => config('staff.aikopanel-id-' . $id . '.zalo_discuss_link'),
                'report_user_traffic_today' => config('staff.aikopanel-id-' . $id . '.report_user_traffic_today', 0),
                'id_group_admin_report_traffic_user_today' => config('staff.aikopanel-id-' . $id . '.id_group_admin_report_traffic_user_today'),
                'interval_report_user_traffic_to_user_today' => config('staff.aikopanel-id-' . $id . '.interval_report_user_traffic_to_user_today'),
                'id_group_user_report_traffic_user_today' => config('staff.aikopanel-id-' . $id . '.id_group_user_report_traffic_user_today'),
                'report_node_traffic_today' => config('staff.aikopanel-id-' . $id . '.report_node_traffic_today', 0),
                'id_group_admin_report_traffic_node_today' => config('staff.aikopanel-id-' . $id . '.id_group_admin_report_traffic_node_today'),
                'interval_report_node_traffic_to_user_today' => config('staff.aikopanel-id-' . $id . '.interval_report_node_traffic_to_user_today'),
                'id_group_user_report_traffic_node_today' => config('staff.aikopanel-id-' . $id . '.id_group_user_report_traffic_node_today'),
                'report_node_online' => config('staff.aikopanel-id-' . $id . '.report_node_online', 0),
                'id_group_admin_report_node_online_today' => config('staff.aikopanel-id-' . $id . '.id_group_admin_report_node_online_today'),
                'interval_report_node_online_to_user_today' => config('staff.aikopanel-id-' . $id . '.interval_report_node_online_to_user_today'),
                'id_group_user_report_node_online_today' => config('staff.aikopanel-id-' . $id . '.id_group_user_report_node_online_today'),
            ],
        ];
        if ($key && isset($data[$key])) {
            return response([
                'data' => [
                    $key => $data[$key]
                ]
            ]);
        }
        ;

        return response([
            'data' => $data
        ]);
    }

    public function save(ConfigSave $request)
    {
        if (!Helper::checklicense()) {
            return abort(500, 'License illegal Please contact https://t.me/Tele_Aiko to purchase copyright.');
        }
        $id = $request->user['id'];
        $data = $request->validated();
        $config = config('staff.aikopanel-id-' . $id);
        foreach (ConfigSave::RULES as $k => $v) {
            if (!in_array($k, array_keys(ConfigSave::RULES))) {
                unset($config[$k]);
                continue;
            }
            if (array_key_exists($k, $data)) {
                $config[$k] = $data[$k];
            }
        }
        $data = var_export($config, 1);
        if (!File::put(base_path() . '/config/staff/aikopanel-id-' . $id . '.php', "<?php\n return $data ;")) {
            abort(500, 'Không chỉnh sửa');
        }
        if (function_exists('opcache_reset')) {
            if (opcache_reset() === false) {
                abort(500, 'Bộ nhớ cache rõ ràng, vui lòng gỡ cài đặt hoặc kiểm tra trạng thái cấu hình opcache');
            }
        }
        Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}