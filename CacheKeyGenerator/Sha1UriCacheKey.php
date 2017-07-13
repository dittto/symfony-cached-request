<?php
namespace Dittto\CachedRequestBundle\CacheKeyGenerator;

use Psr\Http\Message\RequestInterface;

class Sha1UriCacheKey implements CacheKeyInterface
{
    public function getCacheKey(RequestInterface $request, array $options = [])
    {
        return sha1((string) $request->getUri());
    }
}