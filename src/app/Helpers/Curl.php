<?php
namespace App\Helpers;

/**
 * A wrapper class of CURL library
 *
 * @version 1.0
 * @package App\Helpers
*/
class Curl
{
    const MAX_EXECUTE_TIME = 5;
    const HTTP_GET         = 1;
    const HTTP_POST        = 2;
    const HTTP_PUT         = 3;
    const HTTP_DELETE      = 4;

    /**
     * @var integer
    */
    protected $timeout = 20;

    /**
     * @var integer
    */
    protected $method;

    /**
     * @var boolean
    */
    protected $usingJson = true;

    /**
     * @var array
    */
    protected $headers;

    /**
     * @var resource
    */
    protected $ch;

    /**
     * @var array
    */
    protected $observers = [];

    /**
     * @var integer
    */
    protected $startTime;

    /**
     * @var integer
    */
    protected $stopTime;

    /**
     * @var string
    */
    protected $currentUrl;

    /**
     * @var Exception
    */
    protected $exception;

    /**
     * @var array
    */
    protected $lastError;

    /**
     * Define class Constructor
    */
    public function __construct()
    {
        $this->ch = curl_init();

        if (!$this->ch) {
            throw new Exception('Can\' create cURL resource');
        }
    }

    /**
     * Set timeout
     * @param integer $timeout
    */
    public function setTimeOut($timeout = 10)
    {
        $this->timeout = $timeout;
    }

    /**
     * Set headers
     * @param string $header
    */
    public function setHeader($header = '')
    {
        $this->headers[] = $header;
    }

    /**
     * Set method
     * @param integer $method
    */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Set json when POST|PUT data
    */
    public function setUsingJson($value = true)
    {
        $this->usingJson = $value;
    }

    /**
     * Set option for cURL
     *
     * @param integer $key
     * @param mixed $value
    */
    public function setOption($key, $value)
    {
        curl_setopt($this->ch, $key, $value);
    }

    /**
     * Get lasted error of system
     *
     * @return array
    */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Call cURL from current url
     *
     * @param string $url
     * @param array $fields
     * @throws Exception
     * @return mixed
    */
    public function call($url = '', $fields = [])
    {
        if (empty($url)) {
            throw new Exception("Error Processing Request", 1);
        }
        #Define time
        $this->startTime = $this->getMicroTimeInFloat();
        $this->currentUrl = $url;

        #Set URL and other appropriate options
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        
        #Detect method
        if ($this->method === self::HTTP_PUT) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($this->method === self::HTTP_DELETE) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } else {
            $method = $this->method === self::HTTP_POST ? CURLOPT_POST : CURLOPT_HTTPGET;
            curl_setopt($this->ch, $method , true);
        }

        #Detect params
        if (!empty($fields)) {
            if ($this->usingJson) {
                $postFields = json_encode($fields);
                $this->headers[] = 'Content-Type: application/json';
                $this->headers[] = 'Content-Length: ' . strlen($postFields);
            } else {
                $postFields = http_build_query($fields);
            }

            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postFields);
        }

        #Detect headers
        if ($this->headers) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        #Excecute the given cURL session
        $result = curl_exec($this->ch);
        $this->stopTime = $this->getMicroTimeInFloat();

        if (curl_errno($this->ch) !== CURLE_OK) {
            $this->lastError = [
                'url' => $url,
                'method' => ($this->method === self::HTTP_GET ? 'GET' : 'POST'),
                'fields' => http_build_query($fields),
                'error_code' => curl_errno($this->ch),
                'error_str' => curl_error($this->ch)
            ];
            $result = false;
        } else {
            $this->lastError = null;
        }

        curl_close($this->ch);
        return $result;
    }

    /**
     * Get micro time in float
     * @return float
    **/
    public function getMicroTimeInFloat()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float)$sec);
    }
}