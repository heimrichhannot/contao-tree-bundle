<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['palettes'][\HeimrichHannot\TreeBundle\ContentElement\TreeElement::TYPE] =
    '{type_legend},type,headline;{tree_legend},huhTree;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop;';

$dca['fields']['huhTree'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['huh_tree'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [\HeimrichHannot\TreeBundle\EventListener\DataContainer\ContentContainer::class, 'onHuhTreeOptionsCallback'],
    'eval'             => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'              => "int(10) unsigned NOT NULL default '0'"
];