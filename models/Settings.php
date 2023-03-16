<?php namespace Gviabcua\ExceptionReport\Models;

use Winter\Storm\Database\Model;

class Settings extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    public $settingsCode = 'exception_report_settings';
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->disabled_in_debug = true;
        $this->disabled_for_admins = true;
        $this->disabled_exceptions = [
            ['class_name' => 'Winter\Storm\Exception\AjaxException'], 
            ['class_name' => 'Winter\Storm\Exception\ValidationException'],
            ['class_name' => 'Winter\Storm\Auth\AuthenticationException'],
            ['class_name' => 'Symfony\Component\HttpKernel\Exception\HttpException'],
            ['class_name' => 'WebSocket\ConnectionException'],
        ];
    }
}
