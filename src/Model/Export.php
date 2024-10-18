<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class Export extends Formatter
{
    public const CONTENT_TYPE = '';
    public const EXTENSION    = '';

    protected string $name;

    protected array $cells = [];

    protected ?TranslatorInterface $translator = null;

    protected ?string $translationDomain = null;

    /* GETTER/SETTER */

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setCells($cells): void
    {
        $this->cells = $cells;
    }

    public function getCells(): array
    {
        return $this->cells;
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function setTranslator(?TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    public function setTranslationDomain(?string $translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    /* BASIC */

    public function init(): void
    {
    }

    public function finish(): void
    {
    }

    protected function prepareLabel($label): string
    {
        return html_entity_decode(strip_tags($label));
    }

    protected function translateLabel($label, $transDomain = null): string
    {
        // make sure label is a string to prevent null as possible return
        $label ??= '';

        if ($this->translator === null) {
            return $label;
        }

        if ($transDomain === false) {
            return $label;
        }

        $transDomain ??= $this->translationDomain;

        if (empty($transDomain)) {
            return $label;
        }

        return $this->translator->trans($label, [], $transDomain);
    }

    public function formatCellValueString(TableCell $cell, $value)
    {
        if ($cell->getOption('strip_tags', true)) {
            return strip_tags($value);
        }

        return $value;
    }

    public function contenType(): string
    {
        return static::CONTENT_TYPE;
    }

    public function filename(): string
    {
        return $this->getName() . (static::EXTENSION ? '.' . static::EXTENSION : '');
    }

    /* ABSTRACT */

    abstract public function addHeader();

    abstract public function addRow($obj);

    abstract public function response(): Response;

    abstract public function stream();

    abstract public function dump(string $folder = null, string $name = null): string;
}
