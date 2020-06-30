<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\TreeNode;

use HeimrichHannot\TreeBundle\Model\TreeModel;

class SimpleNode extends AbstractTreeNode
{
    public static function getType(): string
    {
        return 'simple';
    }

    public function prepareNodeOutput(array $context, TreeModel $nodeModel): array
    {
        return $context;
    }

    protected function getPalette(): string
    {
        return '{content_legend},description;';
    }
}
