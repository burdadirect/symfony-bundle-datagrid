<?php

namespace HBM\DatagridBundle\Model;

class TableCell {

  const VISIBLE_NORMAL      = 0b000001;
  const VISIBLE_NORMAL_EX   = 0b000101;
  const VISIBLE_EXTENDED    = 0b000010;
  const VISIBLE_EXTENDED_EX = 0b000110;
  const VISIBLE_BOTH        = 0b000011;
  const VISIBLE_EXPORT      = 0b000100;
  const VISIBLE_ALL         = 0b000111;

  /**
   * @var string
   */
  protected $key;

  /**
   * @var string
   */
  protected $label;

  /**
   * @var Route
   */
  private $route;

  /**
   * @var integer
   */
  protected $visibility;

  /**
   * @var array
   */
  protected $options;

  /**
   * @var array
   */
  protected $theadLinks = [];

  /**
   * @var array
   */
  protected $validOptions = [
    'value' => 'string|callback',
    'th_attr' => 'string|array',
    'td_attr' => 'string|array',
    'a_attr' => 'string|array',
    'sort_key' => 'string|array',
    'sort_key_sep' => 'string',
    'label_pos' => 'string',
    'params' => 'array|callback',
    'template' => 'string|callback',
    'templateParams' => 'array|callback',
    'format' => 'string',
  ];

  public function __construct($key, $label, $route, $visibility, $options = []) {
    $this->key = $key;
    $this->label = $label;
    $this->route = $route;
    if ($visibility === TRUE) {
      $this->visibility = self::VISIBLE_EXTENDED;
    } elseif ($visibility === FALSE) {
      $this->visibility = self::VISIBLE_BOTH;
    } else {
      $this->visibility = $visibility;
    }


    $this->setOptions($options);
  }
  /* GETTER/SETTER ************************************************************/

  public function setKey($key) {
    $this->key = $key;
  }

  public function getKey() {
    return $this->key;
  }

  public function setLabel($label) {
    $this->label = $label;
  }

  public function getLabel() {
    return $this->label;
  }

  public function setRoute($route) {
    $this->route = $route;
  }

  public function getRoute() {
    return $this->route;
  }

  public function setVisibility($visibility) {
    $this->visibility = $visibility;
  }

  public function getVisibility() {
    return $this->visibility;
  }

  public function addTheadLink($sortKey, $theadLink) {
    return $this->theadLinks[$sortKey] = $theadLink;
  }

  public function getTheadLinks() {
    return $this->theadLinks;
  }

  public function setOptions($options) {
    $this->validateOptions($options, $this->validOptions);

    $this->options = $options;
  }

  public function getOptions() {
    return $this->options;
  }

  /* CUSTOM *******************************************************************/

  public function isVisible($visibility) {
    if (($this->getVisibility() & $visibility) === $visibility) {
      return TRUE;
    }
    return FALSE;
  }

  public function isVisibleNormal() {
    return $this->isVisible(self::VISIBLE_NORMAL);
  }

  public function isVisibleExtended() {
    return $this->isVisible(self::VISIBLE_EXTENDED);
  }

  public function isVisibleExport() {
    return $this->isVisible(self::VISIBLE_EXPORT);
  }

  public function getLink($obj, $column, $row) {
    return new RouteLink($this->getParams($obj, $column, $row), $this->getRoute());
  }

  public function getParams($obj, $column, $row) {
    if ($this->hasOption('params')) {
      $params = $this->getOption('params');
      if (is_string($params)) {
        return $params;
      } else {
        if (is_callable($params)) {
          return $params($obj, $column, $row);
        } else {
          throw new \Exception("How come?");
        }
      }
    }

    return array();
  }

  public function getTemplate($obj, $column, $row, $default = 'HBMDatagridBundle:Datagrid:table-cell.html.twig') {
    if ($this->hasOption('template')) {
      $template = $this->getOption('template');
      if (is_string($template)) {
        return $template;
      } elseif (is_callable($template)) {
        return $template($obj, $column, $row);
      } else {
        throw new \Exception("How come?");
      }
    }

    return $default;
  }

