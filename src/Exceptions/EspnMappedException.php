<?php

namespace Ingelby\Espn\Exceptions;

use ingelby\toolbox\constants\HttpStatus;
use yii\web\HttpException;

class EspnMappedException extends BaseEspnException
{
    protected array $espnError = [];

    /**
     * @param array $espnError
     * @param null  $previous
     */
    public function __construct(array $espnError, $previous = null)
    {
        $this->espnError = $espnError;
        $message = 'Unknown';
        if (array_key_exists('error', $espnError)) {
            $message = $espnError['error'];
        }
        parent::__construct(HttpStatus::BAD_REQUEST, $message, 0, $previous);
    }

    /**
     * @return array
     */
    public function getEspnError()
    {
        return $this->espnError;
    }
}
