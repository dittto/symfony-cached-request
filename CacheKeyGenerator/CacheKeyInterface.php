<?php
namespace Dittto\CachedRequestBundle\CacheKeyGenerator;

use Psr\Http\Message\RequestInterface;

interface CacheKeyInterface
{
    public function getCacheKey(RequestInterface $request, array $options = []);
}