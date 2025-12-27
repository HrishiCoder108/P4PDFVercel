<?php

namespace P4Pdf;

use P4Pdf\Exceptions\DownloadException;
use P4Pdf\Exceptions\ProcessException;
use P4Pdf\Exceptions\SignatureException;
use P4Pdf\Exceptions\StartException;
use P4Pdf\Exceptions\TaskException;
use P4Pdf\Exceptions\UploadException;
use P4Pdf\Exceptions\AuthException;
use P4Pdf\Http\Client;
use P4Pdf\Http\ClientException;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;

/**
 * Class P4Pdf
 * 
 * Main class for P4PDF API integration.
 * Handles authentication, HTTP communication, and task management.
 *
 * @package P4Pdf
 */
class P4Pdf
{
    /**
     * @var string|null The P4PDF secret API key to be used for requests.
     */
    private $secretKey;

    /**
     * @var string|null The P4PDF public API key to be used for requests.
     */
    private $publicKey;

    /**
     * @var string Default API server URL
     */
    private static $startServer = 'https://api.ilovepdf.com';

    /**
     * @var string|null Worker server URL for processing tasks
     */
    private $workerServer;

    /**
     * @var string|null The version of the P4PDF API to use for requests.
     */
    public static $apiVersion = 'v1';

    /**
     * Library version
     */
    const VERSION = 'php.1.3.0';

    /**
     * @var string|null JWT authentication token
     */
    public $token;

    /**
     * @var int Delay in seconds for timezone exceptions.
     * Time should be UTC, but some servers may not be using NTP.
     * This variable corrects the delay. Default: 5400 seconds (1h 30m)
     */
    public $timeDelay = 5400;

    /**
     * @var bool Whether file encryption is enabled
     */
    private $encrypted = false;

    /**
     * @var string|null Encryption key for file encryption
     */
    private $encryptKey;

    /**
     * @var int Default timeout in seconds for API requests
     */
    public $timeout = 10;

    /**
     * @var int|null Timeout for large operations (upload, download, process)
     */
    public $timeoutLarge;

    /**
     * @var mixed|null API info response cache
     */
    public $info;

    /**
     * P4Pdf constructor.
     *
     * @param string|null $publicKey Public API key
     * @param string|null $secretKey Secret API key
     */
    public function __construct(?string $publicKey = null, ?string $secretKey = null)
    {
        if ($publicKey && $secretKey) {
            $this->setApiKeys($publicKey, $secretKey);
        }
    }

    /**
     * Get the API secret key used for requests.
     *
     * @return string The secret key
     */
    public function getSecretKey(): string
    {
        return $this->secretKey ?? '';
    }

    /**
     * Get the API public key used for requests.
     *
     * @return string The public key
     */
    public function getPublicKey(): string
    {
        return $this->publicKey ?? '';
    }

    /**
     * Set the API keys to be used for requests.
     *
     * @param string $publicKey Public API key
     * @param string $secretKey Secret API key
     * @return void
     */
    public function setApiKeys(string $publicKey, string $secretKey): void
    {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
    }

    /**
     * Get the API version used for requests.
     *
     * @return string|null The API version, null if using the latest version
     */
    public static function getApiVersion(): ?string
    {
        return self::$apiVersion;
    }

    /**
     * Set the API version to use for requests.
     *
     * @param string $apiVersion The API version
     * @return void
     */
    public static function setApiVersion($apiVersion): void
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * Generate and return JWT token to be used for API requests.
     *
     * @return string The JWT token
     */
    public function getJWT(): string
    {
        $secret = $this->getSecretKey();
        $currentTime = time();

        // Build token payload
        $token = [
            'iss' => '',
            'aud' => '',
            'iat' => $currentTime - $this->timeDelay,
            'nbf' => $currentTime - $this->timeDelay,
            'exp' => $currentTime + 3600 + $this->timeDelay,
            'jti' => $this->getPublicKey()
        ];

        // Add encryption key if file encryption is enabled
        if ($this->isFileEncryption()) {
            $token['file_encryption_key'] = $this->getEncryptKey();
        }

        $this->token = JWT::encode($token, $secret, static::getTokenAlgorithm());

        return $this->token;
    }

    /**
     * Get the algorithm used for JWT token encoding.
     *
     * @return string The algorithm name
     */
    public static function getTokenAlgorithm(): string
    {
        return 'HS256';
    }

