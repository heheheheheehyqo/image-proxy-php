<?php

namespace Hyqo\ImageProxy;

class Client implements ClientInterface
{
    protected $domain;

    protected $token;

    public function __construct(string $domain, string $token)
    {
        $this->domain = $domain;
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function doRequest(string $method, array $data): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://%s/%s', $this->domain, $method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->token]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

//        var_dump($result);die();
        if ($result) {
            $response = json_decode($result, true);

            if ($response['success'] ?? false) {
                return $response['data'];
            }

            if ($response['message'] ?? false) {
                throw  new ImageProxyException($response['message']);
            }
        }

        throw new ImageProxyException('Something went wrong');
    }
}
