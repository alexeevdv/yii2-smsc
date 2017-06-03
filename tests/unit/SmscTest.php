<?php

use alexeevdv\sms\Smsc;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

class SmscTest extends PHPUnit_Framework_TestCase
{
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

    public function testJsonFormatIsSupported()
    {
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
        ]);
    }

    public function testXmlFormatIsNotSuppported()
    {
        $this->expectException(NotSupportedException::class);
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_XML,
        ]);
    }

    public function testStringFormatIsNotSupported()
    {
        $this->expectException(NotSupportedException::class);
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_STRING,
        ]);
    }

    public function testNumbersFormatIsNotSupported()
    {
        $this->expectException(NotSupportedException::class);
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_NUMBERS,
        ]);
    }

    public function testWindows1251CharsetIsSupported()
    {
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
            'charset' => 'windows-1251',
        ]);
    }

    public function testUtf8CharsetIsSupported()
    {
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
            'charset' => 'utf-8',
        ]);
    }

    public function testKoi8rCharsetIsSupported()
    {
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
            'charset' => 'koi8-r',
        ]);
    }

    public function testOtherCharsetsAreNotSupported()
    {
        $this->expectException(NotSupportedException::class);
        new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
            'charset' => 'windows-1252',
        ]);
    }

    public function testGetCommonParams()
    {
        $smsc = new Smsc([
            'login' => 'login',
            'password' => 'password',
            'format' => Smsc::FORMAT_JSON,
        ]);

        $this->assertEquals(
            [
            'login' => 'login',
            'psw' => 'password',
            'fmt' => Smsc::FORMAT_JSON,
            ],
            $smsc->getCommonParams()
        );
    }
}
