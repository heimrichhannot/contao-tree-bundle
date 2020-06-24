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
use Contao\Model\Collection;

/**
 * Class TreeModel
 * @package HeimrichHannot\TreeBundle\Model
 *
 * @property int $id;
 * @property int $pid;
 * @property int $sorting;
 * @property int $tstamp;
 * @property string $title;
 * @property string $internalTitle;
 * @property string $alias;
 * @property string $type;
 * @property int $member;
 * @property string $description;
 *
 * @method static Collection|TreeModel|null findByPid($pid, array $opt=array())
 */
class TreeModel extends Model
{
    protected static $strTable = 'tl_tree';

    /**
     * Return if node is a root node (has no pid).
     *
     * @return bool
     */
    public function isRootNode(): bool
    {
        return $this->pid == 0;
    }
}