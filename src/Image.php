<?php

namespace Hyqo\ImageProxy;

class Image implements \JsonSerializable
{
    public $origin;

    public $namespace;

    public $id;

    public $alpha;

    public $blur;

    public $dimensions;

    public function __construct(array $data, ?Dimensions $dimensions)
    {
        $this->origin = $data['origin'] ?? null;
        $this->namespace = $data['namespace'] ?? null;
        $this->id = $data['id'] ?? null;
        $this->alpha = $data['alpha'] ?? false;
        $this->blur = $data['blur'] ?? null;
        $this->dimensions = $dimensions;
    }

    public function getUrl(?int $width = null, ?int $height = null, ?int $scale = null, ?Format $format = null): string
    {
        if (null === $format) {
            $format = $this->alpha ? Format::PNG() : Format::JPEG();
        }

        $unsigned = "n{$this->namespace}-id{$this->id}";
        $unsigned .= "-w{$width}-h{$height}-s{$scale}.{$format->value}";

        $signature = md5("{$unsigned}-" . ImageProxy::getSignature());

        $name = $this->id;

        if ($width || $height) {
            $name .= "-{$width}x{$height}";
        }

        if ($scale) {
            $name .= "@{$scale}x";
        }

        return sprintf(
            "https://%s/%s/%s/%s.%s",
            $this->origin,
            $this->namespace,
            $signature,
            $name,
            $format->value
        );
    }

    public function jsonSerialize()
    {
        return $this;
    }

    public function picture(?int $width = null, ?int $height = null): Picture
    {
        return new Picture($this, $width, $height);
    }
}
