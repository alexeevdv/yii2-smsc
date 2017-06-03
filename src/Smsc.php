<?php

namespace alexeevdv\sms;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client as HttpClient;

/**
 * Class Smsc
 * @package alexeevdv\sms
 */
class Smsc extends \yii\base\Component
{
    const FORMAT_STRING = 0;
    const FORMAT_NUMBERS = 1;
    const FORMAT_XML = 2;
    const FORMAT_JSON = 3;

    /**
     * Login
     * @var string
     */
    public $login;

    /**
     * Password or lowercase md5 hash of password
     * @var string
     */
    public $password;

    /**
     * Base url for smsc api
     * @var string
     */
    public $baseUrl = 'https://smsc.ru/sys/';

    /**
     * Message encoding
     * @var string
     */
    public $charset = 'utf-8';

    /**
     * Server response format
     * @var integer
     */
    public $format;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->login) {
            throw new InvalidConfigException('`login` is required');
        }

        if (!$this->password) {
            throw new InvalidConfigException('`password` is required');
        }

        if ($this->format === null) {
            $this->format = static::FORMAT_JSON;
        }

        if ($this->format !== static::FORMAT_JSON) {
            throw new NotSupportedException('Only JSON is supported for `format`');
        }

        if (!in_array($this->charset, ['windows-1251', 'utf-8', 'koi8-r'])) {
            throw new NotSupportedException('Unsupported charset: ' . $this->charset);
        }
    }

    /**
     * @param array|string $numbers
     * @param array|string $ids
     * @param array $params
     * @return array|null
     */
    public function status($numbers, $ids, $params = [])
    {
        return $this->apiCall(
            'send.php',
            ArrayHelper::merge(
                $this->getCommonParams(),
                [
                    'phone' => implode(',', (array)$numbers),
                    'id' => implode(',', (array)$ids),
                    'charset' => $this->charset,
                ],
                $params
            )
        );
    }

    /**
     * @param string $numbers
     * @param string $message
     * @param array $params
     * @return array|null
     */
    public function send($numbers, $message, array $params = [])
    {
        return $this->apiCall(
            'send.php',
            ArrayHelper::merge(
                $this->getCommonParams(),
                [
                    'phones' => implode(';', (array) $numbers),
                    'mes' => $message,
                    'charset' => $this->charset,
                    'cost' => 3,
                ],
                $params
            )
        );
    }

    /**
     * @param array $params
     * @return array|null
     */
    public function balance(array $params = [])
    {
        return $this->apiCall(
            'balance.php',
            ArrayHelper::merge(
                $this->getCommonParams(),
                $params
            )
        );
    }

    /**
     * Common params for all API calls
     * @return array
     */
    public function getCommonParams()
    {
        return [
            'login' => $this->login,
            'psw' => $this->password,
            'fmt' => $this->format,
        ];
    }

    /**
     * @param string $method
     * @param array $params
     * @return array|null
     */
    public function apiCall($method, array $params = [])
    {
        Yii::trace(
            [
                'method' => $method,
                'params' => $params,
            ],
            'smsc'
        );

        /** @var HttpClient $httpClient */
        $httpClient = Yii::createObject(HttpClient::className(), [
            ['baseUrl' => $this->baseUrl]
        ]);
        $response = $httpClient->get($method, $params)->send();
        Yii::trace($response, 'smsc');

        if ($this->format === static::FORMAT_JSON) {
            return Json::decode($response->content);
        }
        return null;
    }
}
