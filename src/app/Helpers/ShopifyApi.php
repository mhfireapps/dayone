<?php

namespace App\Helpers;

use Closure;
use Exception;
use Psr\Log\LogLevel;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

class ShopifyApi implements LoggerAwareInterface
{
	protected $client;

	/**
	 * The Shopify domain
	 * @var string
	 */
	protected $shop;

	/**
	 * The Shopify access token
	 * @var [type]
	 */
	protected $accessToken;

	/**
	 * The shopify API key
	 * @var [type]
	 */
	protected $apiKey;

	/**
	 * If API calls are from a public or private app
	 * @var [type]
	 */
	protected $private;

	/**
	 * The logger
	 * @var LoggerInterface
	 */
	protected $logger;

	public function __construct(bool $private = false)
	{
		// Check it later
		$this->private = $private;

		// Create the stack and assign the middleware which attempts to fix redirects
        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest([$this, 'authRequest']));

        $this->client = new Client([
            'handler'  => $stack,
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        return $this;
	}

	public function setShop(string $shop)
	{
		$this->shop = $shop;
	}

	public function getShop(): ?string
	{
		return $this->shop;
	}

	/**
	 * Set the access token for use  with the Shopify API
	 * @param string $accessToken the access token
	 * @return  self
	 */
	public function setAccessToken(string $accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * get Access token
	 * @return string|null
	 */
	public function getAccessToken(): ?string
	{
		return $this->accessToken;
	}

	public function setApiKey(string $apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function setApiSecret(string $apiSecret)
	{
		$this->apiSecret = $apiSecret;
	}

	public function setSession(string $shop, string $accessToken)
	{
		$this->setShop($shop);
		$this->setAccessToken($accessToken);
	}

	/**
	 * Accepts a closure to do isolated API calls for a shop
	 * @param  string   $shop        [description]
	 * @param  string   $accessToken [description]
	 * @param  Closurse $closure     [description]
	 * @throws Exception When closure is missing or not callable
	 * @return mixed
	 */
	public function withSession(string $shop, string $accessToken, Closurse $closure)
	{
		$this->log('WithSession started for ' . $shop);

		$cloneApi = clone $this;
		$cloneApi->setSession($shop, $accessToken);

		return $closure->call($cloneApi);
	}

	public function getBaseUri(): Uri
	{
		if ($this->shop === null) {
			throw new Exception('Shopify domain missing for API calls');
		}

		return new Uri('https://' . $this->shop);
	}

	/**
     * Verify the request is from Shopify using the HMAC signature
     *
     * @param array $params The request parameters
     * @throws Exception for missing API secret.
     * @return bool If the HMAC is validated.
     */
    public function verifyRequest(array $params): bool
    {
        if ($this->apiSecret === null) {
            throw new Exception('API secret is missing');
        }

        if (isset($params['shop'])
            && isset($params['timestamp'])
            && isset($params['hmac'])
        ) {
            $hmac = $params['hmac'];
            unset($params['hmac']);
            ksort($params);

            return $hmac === hash_hmac('sha256', urldecode(http_build_query($params)), $this->apiSecret);
        }

        return false;
    }

    /**
     * Gets the access object from a "code" supplied by Shopify request after successfull auth (for public apps).
     *
     * @param string $code The code from Shopify.
     * @throws Exception When API secret is missing.
     * @return array The access object.
     */
    public function requestAccess(string $code)
    {
    	if ($this->apiSecret === null || $this->apiKey === null) {
            throw new Exception('API key or secret is missing');
        }

        $request = $this->client->request(
            'POST',
            $this->getBaseUri()->withPath('/admin/oauth/access_token'),
            [
                'json' => [
                    'client_id'     => $this->apiKey,
                    'client_secret' => $this->apiSecret,
                    'code'          => $code,
                ],
            ]
        );

        // Decode the response body
        $body = json_decode($request->getBody(), true);
        $this->log('RequestAccess response: '.json_encode($body));

        return $body;
    }

    /**
     * Ensures we have the proper request for private and public calls.
     * Also modifies issues with redirects.
     *
     * @param Request $request The request object.
     *
     * @throws Exception for missing API key or password for private apps.
     * @throws Exception for missing access token on GraphQL calls.
     *
     * @return void
     */
    public function authRequest(Request $request): Request
    {
        $uri = $request->getUri();
        if ( $this->isAuthableRequest((string) $uri) ) {
            if ( $this->isRestRequest((string) $uri) ) {
                if ( $this->private && ($this->apiKey === null || $this->apiPassword === null) ) {
                    throw new Exception('API key and password required for private Shopify REST calls');
                }

                if ( $this->private ) {
                    return $request->withHeader(
                        'Authorization',
                        'Basic '.base64_encode("{$this->apiKey}:{$this->apiPassword}")
                    );
                }

                // Public: Add the token header
                return $request->withHeader('X-Shopify-Access-Token', $this->accessToken);
            } else {
                if ( $this->private && ($this->apiPassword === null && $this->accessToken === null) ) {
                    throw new Exception('API password/access token required for private Shopify GraphQL calls');
                } elseif ( !$this->private && $this->accessToken === null ) {
                    throw new Exception('Access token required for public Shopify GraphQL calls');
                }

                // Public/Private: Add the token header
                return $request->withHeader(
                    'X-Shopify-Access-Token',
                    $this->apiPassword ?? $this->accessToken
                );
            }
        }

        return $request;
    }

    /**
     * Determines if the request is to Graph API.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isGraphRequest(string $uri): bool
    {
        return strpos($uri, 'graphql.json') !== false;
    }

    /**
     * Determines if the request is to REST API.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isRestRequest(string $uri): bool
    {
        return $this->isGraphRequest($uri) === false;
    }

    /**
     * Determines if the request requires auth headers.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isAuthableRequest(string $uri): bool
    {
        return preg_match('/\/admin\/oauth\/(authorize|access_token)/', $uri) === 0;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger The logger instance.
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a message to the logger.
     *
     * @param string $msg   The message to send.
     * @param int    $level The level of message.
     *
     * @return bool
     */
    public function log(string $msg, string $level = LogLevel::DEBUG): bool
    {
        if ($this->logger === null) {
            return false;
        }

        call_user_func([$this->logger, $level], self::LOG_KEY.' '.$msg);
        return true;
    }

    /**
     * Decodes the JSON body.
     * @param string $json The JSON body.
     * @return stdClass The decoded JSON.
     */
    protected function jsonDecode($json): stdClass
    {
        // From firebase/php-jwt
        if (!(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $obj = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $maxIntLength = strlen((string) PHP_INT_MAX) - 1;
            $jsonWithoutBigints = preg_replace('/:\s*(-?\d{'.$maxIntLength.',})/', ': "$1"', $json);
            $obj = json_decode($jsonWithoutBigints);
        }

        return $obj;
    }
}
