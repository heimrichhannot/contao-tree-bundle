<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\EventListener\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\TreeBundle\Model\TreeModel;

class UserContainer
{
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
