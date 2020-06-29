<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\ContentElement;


use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\TreeBundle\Generator\TreeGenerator;
use HeimrichHannot\TreeBundle\Model\TreeModel;

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

        $tree = System::getContainer()->get(TreeGenerator::class)->renderTree((int)$this->huhTree);
        $this->Template->tree = $tree;
    }
}