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
namespace Laminas\Google\Gcm;

use Laminas\Google\Exception;
use Laminas\Google\Exception\InvalidArgumentException;

/**
 * Google Cloud Messaging Response
 * This class parses out the response from
 * the Google Cloud Messaging API.
 *
 * @category   Laminas
 */
class Response
{
    /**
     * @const Message ID field
     */
    final public const RESULT_MESSAGE_ID = 'message_id';

    /**
     * @const Error field
     */
    final public const RESULT_ERROR = 'error';

    /**
     * @const Canonical field
     */
    final public const RESULT_CANONICAL = 'registration_id';

    /**
     * Error field responses
     * @link https://developers.google.com/cloud-messaging/http-server-ref#error-codes
     * @var string
     */
    final public const ERROR_MISSING_REGISTRATION         = 'MissingRegistration';
    final public const ERROR_INVALID_REGISTRATION         = 'InvalidRegistration';
    final public const ERROR_NOT_REGISTERED               = 'NotRegistered';
    final public const ERROR_INVALID_PACKAGE_NAME         = 'InvalidPackageName';
    final public const ERROR_MISMATCH_SENDER_ID           = 'MismatchSenderId';
    final public const ERROR_MESSAGE_TOO_BIG              = 'MessageTooBig';
    final public const ERROR_INVALID_DATA_KEY             = 'InvalidDataKey';
    final public const ERROR_INVALID_TTL                  = 'InvalidTtl';
    final public const ERROR_UNAVAILABLE                  = 'Unavailable';
    final public const ERROR_INTERNAL_SERVER_ERROR        = 'InternalServerError';
    final public const ERROR_DEVICE_MESSAGE_RATE_EXCEEDED = 'DeviceMessageRateExceeded';
    final public const ERROR_TOPICS_MESSAGE_RATE_EXCEEDED = 'TopicsMessageRateExceeded';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $cntSuccess;

    /**
     * @var int
     */
    protected $cntFailure;

    /**
     * @var int
     */
    protected $cntCanonical;

    /**
     * @var Message|null
     */
    protected $message;

    /**
     * @var array
     */
    protected $results;

    /**
     * @var array
     */
    protected $response;

    /**
     * Constructor.
     *
     * @param array $response
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function __construct($response = null, Message $message = null)
    {
        if ($response) {
            $this->setResponse($response);
        }

        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * Get Message.
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set Message.
     *
     *
     * @return Response
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get Response.
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set Response.
     *
     *
     * @return Response
     * @throws Exception\InvalidArgumentException
     */
    public function setResponse(array $response)
    {
        if (! isset(
            $response['results'],
            $response['success'],
            $response['failure'],
            $response['canonical_ids'],
            $response['multicast_id']
        )) {
            throw new InvalidArgumentException('Response did not contain the proper fields');
        }

        $this->response = $response;
        $this->results = $response['results'];
        $this->cntSuccess = (int) $response['success'];
        $this->cntFailure = (int) $response['failure'];
        $this->cntCanonical = (int) $response['canonical_ids'];
        $this->id = (int) $response['multicast_id'];

        return $this;
    }

    /**
     * Get Success Count.
     *
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->cntSuccess;
    }

    /**
     * Get Failure Count.
     *
     * @return int
     */
    public function getFailureCount()
    {
        return $this->cntFailure;
    }

    /**
     * Get Canonical Count.
     *
     * @return int
     */
    public function getCanonicalCount()
    {
        return $this->cntCanonical;
    }

    /**
     * Get Results.
     *
     * @return array multi dimensional array of:
     *               NOTE: key is registration_id if the message is passed.
     *               'registration_id' => [
     *               'message_id' => 'id',
     *               'error' => 'error',
     *               'registration_id' => 'id'
     *               ]
     */
    public function getResults()
    {
        return $this->correlate();
    }

    /**
     * Get Singular Result.
     *
     * @param int $flag one of the RESULT_* flags
     *
     * @return array singular array with keys being registration id
     *               value is the type of result
     */
    public function getResult($flag)
    {
        $ret = [];
        foreach ($this->correlate() as $k => $v) {
            if (isset($v[$flag])) {
                $ret[$k] = $v[$flag];
            }
        }

        return $ret;
    }

    /**
     * Correlate Message and Result.
     *
     * @return array
     */
    protected function correlate()
    {
        $results = $this->results;
        if ($this->message && $results) {
            $ids = $this->message->getRegistrationIds();
            while ($id = array_shift($ids)) {
                $results[$id] = array_shift($results);
            }
        }

        return $results;
    }
}
