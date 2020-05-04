<?php

namespace HBM\DatagridBundle\Service;

class SearchMenuHelper {

  /**
   * @param string $minus
   * @param string $plus
   * @param null $zero
   *
   * @return array
   */
  public function flags($minus = 'nein' , $plus = 'ja', $zero = NULL) : array {
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
   * @param string $type
   * @param string $prefix
   * @param mixed $default
   *
   * @return mixed
   */
  public function value(array $searchValues, string $key, string $type = NULL, string $prefix = NULL, $default = NULL) {
    if (isset($searchValues[$key]) && ($searchValues[$key] !== '')) {
      return $this->handleValues([$searchValues[$key]], $type, $prefix)[0];
    }

    return $default;
  }

  /**
   * @param array $searchValues
   * @param string $key
   * @param string $type
   * @param string $prefix
   * @param mixed $default
   *
   * @return mixed
   */
  public function values(array $searchValues, string $key, string $type = NULL, string $prefix = NULL, $default = NULL) {
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
      $values = array_map(function($item) use ($prefix) {
        return str_replace($prefix, '', $item);
      }, $values);
    }

    if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'], TRUE)) {
      $values = array_map(function($item) use ($type) {
        settype($item, $type);
        return $item;
      }, $values);
    } elseif ($type === 'json') {
      $values = array_map(function($item) use ($type) {
        return json_decode($item, TRUE);
      }, $values);
    }

    return $values;
  }

}
