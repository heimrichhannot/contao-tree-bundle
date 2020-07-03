<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use Contao\MemberModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Event\ModifiyNodeLabelCallback;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class MembersNode extends AbstractTreeNode
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * MembersNode constructor.
     */
    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public static function getType(): string
    {
        return 'members';
    }

    public function onLabelCallback(ModifiyNodeLabelCallback $event): string
    {
        if (!isset($event->getRow()['members'])) {
            return parent::onLabelCallback($event);
        }
        $memberIds = StringUtil::deserialize($event->getRow()['members']);
        /** @var Collection|MemberModel|MemberModel[]|null $memberModels */
        if (!$memberIds || !($memberModels = $this->modelUtil->findMultipleModelInstancesByIds('tl_member', $memberIds))) {
            return parent::onLabelCallback($event);
        }
        $memberData = [];
        $i = 0;

        foreach ($memberModels as $memberModel) {
            ++$i;
            $memberData[] = $memberModel->firstname.' '.$memberModel->lastname;

            if (3 === $i) {
                break;
            }
        }

        if ($memberModels->count() > 3) {
            $memberData[] = '…';
        }

        $label = $event->getLabel();

        if ($event->getHasChilds()) {
            $margin = '40';
        } else {
            $margin = '20';
        }

        $label .= '<br /><span style="font-style: italic;display: inline-block;margin-left: '.$margin.'px;margin-top: 5px;">'.implode(', ', $memberData).'</span>';

        return $label;
    }

    public function onBeforeRenderEvent(BeforeRenderNodeEvent $event): void
    {
        $memberIds = StringUtil::deserialize($event->getTreeModel()->members);
        /** @var Collection|MemberModel[]|MemberModel|null $memberModels */
        if (!$memberIds || !($memberModels = $this->modelUtil->findMultipleModelInstancesByIds('tl_member', $memberIds, ['order' => "FIELD(id, '".implode("', '", $memberIds)."')"]))) {
            return;
        }
        $context = $event->getContext();
        $context['members'] = $memberModels->fetchAll();
        $event->setContext($context);
    }

    protected function getPalette(): string
    {
        return '{content_legend},members,description;';
    }
}
