<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\TreeNode;

/**
 *
 * This interface must be implemented by tree nodes.
 *
 * @package HeimrichHannot\TreeBundle\TreeNode
 */
abstract class AbstractTreeNode
{
    const PREPEND_PALETTE = '{type_legend},title,alias,type;';
    const APPEND_PALETTE = '{publish_legend},published,start,stop;';

    /**
     * Return a unique node type.
     *
     * @return string
     */
    abstract public static function getType(): string;

    /**
     * Return the node palette including legends.
     *
     * "{title_legend},title,alias,type;" is always prepended, so don't add it here.
     *
     * @return string
     */
    abstract protected function getPalette(): string;

    /**
     * Return a list of node types that are allowed to be childs of this node.
     * Return null if there should be no limitation.
     *
     * @return array|null
     */
    public function allowedChilds(): ?array
    {
        return null;
    }

    /**
     * Return true if node type is not allowed to be used as root.
     *
     * @return bool
     */
    public function disallowRoot(): bool
    {
        return false;
    }

    /**
     * Return the node type palette
     *
     * @param string $prependPalette
     * @param string $appendPalette
     * @return string
     */
    public function generatePalette(string $prependPalette = self::PREPEND_PALETTE, string $appendPalette = self::APPEND_PALETTE): string
    {
        return $prependPalette.$this->getPalette().$appendPalette;
    }
}