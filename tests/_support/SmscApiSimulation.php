<?php

use yii\helpers\Json;
use yii\httpclient\Response as HttpResponse;
use yii\httpclient\Request as HttpRequest;
use yii\httpclient\Client as HttpClient;

class SmscApiSimulation
{
    public static function handle(HttpRequest $request)
    {
        // check for required params
        $data = $request->getData();
        if (!isset($data['login']) || !isset($data['password']) || !isset($data['fmt']) || !in_array($data['fmt'], [0, 1, 2, 3])) {
            return static::jsonResponse([
                'error' => 'Ошибка в параметрах.',
                'error_code' => 1,
            ]);
        }

        if ($data['login'] != 'login' || $data['password'] != 'password') {
            return static::jsonResponse([
                'error' => 'Неверный логин или пароль.',
                'error_code' => 2,
            ]);
        }

        $url = $request->getUrl();
        if ($url === 'balance.php') {
            return static::getBalance($request);
        }

        $response = new HttpResponse;
        $response->setHeaders([
            'http-code' => 404,
        ]);
        return $response;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public static function getBalance(HttpRequest $request)
    {
        // JSON
        if ($request->getData()['fmt'] == 3) {
            return static::jsonResponse([
                'balance' => 100,
            ]);
        }
    }

    /**
     * @param array $params
     * @return HttpResponse
     */
    public static function jsonResponse(array $params = [])
    {
        $response = new HttpResponse;
        $response->setHeaders([
            'http-code' => 200,
        ]);
        $response->setFormat(HttpClient::FORMAT_JSON);
        $response->setContent(Json::encode($params));
        return $response;
    }
}
