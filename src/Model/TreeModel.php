<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\Model;


use Contao\Model;

/**
 * Class TreeModel
 * @package HeimrichHannot\TreeBundle\Model
 *
 * @property int $id;
 * @property int $pid;
 * @property string $title;
 * @property string $internalTitle;
 * @property string $alias;
 * @property string $type;
 */
class TreeModel extends Model
{
    protected static $strTable = 'tl_tree';
}