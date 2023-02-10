<?php
/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 *
 * @category  Laminas
 */
namespace Laminas\Google\Gcm;

use Laminas\Google\Exception;
use Laminas\Google\Exception\InvalidArgumentException;
use Laminas\Http\Client as HttpClient;
use Laminas\Json\Exception\RuntimeException;
use Laminas\Json\Json;

/**
 * Google Cloud Messaging Client
 * This class allows the ability to send out messages
 * through the Google Cloud Messaging API.
 *
 * @category   Laminas
 */
class Client
{
    /**
     * @const string Server URI
     */
    final public const SERVER_URI = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var \Laminas\Http\Client|null
     */
    protected $httpClient;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * Get API Key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set API Key.
     *
     * @param string $apiKey
     *
     * @return Client
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setApiKey($apiKey)
    {
        if (! is_string($apiKey) || empty($apiKey)) {
            throw new InvalidArgumentException('The api key must be a string and not empty');
        }
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get HTTP Client.
     *
     * @throws \Laminas\Http\Client\Exception\InvalidArgumentException
     *
     * @return \Laminas\Http\Client
     */
    public function getHttpClient()
    {
        if (! $this->httpClient) {
            $this->httpClient = new HttpClient();
            $this->httpClient->setOptions(['strictredirects' => true]);
        }

        return $this->httpClient;
    }

    /**
     * Set HTTP Client.
     *
     * @return Client
     */
    public function setHttpClient(HttpClient $http)
    {
        $this->httpClient = $http;

        return $this;
    }

    /**
     * Send Message.
     *
     *
     * @throws RuntimeException
     * @throws \Laminas\Google\Exception\RuntimeException
     * @throws \Laminas\Http\Exception\RuntimeException
     * @throws \Laminas\Http\Client\Exception\RuntimeException
     * @throws \Laminas\Http\Exception\InvalidArgumentException
     * @throws \Laminas\Http\Client\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @return Response
     */
    public function send(Message $message)
    {
        $client = $this->getHttpClient();
        $client->setUri(self::SERVER_URI);
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', 'key=' . $this->getApiKey());
        $headers->addHeaderLine('Content-length', (string) mb_strlen($message->toJson()));

        $response = $client->setHeaders($headers)
                           ->setMethod('POST')
                           ->setRawBody($message->toJson())
                           ->setEncType('application/json')
                           ->send();

        switch ($response->getStatusCode()) {
            case 500:
                throw new Exception\RuntimeException('500 Internal Server Error');
            case 503:
                $exceptionMessage = '503 Server Unavailable';
                if ($retry = $response->getHeaders()->get('Retry-After')) {
                    $exceptionMessage .= '; Retry After: '.$retry;
                }
                throw new Exception\RuntimeException($exceptionMessage);
            case 401:
                throw new Exception\RuntimeException('401 Forbidden; Authentication Error');
            case 400:
                throw new Exception\RuntimeException('400 Bad Request; invalid message');
        }

        if (! $response = Json::decode($response->getBody(), Json::TYPE_ARRAY)) {
            throw new Exception\RuntimeException('Response body did not contain a valid JSON response');
        }

        return new Response($response, $message);
    }
}
