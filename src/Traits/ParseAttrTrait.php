<?php

namespace HBM\DatagridBundle\Traits;

use HBM\TwigAttributesBundle\Utils\HtmlAttributes;

trait ParseAttrTrait
{
    protected function parseAttr(HtmlAttributes $htmlAttributes, $attributes, array $callbackParams = []): HtmlAttributes
    {
        if (is_callable($attributes)) {
            $htmlAttributes->add($attributes(...$callbackParams));
        }

        if (is_string($attributes) || is_array($attributes) || ($attributes instanceof HtmlAttributes)) {
            $htmlAttributes->add($attributes);
        }

        return $htmlAttributes;
    }
}
