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
interface TreeNodeInterface
{
    /**
     * Return a unique node type.
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * Return the node palette including legends.
     *
     * "{title_legend},title,alias,type;" is always prepended, so don't add it here.
     *
     * @return string
     */
    public static function getPalette(): string;

    /**
     * Return a list of node types that are allowed to be childs of this node.
     * Return null if there should be no limitation.
     *
     * @return array|null
     */
    public static function allowedChilds(): ?array;

    /**
     * Return true if node type is not allowed to be used as root.
     *
     * @return bool
     */
    public static function disallowRoot(): bool;
}