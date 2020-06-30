<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Event;

use Contao\DataContainer;

class ModifiyNodeLabelEvent
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var array
     */
    private $row;
    /**
     * @var string
     */
    private $image;
    /**
     * @var string
     */
    private $imageAttribute;
    /**
     * @var DataContainer
     */
    private $dc;
    /**
     * @var bool
     */
    private $hasChilds;

    /**
     * ModifiyNodeLabelEvent constructor.
     */
    public function __construct(string $label, array $row, string $image, string $imageAttribute, DataContainer $dc, bool $hasChilds)
    {
        $this->label = $label;
        $this->row = $row;
        $this->image = $image;
        $this->imageAttribute = $imageAttribute;
        $this->dc = $dc;
        $this->hasChilds = $hasChilds;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRow(): array
    {
        return $this->row;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getImageAttribute(): string
    {
        return $this->imageAttribute;
    }

    public function getDc(): DataContainer
    {
        return $this->dc;
    }

    /**
     * @return bool
     */
    public function getHasChilds(): bool
    {
        return $this->hasChilds;
    }
}