    /**
     * Send HTTP request to the API server.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param bool $start Whether this is a start request
     *
     * @return ResponseInterface Response from server
     *
     * @throws AuthException
     * @throws ProcessException
     * @throws UploadException
     */
    public function sendRequest(string $method, string $endpoint, array $params = [], bool $start = false): ResponseInterface
    {
        $toServer = self::getStartServer();
        if (!$start && !is_null($this->getWorkerServer())) {
            $toServer = $this->workerServer;
        }

        // Determine timeout based on endpoint type
        $isLargeOperation = in_array($endpoint, ['process', 'upload']) || strpos($endpoint, 'download/') === 0;
        $timeout = $isLargeOperation ? $this->timeoutLarge : $this->timeout;

        $requestConfig = [
            'connect_timeout' => $timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getJWT(),
                'Accept' => 'application/json'
            ],
        ];

        $requestParams = $params ? array_merge($requestConfig, $params) : $requestConfig;

        $client = new Client($params);

        try {
            $response = $client->request($method, $toServer . '/v1/' . $endpoint, $requestParams);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        $responseCode = $response->getStatusCode();

        // Handle error responses
        if ($responseCode != 200 && $responseCode != 201) {
            $responseBody = json_decode((string)$response->getBody());
            $this->handleErrorResponse($responseCode, $endpoint, $responseBody, $response);
        }

        return $response;
    }

    /**
     * Handle error responses from API.
     *
     * @param int $responseCode HTTP response code
     * @param string $endpoint API endpoint
     * @param mixed $responseBody Decoded response body
     * @param ResponseInterface $response Full response object
     * @return void
     * @throws AuthException|ProcessException|UploadException|DownloadException|StartException|TaskException|SignatureException|\Exception
     */
    private function handleErrorResponse(int $responseCode, string $endpoint, $responseBody, ResponseInterface $response): void
    {
        if ($responseCode == 401) {
            throw new AuthException($responseBody->name ?? 'Unauthorized', $responseBody, $responseCode);
        }

        if ($endpoint == 'upload') {
            $message = is_string($responseBody) ? $responseBody : $responseBody->error->message;
            throw new UploadException("Upload error: " . $message, $responseBody, $responseCode);
        }

        if ($endpoint == 'process') {
            throw new ProcessException($responseBody->error->message, $responseBody, $responseCode);
        }

        if (strpos($endpoint, 'download') === 0) {
            throw new DownloadException($responseBody->error->message, $responseBody, $responseCode);
        }

        if (strpos($endpoint, 'start') === 0) {
            if (isset($responseBody->error) && isset($responseBody->error->type)) {
                throw new StartException($responseBody->error->message, $responseBody, $responseCode);
            }
            throw new StartException('Bad Request', $responseBody, $responseCode);
        }

        if ($responseCode == 429) {
            throw new \Exception('Too Many Requests');
        }

        if ($responseCode == 400) {
            if (strpos($endpoint, 'task') !== false) {
                throw new TaskException('Invalid task id', $responseBody, $responseCode);
            }

            if (strpos($endpoint, 'signature') !== false) {
                throw new SignatureException($responseBody->error->type ?? 'Signature error', $responseBody, $responseCode);
            }

            $message = $responseBody->error->message ?? 'Bad Request';
            throw new \Exception($message);
        }

        $message = $responseBody->error->message ?? 'Bad Request';
        throw new \Exception($message);
    }

    /**
     * Create a new task instance for the specified tool.
     *
     * @param string $tool API tool to use (e.g., 'compress', 'merge', 'split')
     * @param bool|null $makeStart Set to false for chained tasks (no start call needed)
     *
     * @return Task Return implemented Task class for specified tool
     *
     * @throws \InvalidArgumentException
     */
    public function newTask(string $tool = '', ?bool $makeStart = true)
    {
        if ($tool == '') {
            $makeStart = false;
        }

        $className = '\\P4Pdf\\' . ucwords(strtolower($tool)) . 'Task';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Invalid tool: {$tool}");
        }

