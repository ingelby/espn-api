<?php

namespace Ingelby\Espn\Api;

use common\helpers\SessionGuid;
use Ingelby\Espn\Exceptions\EspnClientException;
use Ingelby\Espn\Exceptions\EspnConfigurationException;
use Ingelby\Espn\Exceptions\EspnMappedException;
use Ingelby\Espn\Exceptions\EspnServerException;
use ingelby\toolbox\constants\HttpStatus;
use ingelby\toolbox\helpers\LoggingHelper;
use ingelby\toolbox\services\inguzzle\exceptions\InguzzleClientException;
use ingelby\toolbox\services\inguzzle\exceptions\InguzzleInternalServerException;
use ingelby\toolbox\services\inguzzle\exceptions\InguzzleServerException;
use ingelby\toolbox\services\inguzzle\InguzzleHandler;

abstract class AbstractHandler extends InguzzleHandler
{
    protected const DEFAULT_TIMEOUT = 10;

    protected ?string $baseUrl;
    protected ?string $apiKey;
    protected string $routeUri = '';
    protected int $cacheTimeout = 600;

    /**
     * @param string[] $espnConfig
     * @param array    $clientConfig
     * @throws EspnConfigurationException
     */
    public function __construct(array $espnConfig = [], array $clientConfig = [])
    {
        if (!array_key_exists('baseUrl', $espnConfig)) {
            throw new EspnConfigurationException('Missing baseUrl');
        }
        if (!array_key_exists('apiKey', $espnConfig)) {
            throw new EspnConfigurationException('Missing apiKey');
        }

        $this->baseUrl = $espnConfig['baseUrl'];
        $this->apiKey = $espnConfig['apiKey'];

        $defaultClientConfig = [
            'timeout' => self::DEFAULT_TIMEOUT,
        ];

        parent::__construct(
            $this->baseUrl,
            '',
            null,
            null,
            array_merge($defaultClientConfig, $clientConfig)
        );
    }


    /**
     * @param string $uri
     * @param array  $queryParameters
     * @param array  $additionalHeaders
     * @return array
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function get($uri, array $queryParameters = [], array $additionalHeaders = [])
    {
        \Yii::info('Calling: ' . $uri);
        $defaultQueryParemeters = [
            'client' => $this->apiKey,
        ];

        try {
            $response = parent::get(
                $uri,
                array_merge($defaultQueryParemeters, $queryParameters),
                $additionalHeaders
            );
        } catch (InguzzleClientException $exception) {
            throw new EspnClientException($exception->statusCode, $exception->getMessage(), 0, $exception);
        } catch (InguzzleServerException | InguzzleInternalServerException $exception) {
            LoggingHelper::logException($exception);
            throw new EspnServerException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'Unable to contact ESPN: ' . SessionGuid::getShort(),
                0,
                $exception
            );
        }

        $this->responseValidator($response);
        return $response;
    }


    /**
     * @param array $response
     * @return array
     * @throws EspnMappedException
     */
    private function responseValidator(array $response)
    {
        if (!array_key_exists('error', $response)) {
            return $response;
        }

        //@Todo, Map error codes and categories, for now keep it simple stupid...
        throw new EspnMappedException($response);
    }
}
