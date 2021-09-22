<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\Form\DataTransformerInterface;

class TableCell {

  public const VISIBLE_NONE        = 0b000000;
  public const VISIBLE_NORMAL      = 0b000001;
  public const VISIBLE_NORMAL_EX   = 0b000101;
  public const VISIBLE_EXTENDED    = 0b000010;
  public const VISIBLE_EXTENDED_EX = 0b000110;
  public const VISIBLE_BOTH        = 0b000011;
  public const VISIBLE_EXPORT      = 0b000100;
  public const VISIBLE_ALL         = 0b000111;

  public const LABEL_POS_BEFORE = 'before';
  public const LABEL_POS_AFTER  = 'after';
  public const LABEL_POS_NONE   = FALSE;

  /**
   * @var string|array|null
   */
  protected $key;

  protected ?string $label = null;

  protected ?string $labelText = null;

  private ?Route $route;

  protected ?int $visibility = null;

  protected ?array $options = null;

  protected array $theadLinks = [];

  protected Formatter $formatter;

  /**
   * @var array
   */
  public static $validOptions = [
    'value' => 'string|callback',
    'th_attr' => 'string|array',
    'td_attr' => 'string|array',
    'a_attr' => 'string|array',
    'sort_key' => 'string|array',
    'sort_key_sep' => 'string',
    'label_pos' => 'string|bool',
    'params' => 'array|callback',
    'template' => 'string|callback',
    'template_params' => 'array|callback',
    'strip_tags' => 'bool',
    'format' => 'string',
    'separator' => 'string',
    'transformer' => 'object',
  ];

  /**
   * TableCell constructor.
   *
   * @param $key
   * @param $label
   * @param $route
   * @param $visibility
   * @param array $options
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($key, $label, $route, $visibility, array $options = []) {
    $this->key = $key;
    $this->label = $label;
    $this->labelText = $label;
    $this->route = $route;
    $this->visibility = $visibility;

    if ($visibility === TRUE) {
      $this->visibility = self::VISIBLE_EXTENDED;
    } elseif ($visibility === FALSE) {
      $this->visibility = self::VISIBLE_BOTH;
    }

    $this->setOptions($options);

    $this->setFormatter(new Formatter());
  }
  /* GETTER/SETTER ************************************************************/

  public function setKey($key) : void {
    $this->key = $key;
  }

  /**
   * @return array|string
   */
  public function getKey() {
    return $this->key;
  }

  public function setLabel($label) : void {
    $this->label = $label;
  }

  public function getLabel() : ?string {
    return $this->label;
  }

  public function setLabelText($labelText) : void {
    $this->labelText = $labelText;
  }

  public function getLabelText() : ?string {
    return $this->labelText;
  }

  public function setRoute(Route $route) : void {
    $this->route = $route;
  }

  public function getRoute() : ?Route {
    return $this->route;
  }

  public function setVisibility($visibility) : void {
    $this->visibility = $visibility;
  }

  public function getVisibility() : ?int {
    return $this->visibility;
  }

  public function addTheadLink($sortKey, $theadLink) {
    return $this->theadLinks[$sortKey] = $theadLink;
  }

  public function getTheadLinks() : array {
    return $this->theadLinks;
  }

  /**
   * Set formatter.
   *
   * @param Formatter $formatter
   *
   * @return self
   */
  public function setFormatter(Formatter $formatter) : self {
    $this->formatter = $formatter;

    return $this;
  }

  /**
   * Get formatter.
   *
   * @return Formatter
   */
  public function getFormatter() : Formatter {
    return $this->formatter;
  }

  /**
   * @param $options
   *
   * @throws \InvalidArgumentException
   */
  public function setOptions($options) : void {
    $this->validateOptions($options, self::$validOptions);

    $this->options = $options;
  }

  public function getOptions() : array {
    return $this->options;
  }

  /* CUSTOM *******************************************************************/

  public function isVisible($visibility) : bool {
   return ($this->getVisibility() & $visibility) === $visibility;
  }

  public function isVisibleNormal() : bool {
    return $this->isVisible(self::VISIBLE_NORMAL);
  }

  public function isVisibleExtended() : bool {
    return $this->isVisible(self::VISIBLE_EXTENDED);
  }

  public function isVisibleExport() : bool {
    return $this->isVisible(self::VISIBLE_EXPORT);
  }

  /**
   * @param $obj
   * @param $column
   * @param $row
   *
   * @return RouteLink
   *
   * @throws \InvalidArgumentException
   */
  public function getLink($obj, $column, $row) : RouteLink {
    return new RouteLink($this->getParams($obj, $column, $row), $this->getRoute());
  }

  /**
   * @param $obj
   * @param $column
   * @param $row
   *
   * @return array|mixed|null
   *
   * @throws \InvalidArgumentException
   */
  public function getParams($obj, $column, $row) {
    if ($this->hasOption('params')) {
      $params = $this->getOption('params');
      if (is_string($params)) {
        return $params;
      }

      if (is_callable($params)) {
        return $params($obj, $column, $row);
      }

      throw new \InvalidArgumentException('How come?');
    }

    return [];
  }

  /**
   * @param $obj
   * @param $column
   * @param $row
   * @param string $default
   *
   * @return mixed|null|string
   *
   * @throws \InvalidArgumentException
   */
  public function getTemplate($obj, $column, $row, $default = '@HBMDatagrid/Datagrid/table-cell.html.twig') {
    if ($this->hasOption('template')) {
      $template = $this->getOption('template');
      if (is_string($template)) {
        return $template;
      }

      if (is_callable($template)) {
        return $template($obj, $column, $row);
      }

      throw new \InvalidArgumentException('Datagrid: Invalid "template" option.');
    }

    return $default;
  }

