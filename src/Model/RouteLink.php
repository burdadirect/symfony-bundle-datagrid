<?php

namespace HBM\DatagridBundle\Model;

class RouteLink extends Route
{
    /** @var array */
    private $params;

    /** @var int */
    private $value;

    /**
     * RouteLink constructor.
     */
    public function __construct($params, Route $route)
    {
        parent::__construct();

        $this->params = $params;

        if ($route !== null) {
            $this->name     = $route->getName();
            $this->defaults = $route->getDefaults();
            $this->hash     = $route->getHash();
        }
    }

    /* GETTER/SETTER ********************************************************* */

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setParams($params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /* CUSTOM **************************************************************** */

    public function getMerged(): array
    {
        return array_merge($this->getDefaults(), $this->getParams());
    }

    public function __toString(): string
    {
        return $this->name . '(' . json_encode($this->getMerged()) . ') [' . $this->value . ']';
    }
}