        return new $className($this->getPublicKey(), $this->getSecretKey(), $makeStart);
    }

    /**
     * Set the API server URL.
     *
     * @param string $server Server URL
     * @return void
     */
    public static function setStartServer(string $server): void
    {
        self::$startServer = $server;
    }

    /**
     * Get the API server URL.
     *
     * @return string Server URL
     */
    public static function getStartServer(): string
    {
        return self::$startServer;
    }

    /**
     * Get the worker server URL.
     *
     * @return string|null Worker server URL
     */
    public function getWorkerServer(): ?string
    {
        return $this->workerServer;
    }

    /**
     * Set the worker server URL.
     *
     * @param string|null $workerServer Worker server URL
     * @return void
     */
    public function setWorkerServer(?string $workerServer): void
    {
        $this->workerServer = $workerServer;
    }

    /**
     * Enable file encryption with optional custom key.
     *
     * @param string|null $encryptKey Custom encryption key (16, 24, or 32 characters)
     * @return $this
     */
    public function setFileEncryption(?string $encryptKey = null)
    {
        $this->setEncryption($encryptKey == null);
        $this->setEncryptKey($encryptKey);

        return $this;
    }

    /**
     * Enable or disable file encryption.
     *
     * @param bool $enable Whether to enable encryption
     * @return void
     */
    public function enableEncryption(bool $enable): void
    {
        $this->encrypted = $enable;
    }

    /**
     * @deprecated Use enableEncryption() instead
     * Set encryption enabled/disabled (legacy method).
     *
     * @param bool $enable Whether to enable encryption
     * @return void
     */
    public function setEncryption(bool $enable): void
    {
        $this->enableEncryption($enable);
    }

    /**
     * Check if file encryption is enabled.
     *
     * @return bool True if encryption is enabled
     */
    public function isFileEncryption(): bool
    {
        return $this->encrypted;
    }

    /**
     * Get the encryption key.
     *
     * @return string|null The encryption key
     */
    public function getEncryptKey(): ?string
    {
        return $this->encryptKey;
    }

    /**
     * Set the encryption key.
     *
     * @param string|null $encryptKey Encryption key (16, 24, or 32 characters). If null, generates a random key.
     * @return void
     * @throws \InvalidArgumentException If key length is invalid
     */
    public function setEncryptKey(?string $encryptKey = null): void
    {
        if ($encryptKey == null) {
            $encryptKey = P4PdfTool::rand_sha1(32);
        }

        $length = strlen($encryptKey);
        if (!in_array($length, [16, 24, 32])) {
            throw new \InvalidArgumentException('Encryption key must be 16, 24, or 32 characters in length');
        }

        $this->encryptKey = $encryptKey;
    }

    /**
     * Get task status from the API.
     *
     * @param string $server Worker server URL
     * @param string $taskId Task ID
     * @return mixed Decoded JSON response
     * @throws AuthException
     * @throws ProcessException
     * @throws UploadException
     */
    public function getStatus(string $server, string $taskId)
    {
        $originalWorkerServer = $this->getWorkerServer();
        $this->setWorkerServer($server);
        
        try {
            $response = $this->sendRequest('get', 'task/' . $taskId);
            $result = json_decode($response->getBody());
        } finally {
            $this->setWorkerServer($originalWorkerServer);
        }

        return $result;
    }

    /**
     * Set SSL verification for HTTP client.
     *
     * @param bool $verify Whether to verify SSL certificates
     * @return void
     */
    public function verifySsl(bool $verify): void
    {
        Client::setVerify($verify);
    }

    /**
     * Set whether to follow HTTP redirects.
     *
     * @param bool $follow Whether to follow redirects
     * @return void
     */
    public function followLocation(bool $follow): void
    {
        Client::setAllowRedirects($follow);
    }

    /**
     * Get updated API information from the server.
     *
     * @return object API info object
     * @throws AuthException
     * @throws ProcessException
     * @throws UploadException
     */
    private function getUpdatedInfo(): object
    {
        $data = ['v' => self::VERSION];
        $body = ['form_params' => $data];
        $response = $this->sendRequest('get', 'info', $body);
        $this->info = json_decode($response->getBody());
        
        return $this->info;
    }

    /**
     * Get API information.
     *
     * @return object API info object
     */
    public function getInfo(): object
    {
        return $this->getUpdatedInfo();
    }

    /**
     * @deprecated Use getRemainingCredits() instead
     * Get remaining file processing credits (legacy method).
     *
     * @return int Remaining files (credits / 10)
     */
    public function getRemainingFiles(): int
    {
        $info = $this->getUpdatedInfo();
        return (int)($info->remaining_credits / 10);
    }

    /**
     * Get remaining processing credits.
     *
     * @return int Remaining credits
     */
    public function getRemainingCredits(): int
    {
        $info = $this->getUpdatedInfo();
        return $info->remaining_credits;
    }
}
