<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

/**
 * This interface must be implemented by tree nodes.
 */
abstract class AbstractTreeNode
{
    const PREPEND_PALETTE = '{type_legend},title,alias,type;';
    const APPEND_PALETTE  = '{publish_legend},published,start,stop;';

    const ICON_STATE_PUBLISHED   = 0;
    const ICON_STATE_UNPUBLISHED = 1;

    protected $iconHidden = 'bundles/heimrichhannottree/img/backend/node_hidden.svg';
    protected $iconPublished = 'bundles/heimrichhannottree/img/backend/node.svg';

    /**
     * Return a unique node type.
     */
    abstract public static function getType(): string;

    /**
     * Return a list of node types that are allowed to be childs of this node.
     * Return null if there should be no limitation.
     */
    public function getAllowedChildren(): ?array
    {
        return null;
    }

    /**
     * Return true if node type is allowed to be used as root.
     */
    public function getIsRootSupported(): bool
    {
        return true;
    }

    /**
     * Return the node type palette.
     */
    public function generatePalette(string $prependPalette = self::PREPEND_PALETTE, string $appendPalette = self::APPEND_PALETTE): string
    {
        return $prependPalette.$this->getPalette().$appendPalette;
    }

    /**
     * Return the node palette including legends.
     *
     * "{title_legend},title,alias,type;" is always prepended, so don't add it here.
     */
    abstract protected function getPalette(): string;

    /**
     * Return tree node type icon.
     * You can return different icons based on given state.
     * Use AbstractTreeNode::ICON_STATE_* constants for evaluation passed status.
     *
     * @return string
     */
    public function getIcon(string $status): string
    {
        switch ($status) {
            case static::ICON_STATE_UNPUBLISHED:
                return $this->iconHidden;
            case static::ICON_STATE_PUBLISHED:
            default:
                return $this->iconPublished;
        }
    }
}
