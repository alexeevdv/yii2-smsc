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
    private $_charset;

    /**
     * Server response format
     * @var integer
     */
    private $_format;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->login === null) {
            throw new InvalidConfigException('`login` is required');
        }

        if ($this->password === null) {
            throw new InvalidConfigException('`password` is required');
        }

        if ($this->getFormat() === null) {
            $this->setFormat(static::FORMAT_JSON);
        }

        if ($this->getCharset() === null) {
            $this->setCharset('utf-8');
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
     * @param integer $format
     * @throws NotSupportedException
     */
    public function setFormat($format)
    {
        if ($format !== static::FORMAT_JSON) {
            throw new NotSupportedException('Only JSON is supported for `format`');
        }
        $this->_format = $format;
    }

    /**
     * @return int
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * @param string $charset
     * @throws NotSupportedException
     */
    public function setCharset($charset)
    {
        if (!in_array($charset, ['windows-1251', 'utf-8', 'koi8-r'])) {
            throw new NotSupportedException('Unsupported charset: ' . $charset);
        }
        $this->_charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
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
     * @param string $numbers
     * @param string $message
     * @param array $params
     * @return array|null
     */
    public function sendViber($numbers, $message, array $params = [])
    {
        return $this->send(
            $numbers,
            $message,
            ArrayHelper::merge(
                [
                    'viber' => 1,
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
        if (!isset($params['fmt'])) {
            return null;
        }

        Yii::trace(
            [
                'method' => $method,
                'params' => $params,
            ],
            static::class
        );

        /** @var HttpClient $httpClient */
        $httpClient = Yii::createObject(HttpClient::className(), [
            ['baseUrl' => $this->baseUrl]
        ]);
        $response = $httpClient->get($method, $params)->send();
        Yii::trace($response, static::class);

        if ($params['fmt'] === static::FORMAT_JSON) {
            return Json::decode($response->content);
        }
        return null;
    }
}