  /**
   * @param $obj
   * @param $column
   * @param $row
   * @param array $default
   *
   * @return array|mixed|null
   *
   * @throws \InvalidArgumentException
   */
  public function getTemplateParams($obj, $column, $row, array $default = []) {
    if ($this->hasOption('template_params')) {
      $templateParams = $this->getOption('template_params');

      if (is_array($templateParams)) {
        return $templateParams;
      }

      if (is_callable($templateParams)) {
        return $templateParams($obj, $column, $row);
      }

      throw new \InvalidArgumentException('Datagrid: Invalid "template_params" option.');
    }

    return $default;
  }

  public function getOption($key, $default = NULL) {
    return $this->options[$key] ?? $default;
  }

  public function hasOption($key) : bool {
    return isset($this->options[$key]);
  }

  public function getAttr($scope) : string {
    return $this->getHtmlAttrString($this->getOption($scope . '_attr', []));
  }

  public function getValue($obj, $column, $row) {
    $value = $this->parseValue($obj, $column, $row);

    return $this->getFormatter()->formatCellValue($this, $value);
  }

  /**
   * @param $obj
   * @param $column
   * @param $row
   *
   * @return mixed|null|string
   *
   */
  public function parseValue($obj, $column, $row) {
    if ($this->hasOption('value')) {
      $value = $this->getOption('value');
      if (is_string($value)) {
        return $value;
      }
      if (is_callable($value)) {
        return $value($obj, $column, $row);
      }

      throw new \InvalidArgumentException('Datagrid: Invalid "value" option.');
    }

    $value = $this->getValueFromObject($obj, $this->getKey());

    if ($this->hasOption('transformer')) {
      $transformer = $this->getOption('transformer');
      if ($transformer instanceof DataTransformerInterface) {
        return $transformer->transform($value);
      }

      throw new \InvalidArgumentException('Datagrid: Invalid "transform" option.');
    }

    return $value;
  }

  private function getValueFromObject($obj, $key) {
    $callable = [$obj];
    $callableParams = [];
    if (is_string($key)) {
      if (is_callable([$obj, 'get'.ucfirst($key)])) {
        $callable[] = 'get'.ucfirst($key);
      } else {
        $callable[] = $key;
      }
    } elseif (is_array($key)) {
      $callable[] = $key[0] ?? FALSE;
      $callableParams = $key[1] ?? [];
    }

    $value = NULL;
    if (is_callable($callable)) {
      $value = call_user_func_array($callable, $callableParams);
    }

    return $value;
  }

  /**
   * @param $options
   * @param $validOptions
   *
   * @throws \InvalidArgumentException
   */
  private function validateOptions($options, $validOptions) : void {
    foreach ($options as $option => $value) {
      $types = $this->getOptionTypes($option, $validOptions);

      $valid = FALSE;

      foreach ($types as $type) {
        if ($type === 'string') {
          if (is_string($value)) {
            $valid = TRUE;
          }
        } elseif (($type === 'bool') || ($type === 'boolean')) {
          if (is_bool($value)) {
            $valid = TRUE;
          }
        } elseif ($type === 'object') {
          if (is_object($value)) {
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
              throw new \InvalidArgumentException('Datagrid: Unknown type for option "'.$option.'".');
            }
          }
        }
      }

      if (!$valid) {
        throw new \InvalidArgumentException('Datagrid: Option "'.$option.'" is not valid.');
      }
    }
  }

  /**
   * @param $option
   * @param $validOptions
   *
   * @return array
   *
   * @throws \InvalidArgumentException
   */
  private function getOptionTypes($option, $validOptions) : array {
    if (!isset($validOptions[$option])) {
      throw new \InvalidArgumentException('Datagrid: Not a valid option "'.$option.'".');
    }

    $types = $validOptions[$option];

    if (strpos($types, '|') !== FALSE) {
      $types = explode('|', $types);
    } elseif (!is_array($types)) {
      $types = [$types];
    }

    return $types;
  }

  private function getHtmlAttrString($attributes) : string {
    $parts = [];
    foreach ($attributes as $key => $value) {
      $parts[] = $key . '="' . $value . '"';
    }

    return implode(' ', $parts);
  }

  public function isSortable() : bool {
    return $this->hasOption('sort_key');
  }

  public function getSortKeys() {
    $sortKey = $this->getOption('sort_key');

    if (!is_array($sortKey)) {
      $sortKey = array($sortKey => ['label' => $this->getLabel(), 'text' => $this->getLabelText()]);
    }

    return $sortKey;
  }

  public function getSortKeyLabel($sortKey) {
    $sortKeys = $this->getSortKeys();

    if (isset($sortKeys[$sortKey])) {
      $sortKeyData = $sortKeys[$sortKey];
      if (is_array($sortKeyData) && isset($sortKeyData['label'])) {
        return $sortKeys[$sortKey]['label'];
      }
      return $sortKeys[$sortKey];
    }

    return $this->getLabel();
  }

  public function getSortKeyText($sortKey) {
    $sortKeys = $this->getSortKeys();

    if (isset($sortKeys[$sortKey])) {
      $sortKeyData = $sortKeys[$sortKey];
      if (is_array($sortKeyData) && isset($sortKeyData['text'])) {
        return $sortKeys[$sortKey]['text'];
      }
      return $sortKeys[$sortKey];
    }

    return $this->getLabelText();
  }

  public function getSortKeySep() {
    if ($this->hasOption('sort_key_sep')) {
      return $this->getOption('sort_key_sep');
    }

    return ' | ';
  }

  public function getLabelPos() {
    if ($this->hasOption('label_pos')) {
      return $this->getOption('label_pos');
    }

    return self::LABEL_POS_BEFORE;
  }

}
