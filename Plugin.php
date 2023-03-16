<?php namespace Gviabcua\ExceptionReport;

use Backend\Facades\BackendAuth;
use Carbon\Carbon;
use Auth;
use Config;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Gviabcua\ExceptionReport\Classes\Telegram;
use Gviabcua\ExceptionReport\Models\Settings as ExceptionReportSettings;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;


class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'ExceptionReport',
            'description' => 'Report about exceptions to Telegram',
            'author'      => 'Gviabcua',
            'icon'        => 'icon-cog',
            'homepage'    => '',
        ];
    }

    public function boot()
    {
    	App::error(function (Exception $exception) {
            $settings = ExceptionReportSettings::instance();
            if (config('app.debug') == true && $settings->disabled_in_debug == true) {return;}
            if (BackendAuth::check() == true && $settings->disabled_for_admins == true) {return;}
            $exception_class_name = get_class($exception);
            $disabled_classes = $settings->disabled_exceptions;
            if (!collect($disabled_classes)->flatten()->contains($exception_class_name)) {
            	//kostil
            	if ((trim($exception->getMessage()) == "Access denied") and (trim($exception_class_name) == "Symfony\Component\HttpKernel\Exception\HttpException")){return;}
            	if ((trim($exception->getMessage()) == 'A user was found to match all plain text credentials however hashed credential "password" did not match.') and (trim($exception_class_name) == "Winter\Storm\Auth\AuthenticationException")){return;}
            	
            	
            	$login = null;
				$user = Auth::getUser();
				if ($user){if (isset($user->toArray()['username'])){$login =  $user->toArray()['username'];}}
            	$ipaddress = $this->get_ip();
				$ua = $this->get_ua();
				$info = "<b>Exeption in ".Config::get('app.name')."</b>\n\n";
                $info .= 'URL: ' . Request::url() . "\n";
                $class = "<b>Exception class:</b> " . $exception_class_name . "\n";
                $date = "<b>Date and Time:</b> " . Carbon::now() . "\n";
                $code = "<b>Code:</b> " . $exception->getCode() . "\n";
                $file = "<b>File:</b> " . $exception->getFile() . "\n";
                $line = "<b>Line:</b> " . $exception->getLine() . "\n";
                $message = "<b>Error:</b> " . $exception->getMessage() . "\n\n";
                $data = "<b>User:</b> " . $login . "\n";
                $data .= "<b>IP:</b> " . $ipaddress . "\n";
                $data .= "<b>UA:</b> " . $ua;

                $gateway = new Telegram();
                $gateway->sendMessage([
                    'chat_id'    => $settings->telegram_chat_id,
                    'text'       => $info . $class . $date . $code . $file . $line . $message. $data,
                    'parse_mode' => 'HTML',
                ]);
            }
        });
    }
    function get_ua(){
    	try {
			$ua = $_SERVER['HTTP_USER_AGENT'];
    	} catch (Exception $e) {
			$ua = "";
		}
		return $ua;
    }
	function get_ip(){
		try {
			if (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			} else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			} else if (isset($_SERVER['HTTP_FORWARDED'])) {
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			} else if (isset($_SERVER['REMOTE_ADDR'])) {
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			} else {
				$ipaddress = 'UNKNOWN';
			}
		} catch (Exception $e) {
			$ipaddress = "Error getting IP";
		}
		return $ipaddress;
	}
    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Exception report',
                'description' => 'Report about exceptions to Telegram',
                'category'    => SettingsManager::CATEGORY_NOTIFICATIONS,
                'icon'        => 'icon-cog',
                'class'       => 'Gviabcua\ExceptionReport\Models\Settings',
                'order'       => 500,
            ],
        ];
    }
    
/*
	public static function get_client_ua() {
		return 
	}
	*/
}
