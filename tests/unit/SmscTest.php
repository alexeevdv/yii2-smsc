<?php

use alexeevdv\sms\Smsc;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\di\Container;
use yii\httpclient\Client as HttpClient;

class SmscTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Yii::$container = new Container([
            'singletons' => [
                HttpClient::class => function () {
                    return Mockery::mock(HttpClient::class . '[send]')
                        ->shouldReceive('send')
                        ->andReturnUsing(['SmscApiSimulation', 'handle'])
                        ->getMock();
                }
            ],
        ]);
    }

    public function testLoginIsRequired()
    {
        $this->expectException(InvalidConfigException::class);
        new Smsc([
            'password' => 'password',
        ]);
    }

    public function testPasswordIsRequired()
    {
        $this->expectException(InvalidConfigException::class);
        new Smsc([
            'login' => 'login',
        ]);
    }

    public function testXmlFormatIsNotSuppported()
    {
        $smsc = $this->getJsonInstance();
        $this->expectException(NotSupportedException::class);
        $smsc->setFormat(Smsc::FORMAT_XML);
    }

    public function testStringFormatIsNotSupported()
    {
        $smsc = $this->getJsonInstance();
        $this->expectException(NotSupportedException::class);
        $smsc->setFormat(Smsc::FORMAT_STRING);
    }

    public function testNumbersFormatIsNotSupported()
    {
        $smsc = $this->getJsonInstance();
        $this->expectException(NotSupportedException::class);
        $smsc->setFormat(Smsc::FORMAT_NUMBERS);
    }

    public function testGetAndSetFormat()
    {
        $smsc = $this->getJsonInstance();
        $this->assertEquals(Smsc::FORMAT_JSON, $smsc->getFormat(), 'JSON should be set by default');
        $smsc->setFormat(Smsc::FORMAT_JSON);
        $this->assertEquals(Smsc::FORMAT_JSON, $smsc->getFormat());
    }

    public function testGetAndSetCharset()
    {
        $smsc = $this->getJsonInstance();
        $this->assertEquals('utf-8', $smsc->getCharset(), 'UTF-8 should be set by default');
        $smsc->setCharset('utf-8');
        $this->assertEquals('utf-8', $smsc->getCharset());

        $smsc->setCharset('windows-1251');
        $this->assertEquals('windows-1251', $smsc->getCharset());

        $smsc->setCharset('koi8-r');
        $this->assertEquals('koi8-r', $smsc->getCharset());

        $this->expectException(NotSupportedException::class);
        $smsc->setCharset('windows-1252');
    }

    public function testGetCommonParams()
    {
        $smsc = $this->getJsonInstance();
        $this->assertEquals(
            [
                'login' => 'login',
                'psw' => 'password',
                'fmt' => Smsc::FORMAT_JSON,
            ],
            $smsc->getCommonParams()
        );
    }

    public function testApiCall()
    {
        $smsc = $this->getJsonInstance();
        $this->assertNull($smsc->apiCall(null));
        $this->assertNull($smsc->apiCall('balance.php'));
        $this->assertNull($smsc->apiCall('balance.php', [
            'format' => Smsc::FORMAT_XML,
            'login' => 'login',
            'psw' => 'password',
        ]));

        $response = $smsc->apiCall('balance.php', [
            'fmt' => Smsc::FORMAT_JSON,
            'login' => 'login',
            'psw' => 'password',
        ]);
        $this->assertEquals(['balance' => 100], $response);
    }

    public function testBalance()
    {
        $this->assertEquals(['balance' => 100], $this->getJsonInstance()->balance());
    }

    public function testSend()
    {
        $smsc = $this->getJsonInstance();
        $response = $smsc->send('79532328822', 'Test');
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('cnt', $response);
        $this->assertArrayHasKey('cost', $response);
        $this->assertArrayHasKey('balance', $response);
    }

    public function testStatus()
    {
        $smsc = $this->getJsonInstance();
    }

    /**
     * @return Smsc
     */
    private function getJsonInstance()
    {
        $smsc = new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
        ]);
        return $smsc;
    }
}
