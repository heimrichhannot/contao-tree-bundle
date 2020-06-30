<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use Contao\MemberModel;
use HeimrichHannot\TreeBundle\Event\ModifiyNodeLabelEvent;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class MemberNode extends AbstractTreeNode
{
    protected $iconHidden = 'member_.svg';
    protected $iconPublished = 'member.svg';
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * MemberNode constructor.
     */
    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public static function getType(): string
    {
        return 'member';
    }

    public function prepareNodeOutput(array $context, TreeModel $nodeModel): array
    {
        return $context;
    }

    public function onLabelCallback(ModifiyNodeLabelEvent $event): string
    {
        /** @var MemberModel $member */
        if (!isset($event->getRow()['member']) || !($member = $this->modelUtil->findModelInstanceByPk('tl_member', $event->getRow()['member']))) {
            return parent::onLabelCallback($event); // TODO: Change the autogenerated stub
        }
        $label = $event->getLabel();

        if ($event->getHasChilds()) {
            $margin = '40';
        } else {
            $margin = '20';
        }

        $label .= '<br /><span style="font-style: italic;display: inline-block;margin-left: '.$margin.'px;margin-top: 5px;">'.$member->firstname.' '.$member->lastname.'</span>';

        return $label;
    }

    protected function getPalette(): string
    {
        return '{content_legend},member,description;';
    }
}
