<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Collection;

use HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode;

class NodeTypeCollection
{
    /**
     * @var iterable<AbstractTreeNode>
     */
    protected $nodeTypesIterable;
    /**
     * @var array|null
     */
    protected $nodeTypes = null;
    /**
     * @var array|null
     */
    protected $rootNodeTypes = null;

    /**
     * NodeTypeCollection constructor.
     */
    public function __construct(iterable $nodeTypes)
    {
        $this->nodeTypesIterable = $nodeTypes;
    }

    /**
     * Add a tree node type.
     */
    public function addNodeType(AbstractTreeNode $nodeType)
    {
        if (isset($this->rootNodeTypes[$nodeType::getType()])) {
            trigger_error('Duplicate node type.', E_USER_WARNING);
        }
        $this->nodeTypes[$nodeType::getType()] = $nodeType;

        if ($nodeType->getIsRootSupported()) {
            $this->rootNodeTypes[] = $nodeType::getType();
        }
    }

    /**
     * Get tree node type by type.
     */
    public function getNodeType(string $type): ?AbstractTreeNode
    {
        $this->createIndex();

        return $this->nodeTypes[$type] ?: null;
    }

    /**
     * Return all tree node types.
     */
    public function getNodeTypes(): array
    {
        $this->createIndex();

        return $this->nodeTypes;
    }

    /**
     * Return all root node types.
     */
    public function getRootNodeTypes(): array
    {
        $this->createIndex();

        return $this->rootNodeTypes;
    }

    /**
     * Return if type is root node.
     */
    public function isRootType(string $type): bool
    {
        $this->createIndex();

        return \in_array($type, $this->rootNodeTypes);
    }

    /**
     * Return branch node types.
     *
     * @return null
     */
    public function getBranchNodeTypes()
    {
        $this->createIndex();

        return array_keys($this->nodeTypes);
    }

    protected function createIndex(): void
    {
        if (null !== $this->nodeTypes) {
            return;
        }
        $this->nodeTypes = [];
        $this->rootNodeTypes = [];
        /** @var AbstractTreeNode $nodeType */
        foreach ($this->nodeTypesIterable as $nodeType) {
            $this->addNodeType($nodeType);
        }
    }
}
