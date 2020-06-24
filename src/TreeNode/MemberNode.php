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


class MemberNode implements TreeNodeInterface
{

    public static function getType(): string
    {
        return 'member_node';
    }

    public static function getPalette(): string
    {
        return '{content_legend},member,description;';
    }

    public static function allowedChilds(): ?array
    {
        return null;
    }

    public static function disallowRoot(): bool
    {
        return false;
    }
}