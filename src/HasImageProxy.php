<?php

namespace Hyqo\ImageProxy;

interface HasImageProxy
{
    public function getNamespace(): string;

    public function getReferenceId(): ?string;
}
