<?php namespace Gviabcua\ExceptionReport\Classes;

use Gviabcua\ExceptionReport\Models\Settings as ExceptionReportSettings;

class Telegram extends Gateway
{
    public function __construct($options = [])
    {
        $token = ExceptionReportSettings::get('telegram_token');

        $this->name = 'telegram';

        $options += array(
            'host' => 'api.telegram.org',
            'port' => 443,
        );

        parent::__construct($token, $options);

        $this->apiUrl = "{$this->proto_part}://{$this->host}{$this->port_part}/bot{$token}";
    }

    protected function request($method, $params = [])
    {
        $url = $this->apiUrl . '/' . $method;
        $query = http_build_query($params);

        curl_setopt($this->handle, CURLOPT_POST, true);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $query);
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        $response_str = curl_exec($this->handle);
        $response = json_decode($response_str, true);

        return $response;
    }
    public function sendMessage($params = [])
    {
        return $this->request('sendMessage', [
            'chat_id'      => $params['chat_id'],
            'text'         => $params['text'],
            'parse_mode'   => $params['parse_mode'] ?? null,
            'reply_markup' => $params['reply_markup'] ?? null,
        ]);
    }
}