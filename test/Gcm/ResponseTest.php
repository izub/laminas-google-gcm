<?php
/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link       http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 *
 * @category   Laminas
 */
namespace LaminasTest\Google\Gcm;

use Laminas\Google\Gcm\Message;
use Laminas\Google\Gcm\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Error;
use TypeError;

/**
 * @category   Laminas
 * @group      Laminas
 * @group      Laminas_Google
 * @group      Laminas_Google_Gcm
 */
class ResponseTest extends TestCase
{
    public function testConstructorExpectedBehavior()
    {
        $response = new Response();
        self::assertNull($response->getResponse());
        self::assertNull($response->getMessage());

        $message = new Message();
        $response = new Response(null, $message);
        self::assertEquals($message, $response->getMessage());
        self::assertNull($response->getResponse());

        $message = new Message();
        $responseArray = [
            'results' => [
                ['message_id' => '1:1234'],
            ],
            'success' => 1,
            'failure' => 0,
            'canonical_ids' => 0,
            'multicast_id' => 1,
        ];
        $response = new Response($responseArray, $message);
        self::assertEquals($responseArray, $response->getResponse());
        self::assertEquals($message, $response->getMessage());
    }

    public function testInvalidConstructorThrowsException()
    {
        $this->expectException(TypeError::class);
        new Response('{bad');
    }

    public function testMessageExpectedBehavior()
    {
        $message = new Message();
        $response = new Response();
        $response->setMessage($message);
        self::assertEquals($message, $response->getMessage());
    }

    public function testResponse()
    {
        $responseArr = [
            'results' => [
                ['message_id' => '1:234'],
            ],
            'success' => 1,
            'failure' => 0,
            'canonical_ids' => 0,
            'multicast_id' => '123',
        ];
        $response = new Response();
        $response->setResponse($responseArr);
        self::assertEquals($responseArr, $response->getResponse());
        self::assertEquals(1, $response->getSuccessCount());
        self::assertEquals(0, $response->getFailureCount());
        self::assertEquals(0, $response->getCanonicalCount());
        // test results non correlated
        $expected = [['message_id' => '1:234']];
        self::assertEquals($expected, $response->getResults());
        $expected = [0 => '1:234'];
        self::assertEquals($expected, $response->getResult(Response::RESULT_MESSAGE_ID));

        $message = new Message();
        $message->setRegistrationIds(['ABCDEF']);
        $response->setMessage($message);
        $expected = ['ABCDEF' => '1:234'];
        self::assertEquals($expected, $response->getResult(Response::RESULT_MESSAGE_ID));
    }
}
