<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

class MemberNode extends AbstractTreeNode
{
    protected $iconHidden = 'member_.svg';
    protected $iconPublished = 'member.svg';

    public static function getType(): string
    {
        return 'member_node';
    }

    protected function getPalette(): string
    {
        return '{content_legend},member,description;';
    }
}
