<?php

namespace HBM\DatagridBundle\Service;

use Doctrine\ORM\EntityRepository;

class SearchMenuHelper {

  /**
   * @param string|null $minus
   * @param string|null $plus
   * @param string|null $zero
   *
   * @return array
   */
  public function flags(?string $minus = 'nein' , ?string $plus = 'ja', ?string $zero = NULL) : array {
    $flags = [];
    if ($minus !== NULL) {
      $flags['flag_-1'] = $minus;
    }
    if ($zero !== NULL) {
      $flags['flag_0'] = $zero;
    }
    if ($plus !== NULL) {
      $flags['flag_1'] = $plus;
    }

    return $flags;
  }

  /**
   * @return array
   */
  public function flags0() : array {
    return $this->flags(NULL, 'ja', 'nein');
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param string|null $type
   * @param string|null $prefix
   *
   * @return array
   */
  public function tokens(array $searchValues, string $key, string $type = NULL, string $prefix = NULL) : array {
    $values = [];
    if (isset($searchValues[$key])) {
      $values = array_diff(array_map('trim', explode(' ', $searchValues[$key])), ['']);
      return $this->handleValues($values, $type, $prefix);
    }
    return $values;
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param string|null $type
   * @param string|null $prefix
   * @param mixed|null $default
   *
   * @return mixed
   */
  public function value(array $searchValues, string $key, string $type = NULL, string $prefix = NULL, mixed $default = NULL): mixed {
    if (isset($searchValues[$key]) && ($searchValues[$key] !== '')) {
      return $this->handleValues([$searchValues[$key]], $type, $prefix)[0];
    }

    return $default;
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param string|null $type
   * @param string|null $prefix
   * @param mixed|null $default
   *
   * @return mixed
   */
  public function values(array $searchValues, string $key, string $type = NULL, string $prefix = NULL, mixed $default = NULL): mixed {
    if (isset($searchValues[$key]) && ($searchValues[$key] !== '')) {
      return $this->handleValues($searchValues[$key], $type, $prefix);
    }

    return $default;
  }

  /**
   * @param array $values
   * @param string|null $type
   * @param string|null $prefix
   *
   * @return array
   */
  private function handleValues(array $values, string $type = NULL, string $prefix = NULL) : array {
    if ($prefix) {
      $values = array_map(static function($item) use ($prefix) {
        return str_replace($prefix, '', $item);
      }, $values);
    }

    if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'], TRUE)) {
      $values = array_map(static function($item) use ($type) {
        settype($item, $type);
        return $item;
      }, $values);
    } elseif ($type === 'json') {
      $values = array_map(static function($item) use ($type) {
        return json_decode($item, TRUE);
      }, $values);
    }

    return $values;
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param EntityRepository $repo
   *
   * @return object|null
   */
  public function entity(array $searchValues, string $key, EntityRepository $repo): ?object {
    return $this->entities($searchValues, $key, $repo)[0] ?? null;
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param EntityRepository $repo
   *
   * @return array
   */
  public function entities(array $searchValues, string $key, EntityRepository $repo): array {
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
