<?php
namespace Dittto\CachedRequestBundle\GuzzleMiddleware;

use Dittto\CachedRequestBundle\CacheKeyGenerator\CacheKeyInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\{
    RequestInterface, ResponseInterface
};
use Psr\SimpleCache\CacheInterface;

class CachedMiddleware
{
    public const CACHE_TIME_IN_S = 'cache_time';
    private const DEFAULT_CACHE_TIME = 5;

    public function onRequest(CacheInterface $cache, CacheKeyInterface $keyGenerator, int $defaultCacheTime = self::DEFAULT_CACHE_TIME)
    {
        return function (callable $handler) use ($cache, $keyGenerator, $defaultCacheTime) {
            return function (RequestInterface $request, array $options) use ($handler, $cache, $keyGenerator, $defaultCacheTime) {

                $cacheKey = $keyGenerator->getCacheKey($request, $options);

                if ($cachedResponse = $cache->get($cacheKey)) {
                    return new FulfilledPromise($cachedResponse);
                }

                $cacheTime = $options[self::CACHE_TIME_IN_S] ?? $defaultCacheTime;

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $cache, $cacheKey, $cacheTime) {

                        $cache->set($cacheKey, $response, $cacheTime);

                        return $response;
                    },
                    function (TransferException $e) use ($request, $cache, $cacheKey, $cacheTime) {
                        return new RejectedPromise($e);
                    }
                );
            };
        };
    }
}
