<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

class MemberGroupNode extends AbstractTreeNode
{
    public static function getType(): string
    {
        return 'member_group_node';
    }

    protected function getPalette(): string
    {
        return '{content_legend},groups,description;';
    }
}
