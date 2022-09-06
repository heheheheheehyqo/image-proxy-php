<?php

namespace Hyqo\ImageProxy;

class Picture
{
    protected $image;
    protected $width;
    protected $height;

    protected $breakpoints = [];

    protected $attributes = [];

    public function __construct(Image $image, ?int $width = null, ?int $height = null)
    {
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function lazy(): self
    {
        $this->attributes['loading'] = 'lazy';

        return $this;
    }

    public function alt(string $string): self
    {
        $this->attributes['alt'] = $string;

        return $this;
    }

    public function highPriority(): self
    {
        $this->attributes['fetchpriority'] = 'hight';

        return $this;
    }

    public function blur(): self
    {
        $this->attributes['style'] =
            'background: url(data:image/jpeg;base64,'.$this->image->blur.') center; background-size: cover';

        return $this;
    }

    public function class(string $class): self
    {
        $this->attributes['class'] = $class;

        return $this;
    }

    public function responsive(array $breakpoints = [400 => 400, 700 => 700]): self
    {
        $this->breakpoints = $breakpoints;

        return $this;
    }

    protected function calculateBreakpoints(): array
    {
        $result = [];

        if (null === $this->width) {
            return [];
        }

        foreach ($this->breakpoints as $breakpoint => $width) {
            if ($this->width > $breakpoint) {
                $result[$breakpoint] = $width;
            }
        }

        return $result;
    }

    protected function render(): string
    {
        if ($breakpoints = $this->calculateBreakpoints()) {
            return $this->renderViewportAware($breakpoints);
        }

        return $this->renderDPRAware();
    }

    protected function renderAttributes(): string
    {
        $string = '';

        if (!$this->attributes) {
            return $string;
        }


        foreach ($this->attributes as $name => $value) {
            $string .= " $name=\"$value\"";
        }

        return $string;
    }

    protected function renderViewportAware(array $breakpoints): string
    {
        $html = '';

        $height = $this->height;

        foreach (array_merge(ImageProxy::getAvailableFormats(), [null]) as $format) {
            $srcset = [];
            $sizes = [];

            foreach ($breakpoints as $breakpoint => $width) {
                $srcset[] = $this->image->getUrl($width, $height, 1, $format) . sprintf(' %dw', $width);
                $srcset[] = $this->image->getUrl($width, $height, 2, $format) . sprintf(' %dw', $width * 2);

                $sizes[] = sprintf('(max-width: %dpx) %dpx', $breakpoint, $width);
            }

            $srcset[] = sprintf('%s %dw', $this->image->getUrl($this->width, $this->height, 1, $format), $this->width);
            $srcset[] = sprintf(
                '%s %dw',
                $this->image->getUrl($this->width, $this->height, 2, $format),
                $this->width * 2
            );

            $sizes[] = sprintf('%dpx', $this->width);

            if (null === $format) {
                $html .= '<img srcset="' . implode(', ', $srcset) . '" sizes="' . implode(', ', $sizes) . '"';

                if ($height) {
                    $html .= ' height="' . $height . '"';
                }

                $html .= $this->renderAttributes() . '>';
            } else {
                $html .= '<source srcset="' . implode(', ', $srcset) . '" sizes="' . implode(', ', $sizes) . '">';
            }
        }

        return sprintf('<picture>%s</picture>', $html);
    }

    protected function renderDPRAware(): string
    {
        $html = '';

        foreach (array_merge(ImageProxy::getAvailableFormats(), [null]) as $format) {
            $srcset = [];

            foreach ([1, 2] as $scale) {
                $srcset[] = $this->image->getUrl($this->width, $this->height, $scale, $format) . " {$scale}x";
            }

            if (null === $format) {
                $html .= '<img srcset="' . implode(', ', $srcset) . '"';

                if ($this->height) {
                    $html .= ' height="' . $this->height . '"';
                }

                $html .= $this->renderAttributes() . '>';
            } else {
                $html .= sprintf(
                    '<source type="image/%s" srcset="%s">',
                    $format->value,
                    implode(', ', $srcset)
                );
            }
        }

        return sprintf('<picture>%s</picture>', $html);
    }
}
