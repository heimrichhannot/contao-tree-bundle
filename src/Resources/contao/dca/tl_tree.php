<?php

$GLOBALS['TL_DCA']['tl_tree'] = array
(
	// Config
	'config' => array
	(
        'label'                       => '<b>'.$GLOBALS['TL_LANG']['tl_tree']['TYPES']['mainroot'].'</b> '.Config::get('websiteTitle'),
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onLoadCallback'),
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
//				'alias' => 'index',
//				'pid,type,start,stop,published' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'               => 5,
            'icon'               => 'pagemounts.svg',
			'paste_button_callback'   => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onPasteButtonCallback'),
            'panelLayout'        => 'filter;search',
            'child_record_class' => 'tl_tree',
        ),
        'label' => array
        (
            'fields'         => array('title'),
            'format'         => '%s',
            'label_callback' => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onLabelCallback'),
        ),
		'global_operations' => array
		(
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
				'href'                => 'ptg=all',
				'class'               => 'header_toggle',
				'showOnSelect'        => true
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onEditButtonCallback')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onCopyButtonCallback')
			),
			'copyChilds' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['copyChilds'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copychilds.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onCopyChildsButtonCallback')
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onCutButtonCallback')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onDeleteButtonCallback')
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['toggle'],
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onToggleButtonCallback')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			),
		)
	),

    // Palettes
    'palettes' => array
    (
        '__selector__' => array('type'),
        'default'      => \HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode::PREPEND_PALETTE
            . '{content_legend},description;'
            . \HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode::APPEND_PALETTE,
    ),

	// Subpalettes
	'subpalettes' => array
	(
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'label'                   => array('ID'),
			'search'                  => true,
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'internalTitle' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['internalTitle'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['alias'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'folderalias', 'doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'w50 clr'),
			'save_callback' => array
			(
				array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'generateAlias')
			),
			'sql'                     => "varchar(255) COLLATE utf8_bin NOT NULL default ''"
		),
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['type'],
			'default'                 => 'regular',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options_callback'        => array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onTypeOptionsCallback'),
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_tree']['TYPES'],
			'save_callback' => array
			(
				array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onTypeSaveCallback')
			),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
        'member' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['member'],
            'exclude'                 => false,
            'filter'                  => true,
            'sorting'                 => true,
            'flag'             => 1,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_member.firstname',
            'eval'                    => array('mandatory'=>true, 'multiple'=>false, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['description'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
//		'groups' => array
//		(
//			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['groups'],
//			'exclude'                 => true,
//			'filter'                  => true,
//			'inputType'               => 'checkbox',
//			'foreignKey'              => 'tl_member_group.name',
//			'eval'                    => array('mandatory'=>true, 'multiple'=>true),
//			'sql'                     => "blob NULL",
//			'relation'                => array('type'=>'hasMany', 'load'=>'lazy')
//		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'start' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tree']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		)
	)
);