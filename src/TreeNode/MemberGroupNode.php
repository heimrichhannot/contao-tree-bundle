<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use Contao\MemberGroupModel;
use Contao\Model\Collection;
use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;
use HeimrichHannot\TreeBundle\Event\ModifiyNodeLabelCallback;
use HeimrichHannot\UtilsBundle\Member\MemberUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class MemberGroupNode extends AbstractTreeNode
{
    protected $iconHidden = 'group_.svg';
    protected $iconPublished = 'group.svg';
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
        return 'member_group';
    }

    public function onLabelCallback(ModifiyNodeLabelCallback $event): string
    {
        if (!isset($event->getRow()['group'])) {
            return parent::onLabelCallback($event);
        }
        /** @var Collection|MemberGroupModel[]|MemberGroupModel $groups */
        if (!($group = $this->modelUtil->findModelInstanceByPk('tl_member_group', $event->getRow()['group']))) {
            return parent::onLabelCallback($event);
        }
        $label = $event->getLabel();

        if ($event->getHasChilds()) {
            $margin = '40';
        } else {
            $margin = '20';
        }
        $label .= '<br /><span style="font-style: italic;display: inline-block;margin-left: '.$margin.'px;margin-top: 5px;">'.$group->name.'</span>';

        return $label;
    }

    public function onBeforeRenderEvent(BeforeRenderNodeEvent $event): void
    {
        /** @var Collection|MemberGroupModel[]|MemberGroupModel|null $groups */
        $group = $this->modelUtil->findModelInstanceByPk('tl_member_group', $event->getTreeModel()->group);

        if (!$group) {
            return;
        }
        $context = $event->getContext();

        $context['membergroup'] = $group->row();
        $members = $this->memberUtil->findActiveByGroups([$group->id], ['ignoreLogin' => true]);

        if ($members) {
            foreach ($members as $member) {
                $context['membergroup']['members'][] = $member->row();
            }
        }
        $event->setContext($context);
    }

    protected function getPalette(): string
    {
        return '{content_legend},group,description;';
    }
}
