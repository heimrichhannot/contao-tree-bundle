<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

\Contao\Controller::loadLanguageFile('tl_user');

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

$dca['palettes']['default'] = str_replace('fop;', 'fop;{tree_legend},rootNodeMounts,huh_treep;', $dca['palettes']['default']);

$dca['fields']['rootNodeMounts'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['rootNodeMounts'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options_callback' => [\HeimrichHannot\TreeBundle\EventListener\DataContainer\UserContainer::class, 'onRootNodeMountsOptionsCallback'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['huh_treep'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['huh_treep'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['createRoot', 'create', 'deleteRoot', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];