<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Event;

use HeimrichHannot\TreeBundle\Model\TreeModel;
use Symfony\Contracts\EventDispatcher\Event;

class AllowedChildrenEvent extends Event
{
    const NAME = 'huh.tree.allowed_children';

    /**
     * @var array|null
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

    public function __construct(?array $allowedChildren, TreeModel $parentNodeModel, int $currentNodeId)
    {
        $this->allowedChildren = $allowedChildren;
        $this->parentNodeModel = $parentNodeModel;
        $this->currentNodeId = $currentNodeId;
    }

    /**
     * Is null if no restrictions.
     */
    public function getAllowedChildren(): ?array
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

    public function setAllowedChildren(?array $allowedChildren): void
    {
        $this->allowedChildren = $allowedChildren;
    }
}
