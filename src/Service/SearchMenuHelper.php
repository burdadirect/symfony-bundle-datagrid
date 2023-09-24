<?php

namespace HBM\DatagridBundle\Service;

use Doctrine\ORM\EntityRepository;

class SearchMenuHelper
{
    public function flags(?string $minus = 'nein', ?string $plus = 'ja', string $zero = null): array
    {
        $flags = [];

        if ($minus !== null) {
            $flags['flag_-1'] = $minus;
        }

        if ($zero !== null) {
            $flags['flag_0'] = $zero;
        }

        if ($plus !== null) {
            $flags['flag_1'] = $plus;
        }

        return $flags;
    }

    public function flags0(): array
    {
        return $this->flags(null, 'ja', 'nein');
    }

    public function tokens(array $searchValues, string $key, string $type = null, string $prefix = null): array
    {
        $values = [];

        if (isset($searchValues[$key])) {
            $values = array_diff(array_map('trim', explode(' ', $searchValues[$key])), ['']);

            return $this->handleValues($values, $type, $prefix);
        }

        return $values;
    }

    public function value(array $searchValues, string $key, string $type = null, string $prefix = null, mixed $default = null): mixed
    {
        if (isset($searchValues[$key]) && ($searchValues[$key] !== '')) {
            return $this->handleValues([$searchValues[$key]], $type, $prefix)[0];
        }

        return $default;
    }

    public function values(array $searchValues, string $key, string $type = null, string $prefix = null, mixed $default = null): mixed
    {
        if (isset($searchValues[$key]) && ($searchValues[$key] !== '')) {
            return $this->handleValues($searchValues[$key], $type, $prefix);
        }

        return $default;
    }

    private function handleValues(array $values, string $type = null, string $prefix = null): array
    {
        if ($prefix) {
            $values = array_map(static function ($item) use ($prefix) {
                return str_replace($prefix, '', $item);
            }, $values);
        }

        if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'], true)) {
            $values = array_map(static function ($item) use ($type) {
                settype($item, $type);

                return $item;
            }, $values);
        } elseif ($type === 'json') {
            $values = array_map(static function ($item) {
                return json_decode($item, true);
            }, $values);
        }

        return $values;
    }

    public function entity(array $searchValues, string $key, EntityRepository $repo): ?object
    {
        return $this->entities($searchValues, $key, $repo)[0] ?? null;
    }

    public function entities(array $searchValues, string $key, EntityRepository $repo): array
    {
        $ids = $searchValues[$key] ?? [];

        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_diff($ids, ['', null]);

        $entities = [];
        foreach ($ids as $id) {
            if ($entity = $repo->find($id)) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }
}
