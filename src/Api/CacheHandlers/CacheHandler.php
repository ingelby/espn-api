<?php

namespace Ingelby\Espn\Api\CacheHandlers;

use Ingelby\Espn\Api\AbstractHandler;
use ingelby\toolbox\helpers\HyperCache;
use ingelby\toolbox\services\inguzzle\exceptions\InguzzleClientException;
use Yii;
use yii\caching\TagDependency;
use yii\web\ServerErrorHttpException;


abstract class CacheHandler
{

    protected int $ttl = 60 * 10;

    /**
     * @var AbstractHandler
     */
    protected AbstractHandler $handler;

    /**
     * @param AbstractHandler $handler
     * @param int|null        $ttl
     */
    public function __construct(AbstractHandler $handler, ?int $ttl = null)
    {
        if (null !== $ttl) {
            $this->ttl = $ttl;
        }

        $this->handler = $handler;
    }

    /**
     * @param       $method
     * @param array $arguments
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function __call($method, array $arguments)
    {
        Yii::info('Calling cache handler with method: ' . $method . ' with ' . implode(', ', $arguments));

        if (!method_exists($this->handler, $method)) {
            Yii::error($method . ' does not exists in class: ' . get_class($this->handler));
            throw new ServerErrorHttpException('Unknown function');
        }

        $cacheKey = get_class($this->handler) . $method . implode($arguments);
        Yii::debug('md5 of ' . $cacheKey);
        $cacheKey = md5($cacheKey);
        Yii::debug('md5 value ' . $cacheKey);

        if (false !== $result = HyperCache::get($cacheKey)) {
            return $result;
        }

        if (false !== $result = Yii::$app->cache->get($cacheKey)) {
            Yii::info('Item in cache');
            HyperCache::set($cacheKey, $result);
            return $result;
        }
        Yii::info('Item not in cache');

        $result = call_user_func_array([$this->handler, $method], $arguments);

        Yii::$app->cache->set(
            $cacheKey,
            $result,
            $this->ttl
        );

        HyperCache::set($cacheKey, $result);

        return $result;
    }
}
