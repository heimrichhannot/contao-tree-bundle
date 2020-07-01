<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use Contao\MemberGroupModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Event\ModifiyNodeLabelCallback;
use HeimrichHannot\UtilsBundle\Member\MemberUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class MemberGroupsNode extends AbstractTreeNode
{
    protected $iconHidden = 'mgroup_.svg';
    protected $iconPublished = 'mgroup.svg';
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var MemberUtil
     */
    protected $memberUtil;

    /**
     * MemberGroupNode constructor.
     */
    public function __construct(ModelUtil $modelUtil, MemberUtil $memberUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->memberUtil = $memberUtil;
    }

    public static function getType(): string
    {
        return 'member_groups';
    }

    public function onLabelCallback(ModifiyNodeLabelCallback $event): string
    {
        if (!isset($event->getRow()['groups'])) {
            return parent::onLabelCallback($event);
        }
        $groupIds = StringUtil::deserialize($event->getRow()['groups']);

        /** @var Collection|MemberGroupModel[]|MemberGroupModel $groups */
        if (empty($groupIds) || !($groups = $this->modelUtil->findMultipleModelInstancesByIds('tl_member_group', $groupIds))) {
            return parent::onLabelCallback($event);
        }
        $label = $event->getLabel();

        if ($event->getHasChilds()) {
            $margin = '40';
        } else {
            $margin = '20';
        }
        $label .= '<br /><span style="font-style: italic;display: inline-block;margin-left: '.$margin.'px;margin-top: 5px;">'.implode(',', $groups->fetchEach('name')).'</span>';

        return $label;
    }

    public function onBeforeRenderEvent(BeforeRenderNodeEvent $event): void
    {
        $groupIds = StringUtil::deserialize($event->getTreeModel()->groups);

        if (empty($groupIds)) {
            return;
        }
        /** @var Collection|MemberGroupModel[]|MemberGroupModel|null $groups */
        $groups = $this->modelUtil->findMultipleModelInstancesByIds('tl_member_group', $groupIds);

        if (!$groups) {
            return;
        }
        $context = $event->getContext();

        foreach ($groups as $group) {
            $context['membergroups'][$group->id] = $group->row();
            $members = $this->memberUtil->findActiveByGroups([$group->id], ['ignoreLogin' => true]);

            if ($members) {
                foreach ($members as $member) {
                    $context['membergroups'][$group->id]['members'][] = $member->row();
                }
            }
        }
        $event->setContext($context);
    }

    protected function getPalette(): string
    {
        return '{content_legend},groups,description;';
    }
}
