<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class TreeGenerator
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var NodeTypeCollection
     */
    private $nodeTypeCollection;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(KernelInterface $kernel, Connection $connection, Environment $twig, NodeTypeCollection $nodeTypeCollection, EventDispatcherInterface $eventDispatcher)
    {
        $this->kernel = $kernel;
        $this->connection = $connection;
        $this->twig = $twig;
        $this->nodeTypeCollection = $nodeTypeCollection;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Render a tree.
     */
    public function renderTree(int $rootNodeId): string
    {
        $rootNode = TreeModel::findByPk($rootNodeId);

        if (!$rootNode || !$rootNode->isRootNode()) {
            trigger_error('Tree node does not exist or is not a root node.', E_USER_WARNING);

            if ($this->kernel->isDebug()) {
                return '<!-- Tree node does not exist or is not a root node. -->';
            }

            return '';
        }

        return $this->renderNode($rootNode);
    }

    /**
     * Render a single node with it's childs.
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function renderNode(TreeModel $parent, int $depth = 0): string
    {
        $context = $parent->row();
        $context['childs'] = [];
        $context['depth'] = $depth;

        $time = \Date::floorToMinute();
        $stmt = $this->connection->prepare("SELECT id FROM tl_tree WHERE pid=? AND (start='' OR start<=?) AND (stop='' OR stop>?) AND published='1' ORDER BY sorting ASC");
        $stmt->execute([$parent->id, $time, $time + 60]);

        if ($stmt->rowCount() > 0) {
            while ($child = $stmt->fetch(FetchMode::STANDARD_OBJECT)) {
                $childModel = TreeModel::findByPk($child->id);

                if (!$childModel) {
                    continue;
                }
                $context['childs'][$child->id] = $this->renderNode($childModel, ++$depth);
            }
        }

        $nodeType = $this->nodeTypeCollection->getNodeType($parent->type);

        if ($nodeType) {
            $context = $nodeType->prepareNodeOutput($context, $parent);
        }

        $event = $this->eventDispatcher->dispatch(BeforeRenderNodeEvent::NAME, new BeforeRenderNodeEvent($context, $parent));

        return $this->twig->render('@HeimrichHannotTree/node/treenode_default.html.twig', $event->getContext());
    }
}