  public function getTemplateParams($obj, $column, $row, $default = []) {
    if ($this->hasOption('templateParams')) {
      $templateParams = $this->getOption('templateParams');

      if (is_array($templateParams)) {
        return $templateParams;
      } elseif (is_callable($templateParams)) {
        return $templateParams($obj, $column, $row);
      } else {
        throw new \Exception("How come?");
      }
    }

    return $default;
  }

  public function getOption($key, $default = NULL) {
    if (isset($this->options[$key])) {
      return $this->options[$key];
    }

    return $default;
  }

  public function hasOption($key) {
    if (isset($this->options[$key])) {
      return TRUE;
    }

    return FALSE;
  }

  public function getAttr($scope) {
    return $this->getHtmlAttrString($this->getOption($scope . '_attr', array()));
  }

  public function parseValue($obj, $column, $row) {
    if ($this->hasOption("value")) {
      $value = $this->getOption("value");
      if (is_string($value)) {
        return $value;
      } else {
        if (is_callable($value)) {
          return $value($obj, $column, $row);
        } else {
          throw new \Exception("How come?");
        }
      }
    } else {
      if (method_exists($obj, 'get' . ucfirst($this->getKey()))) {
        $value = $obj->{'get' . ucfirst($this->getKey())}();

        if ($value instanceof \DateTime) {
          $format = 'Y-m-d H:i:s';
          if (isset($this->options['format'])) {
            $format = $this->options['format'];
          }

          return $value->format($format);
        } else {
          return $value;
        }
      }
    }
    
    return NULL;
  }

  private function validateOptions($options, $validOptions) {
    foreach ($options as $option => $value) {
      $types = $this->getOptionTypes($option, $validOptions);

      $valid = FALSE;

      foreach ($types as $type) {
        if ($type === 'string') {
          if (is_string($value)) {
            $valid = TRUE;
          }
        } else {
          if ($type === 'array') {
            if (is_array($value)) {
              $valid = TRUE;
            }
          } else {
            if ($type === 'callback') {
              if (is_callable($value)) {
                $valid = TRUE;
              }
            } else {
              throw new \Exception("Unknown type for option '$option'");
            }
          }
        }
      }

      if (!$valid) {
        throw new \Exception("Option '$option' is not valid");
      }
    }
  }

  private function getOptionTypes($option, $validOptions) {
    if (!isset($validOptions[$option])) {
      throw new \Exception("Not a valid option '$option'");
    }

    $types = $validOptions[$option];

    if (strstr($types, "|") !== FALSE) {
      $types = preg_split("/\|/", $types);
    } elseif (!is_array($types)) {
      $types = [$types];
    }

    return $types;
  }

  private function getHtmlAttrString($attributes) {
    $parts = array();
    foreach ($attributes as $key => $value) {
      $parts[] = $key . '="' . $value . '"';
    }

    return implode(' ', $parts);
  }

  public function isSortable() {
    return $this->hasOption("sort_key");
  }

  public function getSortKeys() {
    $sortKey = $this->getOption("sort_key");

    if (!is_array($sortKey)) {
      $sortKey = array($sortKey => $this->getLabel());
    }

    return $sortKey;
  }

  public function getSortKeyLabel($sortKey) {
    $sortKeys = $this->getSortKeys();

    if (isset($sortKeys[$sortKey])) {
      return $sortKeys[$sortKey];
    }

    return $this->getLabel();
  }

  public function getSortKeySep() {
    if ($this->hasOption('sort_key_sep')) {
      return $this->getOption("sort_key_sep");
    }

    return ' | ';
  }

  public function getLabelPos() {
    if ($this->hasOption('label_pos')) {
      return $this->getOption('label_pos');
    }

    return 'before';
  }

}
