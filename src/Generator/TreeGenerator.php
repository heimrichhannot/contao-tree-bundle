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
use HeimrichHannot\TreeBundle\Collection\OutputTypeCollection;
use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use HeimrichHannot\TreeBundle\OutputType\AbstractOutputType;
use HeimrichHannot\TreeBundle\OutputType\ListOutputType;
use HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;

class TreeGenerator
{
    /**
     * @var array
     */
    protected $templateCache = [];
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
    /**
     * @var TemplateUtil
     */
    private $templateUtil;
    /**
     * @var OutputTypeCollection
     */
    private $outputTypeCollection;

    public function __construct(KernelInterface $kernel, Connection $connection, Environment $twig, NodeTypeCollection $nodeTypeCollection, EventDispatcherInterface $eventDispatcher, TemplateUtil $templateUtil, OutputTypeCollection $outputTypeCollection)
    {
        $this->kernel = $kernel;
        $this->connection = $connection;
        $this->twig = $twig;
        $this->nodeTypeCollection = $nodeTypeCollection;
        $this->eventDispatcher = $eventDispatcher;
        $this->templateUtil = $templateUtil;
        $this->outputTypeCollection = $outputTypeCollection;
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

        return $this->renderNode($rootNode, $rootNode);
    }

    /**
     * Render a single node with it's childs.
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function renderNode(TreeModel $currentNode, TreeModel $rootNode, int $depth = 0): string
    {
        $nodeType = $this->nodeTypeCollection->getNodeType($currentNode->type);
        $outputType = $this->outputTypeCollection->getType($rootNode->outputType ?: ListOutputType::getType());

        $context = $currentNode->row();
        $context['childs'] = [];
        $context['depth'] = $depth;
        $context['cssClasses'] = $nodeType::getType().' depth_'.$depth;

        if (0 === $depth) {
            $context['cssClasses'] .= ' huh_tree root';
        }
        $context['cssId'] = 'node_'.$currentNode->alias;

        $time = \Date::floorToMinute();
        $stmt = $this->connection->prepare("SELECT id FROM tl_tree WHERE pid=? AND (start='' OR start<=?) AND (stop='' OR stop>?) AND published='1' ORDER BY sorting ASC");
        $stmt->execute([$currentNode->id, $time, $time + 60]);

        if ($stmt->rowCount() > 0) {
            while ($child = $stmt->fetch(FetchMode::STANDARD_OBJECT)) {
                $childModel = TreeModel::findByPk($child->id);

                if (!$childModel) {
                    continue;
                }
                $context['childs'][$child->id] = $this->renderNode($childModel, $rootNode, ++$depth);
            }
        }

        $template = $this->getTemplate($nodeType, $outputType);

        $this->eventDispatcher->addListener(BeforeRenderNodeEvent::NAME, [$nodeType, 'onBeforeRenderEvent'], 200);
        $this->eventDispatcher->addListener(BeforeRenderNodeEvent::NAME, [$outputType, 'onBeforeRenderEvent'], 100);
        $event = $this->eventDispatcher->dispatch(BeforeRenderNodeEvent::NAME, new BeforeRenderNodeEvent($context, $currentNode, $template));

        return $this->twig->render($event->getTemplate(), $event->getContext());
    }

    protected function getTemplate(AbstractTreeNode $nodeType, AbstractOutputType $outputType): string
    {
        if (isset($this->templateCache[$nodeType::getType()])) {
            return $this->templateCache[$nodeType::getType()];
        }
        $templateHierarchy = [
            'treenode_'.$outputType::getType().'_'.$nodeType->getTemplateTypeName(),
            'treenode_'.$outputType::getType().'_default',
            'treenode_'.$nodeType->getTemplateTypeName(),
            'treenode_default',
        ];

        foreach ($templateHierarchy as $template) {
            try {
                $template = $this->templateUtil->getTemplate($template);
            } catch (LoaderError $e) {
                continue;
            }

            break;
        }

        if ($template) {
            $this->templateCache[$nodeType::getType()] = $template;
        }

        return $template;
    }
}
