<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\Collection;


use HeimrichHannot\TreeBundle\TreeNode\RootNodeInterface;
use HeimrichHannot\TreeBundle\TreeNode\TreeNodeInterface;

class NodeTypeCollection
{
    /**
     * @var iterable<TreeNodeInterface>
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
     * @var array|null
     */
    protected $branchNodeTypes = null;


    /**
     * NodeTypeCollection constructor.
     * @param iterable $nodeTypes
     */
    public function __construct(iterable $nodeTypes)
    {
        $this->nodeTypesIterable = $nodeTypes;
    }

    /**
     * Add a tree node type.
     *
     * @param TreeNodeInterface $nodeType
     */
    public function addNodeType(TreeNodeInterface $nodeType)
    {
        if (isset($this->rootNodeTypes[$nodeType::getType()]))
        {
            trigger_error("Duplicate node type.", E_USER_WARNING);
        }
        $this->nodeTypes[$nodeType::getType()] = $nodeType;
        if ($nodeType instanceof RootNodeInterface)
        {
            $this->rootNodeTypes[] = $nodeType::getType();
        } else
        {
            $this->branchNodeTypes[] = $nodeType::getType();
        }
    }

    /**
     * Get tree node type by type
     *
     * @param string $type
     * @return TreeNodeInterface|null
     */
    public function getNodeType(string $type): ?TreeNodeInterface
    {
        $this->createIndex();
        return $this->nodeTypes[$type] ?: null;
    }

    /**
     * Return all tree node types
     *
     * @return array
     */
    public function getNodeTypes(): array
    {
        $this->createIndex();
        return $this->nodeTypes;
    }

    /**
     * Return all root node types
     *
     * @return array
     */
    public function getRootNodeTypes(): array
    {
        $this->createIndex();
        return $this->rootNodeTypes;
    }

    /**
     * Return if type is root node
     *
     * @param string $type
     * @return bool
     */
    public function isRootType(string $type): bool
    {
        $this->createIndex();
        return in_array($type, $this->rootNodeTypes);
    }

    protected function createIndex(): void
    {
        if (null !== $this->nodeTypes)
        {
            return;
        }
        $this->nodeTypes     = [];
        $this->rootNodeTypes = [];
        /** @var TreeNodeInterface $nodeType */
        foreach ($this->nodeTypesIterable as $nodeType)
        {
            $this->addNodeType($nodeType);
        }
    }

    /**
     * Return branch node types
     *
     * @return null
     */
    public function getBranchNodeTypes()
    {
        $this->createIndex();
        return $this->branchNodeTypes;
    }
}