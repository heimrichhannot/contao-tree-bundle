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


class SimpleNode implements TreeNodeInterface
{

    public static function getType(): string
    {
        return 'simple_node';
    }

    public static function getPalette(): string
    {
        return '{content_legend},description;';
    }
}