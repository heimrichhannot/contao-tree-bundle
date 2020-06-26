<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\EventListener;

use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode;

class LoadDataContainerListener
{
    /**
     * @var NodeTypeCollection
     */
    private $nodeTypeCollection;

    /**
     * LoadDataContainerListener constructor.
     */
    public function __construct(NodeTypeCollection $nodeTypeCollection)
    {
        $this->nodeTypeCollection = $nodeTypeCollection;
    }

    public function __invoke(string $table)
    {
        if ('tl_tree' !== $table) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_tree'];

        /** @var AbstractTreeNode $nodeType */
        foreach ($this->nodeTypeCollection->getNodeTypes() as $nodeType) {
            $dca['palettes'][$nodeType::getType()] = $nodeType->generatePalette();
        }
    }
}
