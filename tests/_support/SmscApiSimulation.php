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
        if (!isset($data['login']) || !isset($data['psw']) || !isset($data['fmt']) || !in_array($data['fmt'], [0, 1, 2, 3])) {
            return static::jsonResponse([
                'error' => 'Ошибка в параметрах.',
                'error_code' => 1,
            ]);
        }

        if ($data['login'] != 'login' || $data['psw'] != 'password') {
            return static::jsonResponse([
                'error' => 'Неверный логин или пароль.',
                'error_code' => 2,
            ]);
        }

        $url = $request->getUrl();
        if ($url === 'balance.php') {
            return static::handleBalance($request);
        }
        if ($url === 'send.php') {
            return static::handleSend($request);
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
    private static function handleSend(HttpRequest $request)
    {
        $data = $request->getData();
        $fmt = $data['fmt'];
        $cost = $data['cost'];

        if ($fmt == 3 && $cost == 3) {
            return static::jsonResponse([
                'id' => 1,
                'cnt' => 1,
                'cost' => 2,
                'balance' => 100,
            ]);
        }
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    private static function handleBalance(HttpRequest $request)
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
    private static function jsonResponse(array $params = [])
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
