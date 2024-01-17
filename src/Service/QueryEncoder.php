<?php

namespace HBM\DatagridBundle\Service;

class QueryEncoder
{
    private string $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return null|array|mixed
     */
    public function getQueryParams(?string $var, string $mode = null): mixed
    {
        $modes      = explode('+', $mode ?: $this->mode);
        $modeQuery  = $modes[0] ?? null;
        $modeParams = $modes[1] ?? null;

        $queryParams = [];

        // DECODE QUERY
        switch ($modeQuery) {
            case 'json':
                try {
                    $queryParams = json_decode($var, true, 512, JSON_THROW_ON_ERROR) ?? [];
                } catch (\JsonException) {
                    $queryParams = [];
                }

                break;

            case 'query':
                $url         = parse_url('?' . $var);
                $queryString = urldecode($url['query'] ?? '');
                $queryString = ($queryString === '-') ? '' : $queryString;
                parse_str($queryString, $queryParams);

                break;

            case 'base64':
                $queryParams = base64_decode($var);

                break;
        }

        // DECODE PARAMS
        switch ($modeParams) {
            case 'base64':
                array_walk_recursive($queryParams, static function (&$item) {
                    $item = base64_decode($item, true);
                });

                return $queryParams;

            case 'urlencode':
                array_walk_recursive($queryParams, static function (&$item) {
                    $item = rawurldecode(rawurldecode($item));
                });

                return $queryParams;

            case 'json':
                try {
                    return json_decode($queryParams, true, 512, JSON_THROW_ON_ERROR) ?? [];
                } catch (\JsonException) {
                    return [];
                }

            default:
                return $queryParams;
        }
    }

    /**
     * @param mixed $vars
     * @param string|null $mode
     *
     * @return false|string
     */
    public function getQueryString(mixed $vars, string $mode = null): false|string
    {
        $modes      = explode('+', $mode ?: $this->mode);
        $modeQuery  = $modes[0] ?? null;
        $modeParams = $modes[1] ?? null;

        // ENCODE PARAMS
        switch ($modeParams) {
            case 'base64':
                array_walk_recursive($vars, static function (&$item) {
                    $item = base64_encode($item);
                });

                break;

            case 'urlencode':
                array_walk_recursive($vars, static function (&$item) {
                    $item = rawurlencode(rawurlencode($item));
                });

                break;

            case 'json':
                try {
                    $vars = json_encode($vars, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $vars = '';
                }

                break;
        }

        // ENCODE QUERY
        switch ($modeQuery) {
            case 'json':
                try {
                    return json_encode($vars, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    return '';
                }

            case 'query':
                return http_build_query($vars) ?: '-';

            case 'base64':
                return base64_encode($vars);
        }

        throw new \InvalidArgumentException('No matching query encoder found for "' . $modeQuery . '".');
    }
}
