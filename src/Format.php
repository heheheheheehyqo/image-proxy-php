<?php

namespace Hyqo\ImageProxy;

use Hyqo\Enum\Enum;

/**
 * @method static JPEG()
 * @method static PNG()
 * @method static WEBP()
 * @method static AVIF()
 */
class Format extends Enum
{
    public const JPEG = 'jpeg';
    public const PNG = 'png';
    public const WEBP = 'webp';
    public const AVIF = 'avif';
}
