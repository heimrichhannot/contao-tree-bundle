<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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