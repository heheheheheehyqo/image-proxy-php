<?php

namespace Hyqo\ImageProxy;

interface ClientInterface
{
    public function __construct(string $domain, string $token);

    /**
     * @throws ImageProxyException
     */
    public function doRequest(string $method, array $data): array;
}
