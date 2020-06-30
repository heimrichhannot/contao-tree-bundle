<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
     * @var string
     */
    private $template;

    /**
     * BeforeRenderNodeEvent constructor.
     */
    public function __construct(array $context, TreeModel $treeModel, string $template)
    {
        $this->context = $context;
        $this->treeModel = $treeModel;
        $this->template = $template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getTreeModel(): TreeModel
    {
        return $this->treeModel;
    }

    public function setTreeModel(TreeModel $treeModel): void
    {
        $this->treeModel = $treeModel;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
}
