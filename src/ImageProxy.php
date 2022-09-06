<?php

namespace Hyqo\ImageProxy;

class ImageProxy
{
    protected static $sizeLimit = 5000000;
    protected static $sign;

    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public static function setSizeLimit(int $bites): void
    {
        self::$sizeLimit = $bites;
    }

    public static function setSignature(string $sign): void
    {
        self::$sign = $sign;
    }

    public static function getSignature(): string
    {
        if (null === self::$sign) {
            throw new ImageProxySignatureException('Undefined signature');
        }

        return self::$sign;
    }

    public static function getAvailableFormats(): array
    {
        return [Format::AVIF(), Format::WEBP()];
    }

    /**
     * @throws ImageProxyException
     */
    public function upload(string $namespace, string $filename, ?string $referenceId = null): Image
    {
        if (!file_exists($filename)) {
            throw new ImageProxyException("File $filename does not exist");
        }

        $mime = (new \finfo())->file($filename, FILEINFO_MIME_TYPE);

        if (!$mime || !in_array($mime, ['image/jpeg', 'image/png'])) {
            throw new ImageProxyException(sprintf('Unsupported MIME type: %s', $mime ?: 'unknown'));
        }

        if (filesize($filename) > self::$sizeLimit) {
            if (self::$sizeLimit > 1e6) {
                $prettifySizeLimit = sprintf("%dMB", round(self::$sizeLimit / 1e6));
            } elseif (self::$sizeLimit > 1e3) {
                $prettifySizeLimit = sprintf("%dKB", round(self::$sizeLimit / 1e3));
            } else {
                $prettifySizeLimit = sprintf("%dB", self::$sizeLimit);
            }

            throw new ImageProxyException(
                sprintf('File "%s" is too big: max size is %s', $filename, $prettifySizeLimit)
            );
        }

        $data = [
            'namespace' => $namespace,
            'image' => curl_file_create($filename),
        ];

        if (null !== $referenceId) {
            $data['referenceId'] = $referenceId;
        }

        return $this->doRequest('upload', $data);
    }

    /**
     * @throws ImageProxyException
     */
    public function upgrade(Image $image, ?string $referenceId = null): Image
    {
        $data = [
            'data' => json_encode($image)
        ];

        if (null !== $referenceId) {
            $data['referenceId'] = $referenceId;
        }

        return $this->doRequest('upgrade', $data);
    }

    /**
     * @throws ImageProxyException
     */
    protected function doRequest(string $method, array $data): Image
    {
        $imageData = $this->client->doRequest($method, $data);

        return new Image($imageData);
    }

}
