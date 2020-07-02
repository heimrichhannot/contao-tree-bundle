<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Event;

use HeimrichHannot\TreeBundle\Model\TreeModel;
use Symfony\Component\EventDispatcher\Event;

class AllowedChildrenEvent extends Event
{
    const NAME = 'huh.tree.allowed_children';

    /**
     * @var array
     */
    private $allowedChildren;
    /**
     * @var TreeModel
     */
    private $parentNodeModel;
    /**
     * @var int
     */
    private $currentNodeId;

    public function __construct(array $allowedChildren, TreeModel $parentNodeModel, int $currentNodeId)
    {
        $this->allowedChildren = $allowedChildren;
        $this->parentNodeModel = $parentNodeModel;
        $this->currentNodeId = $currentNodeId;
    }

    public function getAllowedChildren(): array
    {
        return $this->allowedChildren;
    }

    public function getParentNodeModel(): TreeModel
    {
        return $this->parentNodeModel;
    }

    public function getCurrentNodeId(): int
    {
        return $this->currentNodeId;
    }

    public function setAllowedChildren(array $allowedChildren): void
    {
        $this->allowedChildren = $allowedChildren;
    }
}
