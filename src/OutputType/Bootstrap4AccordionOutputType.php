<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\OutputType;

use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class Bootstrap4AccordionOutputType extends AbstractOutputType
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public static function getType(): string
    {
        return 'bs4accordion';
    }

    public function onBeforeRenderEvent(BeforeRenderNodeEvent $event): void
    {
        $context = $event->getContext();

        /** @var TreeModel $parentNodeModel */
        $parentNodeModel = $this->modelUtil->findModelInstanceByPk('tl_tree', $event->getTreeModel()->pid);

        if ($parentNodeModel) {
            $context['parentNode'] = $parentNodeModel->row();
        }
        $event->setContext($context);
    }
}
