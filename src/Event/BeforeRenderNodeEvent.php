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


use HeimrichHannot\TreeBundle\Model\TreeModel;
use Symfony\Component\EventDispatcher\Event;

class BeforeRenderNodeEvent extends Event
{
    const NAME = 'huh.tree.before_render_node';

    /**
     * @var array
     */
    private $context;
    /**
     * @var TreeModel
     */
    private $treeModel;

    /**
     * BeforeRenderNodeEvent constructor.
     */
    public function __construct(array $context, TreeModel $treeModel)
    {
        $this->context = $context;
        $this->treeModel = $treeModel;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * @return TreeModel
     */
    public function getTreeModel(): TreeModel
    {
        return $this->treeModel;
    }

    /**
     * @param TreeModel $treeModel
     */
    public function setTreeModel(TreeModel $treeModel): void
    {
        $this->treeModel = $treeModel;
    }
}