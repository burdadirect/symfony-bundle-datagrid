<?php

namespace HBM\DatagridBundle\Service;

class QueryEncoder
{

    /**
     * @return null|array|mixed
     */
    public function getQueryParams(?string $var): mixed
    {
        // DECODE QUERY
        try {
            $queryParams = json_decode($var, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException) {
            $queryParams = [];
        }

        // DECODE PARAMS
        array_walk_recursive($queryParams, static function (&$item) {
            $item = rawurldecode(rawurldecode($item));
        });

        return $queryParams;
    }

    /**
     * @param mixed $vars
     *
     * @return false|string
     */
    public function getQueryString(mixed $vars): false|string
    {
        // ENCODE PARAMS
        array_walk_recursive($vars, static function (&$item) {
            $item = rawurlencode(rawurlencode($item));
        });

        // ENCODE QUERY
        try {
            return json_encode($vars, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '';
        }
    }
}
