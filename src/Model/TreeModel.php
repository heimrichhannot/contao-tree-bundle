<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * Class TreeModel.
 *
 * @property int    $id;
 * @property int    $pid;
 * @property int    $sorting;
 * @property int    $tstamp;
 * @property string $title;
 * @property string $internalTitle;
 * @property string $alias;
 * @property string $type;
 * @property string $outputType;
 * @property int    $member;
 * @property int    $members;
 * @property int    $group;
 * @property string $groups;
 * @property string $description;
 * @property bool   $published
 * @property string $start
 * @property string $stop
 *
 * @method static Collection|TreeModel|null findByPid($pid, array $opt=array())
 */
class TreeModel extends Model
{
    protected static $strTable = 'tl_tree';

    /**
     * Return if node is a root node (has no pid).
     */
    public function isRootNode(): bool
    {
        return 0 == $this->pid;
    }

    public function getRootNode(): ?self
    {
        foreach (static::findParentsById($this->id) as $parent) {
            if ($parent->pid > 0) {
                continue;
            }

            return $parent;
        }

        return null;
    }

    /**
     * Find the parent pages of a page.
     *
     * @param int $intId The page's ID
     *
     * @return Collection|TreeModel[]|TreeModel|null A collection of models or null if there are no parent pages
     */
    public static function findParentsById($intId)
    {
        $arrModels = [];

        while ($intId > 0 && null !== ($objPage = static::findByPk($intId))) {
            $intId = $objPage->pid;
            $arrModels[] = $objPage;
        }

        if (empty($arrModels)) {
            return null;
        }

        return static::createCollection($arrModels, 'tl_tree');
    }

    public static function findPublishedRootNodes($options)
    {
        $t = static::$strTable;
        $columns = ['pid=0'];

        if (!static::isPreviewMode($options)) {
            $time = \Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        return static::findBy($columns, null, $options);
    }
}
