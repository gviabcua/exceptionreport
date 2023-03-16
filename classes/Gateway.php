<?php namespace Gviabcua\ExceptionReport\Classes;

use Exception;

abstract class Gateway
{
    public $name;

    protected $host;
    protected $port;
    protected $apiUrl;

    protected $proto_part;
    protected $port_part;

    protected $handle;
    protected $botToken;

    public function __construct($token, $options = [])
    {
        if ($this->name == null) {
            throw new Exception('Gateway name is empty!');
        }

        $this->handle = curl_init();
        $this->host = $host = $options['host'];
        $this->port = $port = $options['port'];
        $this->botToken = $token;

        $this->proto_part = ($port == 443 ? 'https' : 'http');
        $this->port_part = ($port == 443 || $port == 80) ? '' : ':' . $port;
    }

    /**
     * Отправка запроса боту
     * @param $method
     * @param array $params
     * @return mixed
     */
    abstract protected function request($method, $params = []);

    /**
     * Отправка сообщения
     * @param array $params
     * @return mixed
     */
    abstract public function sendMessage($params = []);
}