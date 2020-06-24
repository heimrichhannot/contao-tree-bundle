<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\EventListener\DataContainer;


use Contao\DataContainer;
use HeimrichHannot\TreeBundle\Model\TreeModel;

class UserContainer
{
    /**
     * @param DataContainer $dc
     */
    public function onRootNodeMountsOptionsCallback(DataContainer $dc): array
    {
        $options = [];
        $rootNodes = TreeModel::findByPid(0);
        if ($rootNodes) {
            /** @var TreeModel $node */
            foreach ($rootNodes as $node) {
                $options[$node->id] = $node->internalTitle;
            }
        }
        return $options;
    }
}