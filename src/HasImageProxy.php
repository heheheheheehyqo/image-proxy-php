<?php

namespace Hyqo\ImageProxy;

interface HasImageProxy
{
    public function getNamespace(): string;

    public function getReferenceId(): ?string;

    public function getImageProxy(): ?Image;

    public function getImageDimensions(): ?Dimensions;
}
