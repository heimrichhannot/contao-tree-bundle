<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use HeimrichHannot\TreeBundle\Generator\TreeGenerator;
use HeimrichHannot\TreeBundle\Model\TreeModel;

/**
 * @property int $huhTree
 */
class TreeElement extends ContentElement
{
    const TYPE = 'huh_tree_element';

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_tree_default';

    protected function compile()
    {
        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            $this->Template = new BackendTemplate('be_wildcard');
            $tree = TreeModel::findByPk($this->huhTree);

            if ($tree) {
                $this->Template->title = $tree->internalTitle;
            }

            return;
        }

        $tree = System::getContainer()->get(TreeGenerator::class)->renderTree((int) $this->huhTree);
        $this->Template->tree = $tree;
    }
}
