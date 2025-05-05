<?php

namespace HBM\DatagridBundle\Model;

use HBM\DatagridBundle\Traits\ParseAttrTrait;
use HBM\TwigAttributesBundle\Utils\HtmlAttributes;
use Symfony\Component\Form\DataTransformerInterface;

class TableCell
{
    use ParseAttrTrait;

    // Visibility constants
    public const VISIBLE_NONE        = 0b000000;
    public const VISIBLE_NORMAL      = 0b000001;
    public const VISIBLE_NORMAL_EX   = 0b000101;
    public const VISIBLE_EXTENDED    = 0b000010;
    public const VISIBLE_EXTENDED_EX = 0b000110;
    public const VISIBLE_BOTH        = 0b000011;
    public const VISIBLE_EXPORT      = 0b000100;
    public const VISIBLE_ALL         = 0b000111;

    // Label constants
    public const LABEL_POS_BEFORE = 'before';
    public const LABEL_POS_AFTER  = 'after';
    public const LABEL_POS_NONE   = false;

    protected string|array|\Closure|null $key;

    protected ?string $label = null;

    protected ?string $labelText = null;

    private ?Route $route;

    protected ?int $visibility = null;

    protected array $options = [];

    protected array $theadLinks = [];

    protected Formatter $formatter;

    public static array $validOptions = [
      'value'             => 'string|callable',
      'th_attr'           => 'string|array|callable',
      'td_attr'           => 'string|array|callable',
      'a_attr'            => 'string|array|callable',
      'sort_key'          => 'string|array',
      'sort_key_sep'      => 'string',
      'label_pos'         => 'string|bool',
      'label_prefix'      => 'string',
      'label_prefix_raw'  => 'bool',
      'label_postfix'     => 'string',
      'label_postfix_raw' => 'bool',
      'params'            => 'array|callable',
      'template'          => 'string|callable',
      'template_params'   => 'array|callable',
      'strip_tags'        => 'bool',
      'raw'               => 'bool',
      'format'            => 'string',
      'separator'         => 'string',
      'transformer'       => 'object',
      'trans_domain'      => 'bool|string',
      'img_max_width'     => 'int',
      'img_max_height'    => 'int',
      'xlsx_column_width' => 'bool|int',
      'xlsx_cell_callback' => 'callable',
      'xlsx_header_callback' => 'callable',
    ];

    /**
     * TableCell constructor.
     *
     * @param array{
     *       value?:             string|callable,
     *       th_attr?:           string|array|callable,
     *       td_attr?:           string|array|callable,
     *       a_attr?:            string|array|callable,
     *       sort_key?:          string|array,
     *       sort_key_sep?:      string,
     *       label_pos?:         string|bool,
     *       label_prefix?:      string,
     *       label_prefix_raw?:  string,
     *       label_postfix?:     string,
     *       label_postfix_raw?: string,
     *       params?:            array|callable,
     *       template?:          string|callable,
     *       template_params?:   array|callable,
     *       strip_tags?:        bool,
     *       raw?:               bool,
     *       format?:            string,
     *       separator?:         string,
     *       transformer?:       object,
     *       trans_domain?:      bool|string,
     *       img_max_width?:     int,
     *       img_max_height?:    int
     *   } $options
     */
    public function __construct(string|callable|array|null $key, ?string $label, ?Route $route, int|bool $visibility, array $options = [])
    {
        $this->key        = $key;
        $this->label      = $label;
        $this->labelText  = $label;
        $this->route      = $route;
        $this->visibility = $visibility;

        if ($visibility === true) {
            $this->visibility = self::VISIBLE_EXTENDED;
        } elseif ($visibility === false) {
            $this->visibility = self::VISIBLE_BOTH;
        }

        $this->setOptions($options);

        $this->setFormatter(new Formatter());
    }
    /* GETTER/SETTER *********************************************************** */

    public function setKey(string|array|callable|null $key): void
    {
        $this->key = $key;
    }

