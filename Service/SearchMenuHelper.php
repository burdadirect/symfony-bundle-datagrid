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
   *
   * @return array
   */
  public function words(array $searchValues, string $key) : array {
    $values = [];
    if (isset($searchValues[$key])) {
      $values = array_diff(array_map('trim', explode(' ', $searchValues[$key])), ['']);
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
      $value = trim($searchValues[$key]);
      if ($prefix) {
        $value = str_replace($prefix, '', $value);
      }
      if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'], TRUE)) {
        settype($value, $type);
      } elseif ($type === 'json') {
        return json_decode($value, TRUE) ?: $default;
      }
      return $value;
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
      $value = $searchValues[$key];
      if ($prefix) {
        $value = array_map(function($item) use ($prefix) {
          return str_replace($prefix, '', $item);
        }, $value);
      }
      if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'], TRUE)) {
        $value = array_map(function($item) use ($type) {
          settype($item, $type);
          return $item;
        }, $value);
      }
      return $value;
    }

    return $default;
  }

}
