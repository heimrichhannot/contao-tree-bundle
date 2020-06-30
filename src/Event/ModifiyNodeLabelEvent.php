<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
     * ModifiyNodeLabelEvent constructor.
     */
    public function __construct(string $label, array $row, string $image, string $imageAttribute, DataContainer $dc)
    {
        $this->label = $label;
        $this->row = $row;
        $this->image = $image;
        $this->imageAttribute = $imageAttribute;
        $this->dc = $dc;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getImageAttribute(): string
    {
        return $this->imageAttribute;
    }

    /**
     * @return DataContainer
     */
    public function getDc(): DataContainer
    {
        return $this->dc;
    }
}