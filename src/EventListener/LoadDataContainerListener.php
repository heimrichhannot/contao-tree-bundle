<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\EventListener;


use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer;
use HeimrichHannot\TreeBundle\TreeNode\RootNodeInterface;
use HeimrichHannot\TreeBundle\TreeNode\TreeNodeInterface;

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

        /** @var TreeNodeInterface $nodeType */
        foreach ($this->nodeTypeCollection->getNodeTypes() as $nodeType) {
            if ($nodeType instanceof RootNodeInterface) {
                $palette = TreeContainer::PREPEND_ROOT_PALETTE;
            } else {
                $palette = TreeContainer::PREPEND_PALETTE;
            }
            $dca['palettes'][$nodeType::getType()] = $palette.$nodeType::getPalette();
        }
    }
}