    public function getKey(): array|string|callable|null
    {
        return $this->key;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabelText(?string $labelText): void
    {
        $this->labelText = $labelText;
    }

    public function getLabelText(): ?string
    {
        return $this->labelText;
    }

    public function setRoute(?Route $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setVisibility(?int $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function addTheadLink(string $sortKey, RouteLink $theadLink): void
    {
        $this->theadLinks[$sortKey] = $theadLink;
    }

    /**
     * @return RouteLink[]
     */
    public function getTheadLinks(): array
    {
        return $this->theadLinks;
    }

    /**
     * Set formatter.
     */
    public function setFormatter(Formatter $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Get formatter.
     */
    public function getFormatter(): Formatter
    {
        return $this->formatter;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setOptions(array $options): void
    {
        $this->validateOptions($options);

        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /* CUSTOM ****************************************************************** */

    public function isVisible(int $visibility): bool
    {
        return ($this->getVisibility() & $visibility) === $visibility;
    }

    public function isVisibleNormal(): bool
    {
        return $this->isVisible(self::VISIBLE_NORMAL);
    }

    public function isVisibleExtended(): bool
    {
        return $this->isVisible(self::VISIBLE_EXTENDED);
    }

    public function isVisibleExport(): bool
    {
        return $this->isVisible(self::VISIBLE_EXPORT);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getLink($obj, $column, $row): RouteLink
    {
        return new RouteLink($this->getParams($obj, $column, $row), $this->getRoute());
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return null|array|mixed
     */
    public function getParams($obj, $column, $row): mixed
    {
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
     * @throws \InvalidArgumentException
     *
     * @return null|mixed|string
     */
    public function getTemplate($obj, $column, $row, string $default = '@HBMDatagrid/Datagrid/table-cell.html.twig'): mixed
    {
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
     * @throws \InvalidArgumentException
     *
     * @return null|array|mixed
     */
    public function getTemplateParams($obj, $column, $row, array $default = []): mixed
    {
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

    public function getOption($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption($key): bool
    {
        return isset($this->options[$key]);
    }

    public function getAttr($scope, $obj = null, $column = null, $row = null): HtmlAttributes
    {
        $attributes = new HtmlAttributes();

        return $this->parseAttr($attributes, $this->getOption($scope . '_attr', []), [$obj, $column, $row]);
    }

    public function getValue($obj, $column, $row)
    {
        $value = $this->parseValue($obj, $column, $row);

        return $this->getFormatter()->formatCellValue($this, $value);
    }

    /**
     * @return null|mixed|string
     */
    public function parseValue($obj, $column, $row): mixed
    {
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

    private function getValueFromObject(object $obj, string|array|callable|null $key)
    {
        $callable       = [$obj];
        $callableParams = [];

        if (is_string($key)) {
            if (is_callable([$obj, 'get' . ucfirst($key)])) {
                $callable[] = 'get' . ucfirst($key);
            } else {
                $callable[] = $key;
            }
        } elseif (is_array($key)) {
            $callable[]     = $key[0] ?? false;
            $callableParams = $key[1] ?? [];
        } elseif (is_callable($key)) {
            return $key($obj);
        }

        $value = null;

        if (is_callable($callable)) {
            $value = call_user_func_array($callable, $callableParams);
        }

        return $value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $types = $this->getOptionTypes($option);

            $valid = false;

            foreach ($types as $type) {
                if ($type === 'string') {
                    if (is_string($value)) {
                        $valid = true;
                    }
                } elseif (($type === 'bool') || ($type === 'boolean')) {
                    if (is_bool($value)) {
                        $valid = true;
                    }
                } elseif ($type === 'object') {
                    if (is_object($value)) {
                        $valid = true;
                    }
                } elseif ($type === 'array') {
                    if (is_array($value)) {
                        $valid = true;
                    }
                } elseif ($type === 'callable') {
                    if (is_callable($value)) {
                        $valid = true;
                    }
                } elseif ($type === 'int') {
                    if (is_int($value)) {
                        $valid = true;
                    }
                } else {
                    throw new \InvalidArgumentException('Datagrid: Unknown type for option "' . $option . '".');
                }
            }

            if (!$valid) {
                throw new \InvalidArgumentException('Datagrid: Option "' . $option . '" is not valid.');
            }
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getOptionTypes(string $option): array
    {
        if (!isset(self::$validOptions[$option])) {
            throw new \InvalidArgumentException('Datagrid: Not a valid option "' . $option . '".');
        }

        $types = self::$validOptions[$option];

        if (str_contains($types, '|')) {
            $types = explode('|', $types);
        } elseif (!is_array($types)) {
            $types = [$types];
        }

        return $types;
    }

    public function isSortable(): bool
    {
        return $this->hasOption('sort_key');
    }

    public function getSortKeys()
    {
        $sortKey = $this->getOption('sort_key');

        if (!is_array($sortKey)) {
            $sortKey = [$sortKey => ['label' => $this->getLabel(), 'text' => $this->getLabelText()]];
        }

        return $sortKey;
    }

    public function getSortKeyLabel(string $sortKey)
    {
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

    public function getSortKeyText(string $sortKey)
    {
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

    public function getSortKeySep()
    {
        return $this->getOptionValueWithFallback('sort_key_sep', ' | ');
    }

    public function getLabelPos()
    {
        return $this->getOptionValueWithFallback('label_pos', self::LABEL_POS_BEFORE);
    }

    public function getLabelPrefix(): ?string
    {
        return $this->getOptionValueWithFallback('label_prefix', null);
    }

    public function getLabelPostFix(): ?string
    {
        return $this->getOptionValueWithFallback('label_postfix', null);
    }

    private function getOptionValueWithFallback(string $optionKey, mixed $fallback): mixed
    {
        if ($this->hasOption($optionKey)) {
            return $this->getOption($optionKey);
        }

        return $fallback;
    }
}
