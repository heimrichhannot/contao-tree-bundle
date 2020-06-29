<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use HeimrichHannot\TreeBundle\Model\TreeModel;

class MemberGroupNode extends AbstractTreeNode
{
    protected $iconHidden = 'mgroup_.svg';
    protected $iconPublished = 'mgroup.svg';

    public static function getType(): string
    {
        return 'member_group_node';
    }

    protected function getPalette(): string
    {
        return '{content_legend},groups,description;';
    }

    public function prepareNodeOutput(array $context, TreeModel $nodeModel): array
    {
        return $context;
    }
}
