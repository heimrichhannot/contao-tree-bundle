<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer;
use HeimrichHannot\TreeBundle\OutputType\ListOutputType;
use HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode;
use HeimrichHannot\TreeBundle\TreeNode\SimpleNode;

$GLOBALS['TL_DCA']['tl_tree'] = [
    // Config
    'config' => [
        'label' => '<b>'.($GLOBALS['TL_LANG']['tl_tree']['TYPES']['mainroot'] ?? 'Root').'</b> '.\Contao\Config::get('websiteTitle'),
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onload_callback' => [
            [TreeContainer::class, 'onLoadCallback'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
//				'alias' => 'index',
//				'pid,type,start,stop,published' => 'index'
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 5,
            'icon' => 'bundles/heimrichhannottree/img/backend/tree.svg',
            'paste_button_callback' => [TreeContainer::class, 'onPasteButtonCallback'],
            'panelLayout' => 'filter;search',
            'child_record_class' => 'tl_tree',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
            'label_callback' => [TreeContainer::class, 'onLabelCallback'],
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href' => 'ptg=all',
                'class' => 'header_toggle',
                'showOnSelect' => true,
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
                'button_callback' => [TreeContainer::class, 'onEditButtonCallback'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => [TreeContainer::class, 'onCopyButtonCallback'],
            ],
            'copyChilds' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['copyChilds'],
                'href' => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon' => 'copychilds.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => [TreeContainer::class, 'onCopyChildsButtonCallback'],
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => [TreeContainer::class, 'onCutButtonCallback'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [TreeContainer::class, 'onDeleteButtonCallback'],
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['toggle'],
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [TreeContainer::class, 'onToggleButtonCallback'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_tree']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type'],
        'default' => AbstractTreeNode::PREPEND_PALETTE
            .'{content_legend},description;'
            .AbstractTreeNode::APPEND_PALETTE,
    ],

    // Subpalettes
    'subpalettes' => [
    ],

    // Fields
    'fields' => [
        'id' => [
            'label' => ['ID'],
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'internalTitle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['internalTitle'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'folderalias', 'doNotCopy' => true, 'maxlength' => 128, 'tl_class' => 'w50 clr'],
            'save_callback' => [
                [TreeContainer::class, 'generateAlias'],
            ],
            'sql' => "varchar(255) COLLATE utf8_bin NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['type'],
            'default' => SimpleNode::getType(),
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [TreeContainer::class, 'onTypeOptionsCallback'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tree']['TYPES'],
            'save_callback' => [
                [TreeContainer::class, 'onTypeSaveCallback'],
            ],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'outputType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['outputType'],
            'default' => ListOutputType::getType(),
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [TreeContainer::class, 'onOutputTypeOptionsCallback'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tree']['OUTPUTTYPES'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'member' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['member'],
            'exclude' => false,
            'filter' => false,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'select',
            'foreignKey' => 'tl_member.CONCAT(firstname,\' \',lastname)',
            'eval' => ['mandatory' => true, 'multiple' => false, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'members' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['members'],
            'exclude' => false,
            'filter' => false,
            'inputType' => 'checkboxWizard',
            'foreignKey' => 'tl_member.CONCAT(firstname,\' \',lastname)',
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'group' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['group'],
            'exclude' => false,
            'filter' => false,
            'sorting' => false,
            'flag' => 1,
            'inputType' => 'select',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => false, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['description'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'decodeEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'groups' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['groups'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_tree']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
