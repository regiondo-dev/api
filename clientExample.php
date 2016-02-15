<?php

class testApi
{
    protected $_args = [];

    protected $_basePath = 'categories';
    protected $_baseUrl = 'https://api.regiondo.com/v1/';
    protected $_acceptLanguage = 'de-DE';
    protected $_publicKey = null;
    protected $_privateKey = null;

    public function __construct()
    {
        $this->init();

        $time = time();

        $data = $this->getData();

        $message = $this->buildMessage($time, $this->_publicKey, $data);

        $hash = hash_hmac('sha256', $message, $this->_privateKey);
        $headers = [
            'X-API-ID: ' .   $this->_publicKey,
            'X-API-TIME: ' . $time,
            'X-API-HASH: ' . $hash,
            'Accept-Language: ' . $this->_acceptLanguage,
        ];
        $this->curlExec($data, $headers);

    }

    protected function getData()
    {
        $res = [];
        if ($this->getArg('data')) {
            $tmp = explode('#', $this->getArg('data'));
            foreach ($tmp as $param) {
                list($k, $v) = explode('=', $param);
                $res[ $k ] = $v;
            }
        }

        return $res;
    }

    protected function init()
    {
        $this->_parseArgs();

        date_default_timezone_set('UTC'); // important to use everywhere UTC

        if (!$this->getArg('publicKey') || !$this->getArg('privateKey')) {
            echo "\nPublic and Private keys are required\n";
            echo $this->usageHelp();
            die();
        }

        $this->_publicKey = $this->getArg('publicKey');
        $this->_privateKey = $this->getArg('privateKey');

        if ($this->getArg('baseUrl')) {
            $this->_baseUrl = $this->getArg('baseUrl');
        }
        if ($this->getArg('action')) {
            $this->_basePath = $this->getArg('action');
        }
        if ($this->getArg('lng')) {
            $this->_acceptLanguage = $this->getArg('lng');
        }

    }

    protected function buildMessage($time, $publicKey, $data)
    {
        return $time . $publicKey . http_build_query($data);
    }

    protected function curlExec($data, $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . $this->_basePath . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $result = curl_exec($ch);
        if ($result === false) {
            echo "Curl Error: " . curl_error($ch);
        } else {
            echo PHP_EOL;
            echo "Request: " . PHP_EOL;
            echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
            echo PHP_EOL;

            echo "Response:" . PHP_EOL;
            echo $result;
            echo PHP_EOL;
        }

        curl_close($ch);

    }

    public function getArg($name)
    {
        if (isset($this->_args[ $name ])) {
            return $this->_args[ $name ];
        }

        return false;
    }

    protected function _parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = [];
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[ $current ] = true;
            } else {
                if ($current) {
                    $this->_args[ $current ] = $arg;
                } else {
                    if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                        $this->_args[ $match[1] ] = true;
                    }
                }
            }
        }

        return $this;
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php clientExample.php [options]

  -publicKey KEY          Public key
  -privateKey KEY         Protected (secure) key
  -action                 Url action part (Ex.: categories, products etc)
  -lng                    Accepted language (Ex.: de-DE, en-US, de-AT, fr-FR etc.)
  -baseUrl                Base API URL, like https://api.regiondo.de/v1/
  -data                   Any GET parameters which you want to send. Ex.: limit=10#offset=100

  Ex.(min):  php clientExample.php -publicKey YOUR_PUBLIC_KEY -privateKey YOUR_PRIVATE_KEY
  Ex.(full): php clientExample.php -publicKey YOUR_PUBLIC_KEY -privateKey YOUR_PRIVATE_KEY -action categories -lng de-DE -baseUrl https://api.regiondo.com/v1/ -data limit=10#offset=100

  Command line example: params=$(php -e -r "echo http_build_query(['limit' => 10, 'offset' => 10]);"); h=$(php -e -r "echo hash_hmac('sha256', (time() . 'YOUR_PUBLIC_KEY' . http_build_query(['limit' => 10, 'offset' => 10])), 'YOUR_PRIVATE_KEY');"); ts=$(date +%s); curl -i -H "X-API-ID: YOUR_PUBLIC_KEY"  -H "X-API-TIME: \$ts"  -H "X-API-HASH: \$h" -H "Accept-Language: de-DE" -H "Accept: application/json"  "https://api.regiondo.com/v1/categories?\$params"

USAGE;
    }
}

new testApi();







