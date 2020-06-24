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
//			array('tl_page', 'addBreadcrumb'),
			array(\HeimrichHannot\TreeBundle\EventListener\DataContainer\TreeContainer::class, 'onLoadCallback'),
//			array('tl_page', 'makeRedirectPageMandatory'),
//			array('tl_page', 'generateSitemap')
		),
//		'oncut_callback' => array
//		(
//			array('tl_page', 'scheduleUpdate')
//		),
//		'ondelete_callback' => array
//		(
//			array('tl_page', 'purgeSearchIndex'),
//			array('tl_page', 'scheduleUpdate')
//		),
//		'onsubmit_callback' => array
//		(
//			array('tl_page', 'scheduleUpdate'),
//			array('tl_page', 'generateArticle')
//		),
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
//			'paste_button_callback'   => array('tl_page', 'pastePage'),
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
//				'button_callback'     => array('tl_page', 'editPage')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
//				'button_callback'     => array('tl_page', 'copyPage')
			),
			'copyChilds' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['copyChilds'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copychilds.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
//				'button_callback'     => array('tl_page', 'copyPageWithSubpages')
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
//				'button_callback'     => array('tl_page', 'cutPage')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
//				'button_callback'     => array('tl_page', 'deletePage')
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['toggle'],
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
//				'button_callback'     => array('tl_page', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tree']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			),
		)
	),

	// Select
//	'select' => array
//	(
//		'buttons_callback' => array
//		(
//			array('tl_page', 'addAliasButton')
//		)
//	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('type'),
		'default'                     => '{title_legend},title,alias,type;{content_legend},description;',
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['title'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['alias'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['type'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['description'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'start' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		)
	)
);

//// Disable the articles link in the modal window
//if (Input::get('popup'))
//{
//	unset($GLOBALS['TL_DCA']['tl_page']['list']['operations']['articles']);
//}

///**
// * Provide miscellaneous methods that are used by the data configuration array.
// *
// * @author Leo Feyer <https://github.com/leofeyer>
// */
//class tl_page extends Backend
//{
//	/**
//	 * Import the back end user object
//	 */
//	public function __construct()
//	{
//		parent::__construct();
//		$this->import('BackendUser', 'User');
//	}
//
//	/**
//	 * Add the breadcrumb menu
//	 */
//	public function addBreadcrumb()
//	{
//		Backend::addPagesBreadcrumb();
//	}
//


//	/**
//	 * Make sure that top-level pages are root pages
//	 *
//	 * @param mixed         $varValue
//	 * @param DataContainer $dc
//	 *
//	 * @return mixed
//	 *
//	 * @throws Exception
//	 */
//	public function checkRootType($varValue, DataContainer $dc)
//	{
//		if ($varValue != 'root' && $dc->activeRecord->pid == 0)
//		{
//			throw new Exception($GLOBALS['TL_LANG']['ERR']['topLevelRoot']);
//		}
//
//		return $varValue;
//	}
//
//	/**
//	 * Make the redirect page mandatory if the page is a logout page
//	 *
//	 * @param DataContainer $dc
//	 *
//	 * @throws Exception
//	 */
//	public function makeRedirectPageMandatory(DataContainer $dc)
//	{
//		$objPage = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
//								  ->limit(1)
//								  ->execute($dc->id);
//
//		if ($objPage->numRows && $objPage->type == 'logout')
//		{
//			$GLOBALS['TL_DCA']['tl_page']['fields']['jumpTo']['eval']['mandatory'] = true;
//		}
//	}
//
//	/**
//	 * Check for modified pages and update the XML files if necessary
//	 */
//	public function generateSitemap()
//	{
//		/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
//		$objSession = System::getContainer()->get('session');
//
//		$session = $objSession->get('sitemap_updater');
//
//		if (empty($session) || !is_array($session))
//		{
//			return;
//		}
//
//		$this->import('Automator');
//
//		foreach ($session as $id)
//		{
//			$this->Automator->generateSitemap($id);
//		}
//
//		$objSession->set('sitemap_updater', null);
//	}
//
//	/**
//	 * Schedule a sitemap update
//	 *
//	 * This method is triggered when a single page or multiple pages are
//	 * modified (edit/editAll), moved (cut/cutAll) or deleted
//	 * (delete/deleteAll). Since duplicated pages are unpublished by default,
//	 * it is not necessary to schedule updates on copyAll as well.
//	 *
//	 * @param DataContainer $dc
//	 */
//	public function scheduleUpdate(DataContainer $dc)
//	{
//		// Return if there is no ID
//		if (!$dc->activeRecord || !$dc->activeRecord->id || Input::get('act') == 'copy')
//		{
//			return;
//		}
//
//		/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
//		$objSession = System::getContainer()->get('session');
//
//		// Store the ID in the session
//		$session = $objSession->get('sitemap_updater');
//		$session[] = PageModel::findWithDetails($dc->activeRecord->id)->rootId;
//		$objSession->set('sitemap_updater', array_unique($session));
//	}
//
//
//	/**
//	 * Automatically create an article in the main column of a new page
//	 *
//	 * @param DataContainer $dc
//	 */
//	public function generateArticle(DataContainer $dc)
//	{
//		// Return if there is no active record (override all)
//		if (!$dc->activeRecord)
//		{
//			return;
//		}
//
//		// No title or not a regular page
//		if ($dc->activeRecord->title == '' || !in_array($dc->activeRecord->type, array('regular', 'error_403', 'error_404')))
//		{
//			return;
//		}
//
//		/** @var Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $objSessionBag */
//		$objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');
//
//		$new_records = $objSessionBag->get('new_records');
//
//		// Not a new page
//		if (!$new_records || !is_array($new_records[$dc->table]) || !in_array($dc->id, $new_records[$dc->table]))
//		{
//			return;
//		}
//
//		// Check whether there are articles (e.g. on copied pages)
//		$objTotal = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_article WHERE pid=?")
//								   ->execute($dc->id);
//
//		if ($objTotal->count > 0)
//		{
//			return;
//		}
//
//		// Create article
//		$arrSet['pid'] = $dc->id;
//		$arrSet['sorting'] = 128;
//		$arrSet['tstamp'] = time();
//		$arrSet['author'] = $this->User->id;
//		$arrSet['inColumn'] = 'main';
//		$arrSet['title'] = $dc->activeRecord->title;
//		$arrSet['alias'] = str_replace('/', '-', $dc->activeRecord->alias); // see #5168
//		$arrSet['published'] = $dc->activeRecord->published;
//
//		$this->Database->prepare("INSERT INTO tl_article %s")->set($arrSet)->execute();
//	}
//
//	/**
//	 * Purge the search index if a page is being deleted
//	 *
//	 * @param DataContainer $dc
//	 */
//	public function purgeSearchIndex(DataContainer $dc)
//	{
//		if (!$dc->id)
//		{
//			return;
//		}
//
//		$objResult = $this->Database->prepare("SELECT id FROM tl_search WHERE pid=?")
//									->execute($dc->id);
//
//		while ($objResult->next())
//		{
//			$this->Database->prepare("DELETE FROM tl_search WHERE id=?")
//						   ->execute($objResult->id);
//
//			$this->Database->prepare("DELETE FROM tl_search_index WHERE pid=?")
//						   ->execute($objResult->id);
//		}
//	}
//
//	/**
//	 * Check the sitemap alias
//	 *
//	 * @param mixed         $varValue
//	 * @param DataContainer $dc
//	 *
//	 * @return mixed
//	 *
//	 * @throws Exception
//	 */
//	public function checkFeedAlias($varValue, DataContainer $dc)
//	{
//		// No change or empty value
//		if ($varValue == $dc->value || $varValue == '')
//		{
//			return $varValue;
//		}
//
//		$varValue = StringUtil::standardize($varValue); // see #5096
//
//		$this->import('Automator');
//		$arrFeeds = $this->Automator->purgeXmlFiles(true);
//
//		// Alias exists
//		if (in_array($varValue, $arrFeeds))
//		{
//			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
//		}
//
//		return $varValue;
//	}
//
//	/**
//	 * Prevent circular references
//	 *
//	 * @param mixed         $varValue
//	 * @param DataContainer $dc
//	 *
//	 * @return mixed
//	 *
//	 * @throws Exception
//	 */
//	public function checkJumpTo($varValue, DataContainer $dc)
//	{
//		if ($varValue == $dc->id)
//		{
//			throw new Exception($GLOBALS['TL_LANG']['ERR']['circularReference']);
//		}
//
//		return $varValue;
//	}
//
//	/**
//	 * Check the DNS settings
//	 *
//	 * @param mixed $varValue
//	 *
//	 * @return mixed
//	 */
//	public function checkDns($varValue)
//	{
//		return str_ireplace(array('http://', 'https://', 'ftp://'), '', $varValue);
//	}
//
//	/**
//	 * Return all page layouts grouped by theme
//	 *
//	 * @return array
//	 */
//	public function getPageLayouts()
//	{
//		$objLayout = $this->Database->execute("SELECT l.id, l.name, t.name AS theme FROM tl_layout l LEFT JOIN tl_theme t ON l.pid=t.id ORDER BY t.name, l.name");
//
//		if ($objLayout->numRows < 1)
//		{
//			return array();
//		}
//
//		$return = array();
//
//		while ($objLayout->next())
//		{
//			$return[$objLayout->theme][$objLayout->id] = $objLayout->name;
//		}
//
//		return $return;
//	}
//
//	/**
//	 * Return the edit page button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 *
//	 * @return string
//	 */
//	public function editPage($row, $href, $label, $title, $icon, $attributes)
//	{
//		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
//	}
//
//	/**
//	 * Return the copy page button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 * @param string $table
//	 *
//	 * @return string
//	 */
//	public function copyPage($row, $href, $label, $title, $icon, $attributes, $table)
//	{
//		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
//		{
//			return '';
//		}
//
//		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
//	}
//
//	/**
//	 * Return the copy page with subpages button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 * @param string $table
//	 *
//	 * @return string
//	 */
//	public function copyPageWithSubpages($row, $href, $label, $title, $icon, $attributes, $table)
//	{
//		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
//		{
//			return '';
//		}
//
//		$objSubpages = $this->Database->prepare("SELECT * FROM tl_page WHERE pid=?")
//									  ->limit(1)
//									  ->execute($row['id']);
//
//		return ($objSubpages->numRows && $this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
//	}
//
//	/**
//	 * Return the cut page button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 *
//	 * @return string
//	 */
//	public function cutPage($row, $href, $label, $title, $icon, $attributes)
//	{
//		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
//	}
//
//	/**
//	 * Return the paste page button
//	 *
//	 * @param DataContainer $dc
//	 * @param array         $row
//	 * @param string        $table
//	 * @param boolean       $cr
//	 * @param array         $arrClipboard
//	 *
//	 * @return string
//	 */
//	public function pastePage(DataContainer $dc, $row, $table, $cr, $arrClipboard=null)
//	{
//		$disablePA = false;
//		$disablePI = false;
//
//		// Disable all buttons if there is a circular reference
//		if ($arrClipboard !== false && (($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id'])) || ($arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id'])))))
//		{
//			$disablePA = true;
//			$disablePI = true;
//		}
//
//		// Prevent adding non-root pages on top-level
//		if (Input::get('mode') != 'create' && $row['pid'] == 0)
//		{
//			$objPage = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
//									  ->limit(1)
//									  ->execute(Input::get('id'));
//
//			if ($objPage->type != 'root')
//			{
//				$disablePA = true;
//
//				if ($row['id'] == 0)
//				{
//					$disablePI = true;
//				}
//			}
//		}
//
//		// Check permissions if the user is not an administrator
//		if (!$this->User->isAdmin)
//		{
//			// Disable "paste into" button if there is no permission 2 (move) or 1 (create) for the current page
//			if (!$disablePI)
//			{
//				if (!$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row) || (Input::get('mode') == 'create' && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $row)))
//				{
//					$disablePI = true;
//				}
//			}
//
//			$objPage = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
//									  ->limit(1)
//									  ->execute($row['pid']);
//
//			// Disable "paste after" button if there is no permission 2 (move) or 1 (create) for the parent page
//			if (!$disablePA && $objPage->numRows)
//			{
//				if (!$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $objPage->row()) || (Input::get('mode') == 'create' && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row())))
//				{
//					$disablePA = true;
//				}
//			}
//
//			// Disable "paste after" button if the parent page is a root page and the user is not an administrator
//			if (!$disablePA && ($row['pid'] < 1 || in_array($row['id'], $dc->rootIds)))
//			{
//				$disablePA = true;
//			}
//		}
//
//		$return = '';
//
//		// Return the buttons
//		$imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
//		$imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));
//
//		if ($row['id'] > 0)
//		{
//			$return = $disablePA ? Image::getHtml('pasteafter_.svg') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a> ';
//		}
//
//		return $return . ($disablePI ? Image::getHtml('pasteinto_.svg') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto . '</a> ');
//	}
//
//	/**
//	 * Return the delete page button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 *
//	 * @return string
//	 */
//	public function deletePage($row, $href, $label, $title, $icon, $attributes)
//	{
//		$root = func_get_arg(7);
//
//		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_DELETE_PAGE, $row) && ($this->User->isAdmin || !in_array($row['id'], $root))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
//	}
//
//	/**
//	 * Automatically generate the folder URL aliases
//	 *
//	 * @param array         $arrButtons
//	 * @param DataContainer $dc
//	 *
//	 * @return array
//	 */
//	public function addAliasButton($arrButtons, DataContainer $dc)
//	{
//		if (!$this->User->hasAccess('tl_page::alias', 'alexf'))
//		{
//			return $arrButtons;
//		}
//
//		// Generate the aliases
//		if (Input::post('FORM_SUBMIT') == 'tl_select' && isset($_POST['alias']))
//		{
//			/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
//			$objSession = System::getContainer()->get('session');
//
//			$session = $objSession->all();
//			$ids = $session['CURRENT']['IDS'];
//
//			foreach ($ids as $id)
//			{
//				$objPage = PageModel::findWithDetails($id);
//
//				if ($objPage === null)
//				{
//					continue;
//				}
//
//				$dc->id = $id;
//				$dc->activeRecord = $objPage;
//
//				$strAlias = '';
//
//				// Generate new alias through save callbacks
//				foreach ($GLOBALS['TL_DCA'][$dc->table]['fields']['alias']['save_callback'] as $callback)
//				{
//					if (is_array($callback))
//					{
//						$this->import($callback[0]);
//						$strAlias = $this->{$callback[0]}->{$callback[1]}($strAlias, $dc);
//					}
//					elseif (is_callable($callback))
//					{
//						$strAlias = $callback($strAlias, $dc);
//					}
//				}
//
//				// The alias has not changed
//				if ($strAlias == $objPage->alias)
//				{
//					continue;
//				}
//
//				// Initialize the version manager
//				$objVersions = new Versions('tl_page', $id);
//				$objVersions->initialize();
//
//				// Store the new alias
//				$this->Database->prepare("UPDATE tl_page SET alias=? WHERE id=?")
//							   ->execute($strAlias, $id);
//
//				// Create a new version
//				$objVersions->create();
//			}
//
//			$this->redirect($this->getReferer());
//		}
//
//		// Add the button
//		$arrButtons['alias'] = '<button type="submit" name="alias" id="alias" class="tl_submit" accesskey="a">' . $GLOBALS['TL_LANG']['MSC']['aliasSelected'] . '</button> ';
//
//		return $arrButtons;
//	}
//
//	/**
//	 * Return the "toggle visibility" button
//	 *
//	 * @param array  $row
//	 * @param string $href
//	 * @param string $label
//	 * @param string $title
//	 * @param string $icon
//	 * @param string $attributes
//	 *
//	 * @return string
//	 */
//	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
//	{
//		if (strlen(Input::get('tid')))
//		{
//			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
//			$this->redirect($this->getReferer());
//		}
//
//		// Check permissions AFTER checking the tid, so hacking attempts are logged
//		if (!$this->User->hasAccess('tl_page::published', 'alexf'))
//		{
//			return '';
//		}
//
//		$href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
//
//		if (!$row['published'])
//		{
//			$icon = 'invisible.svg';
//		}
//
//		$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
//								  ->limit(1)
//								  ->execute($row['id']);
//
//		if (!$this->User->hasAccess($row['type'], 'alpty') || !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row()))
//		{
//			return Image::getHtml($icon) . ' ';
//		}
//
//		return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
//	}
//
//	/**
//	 * Disable/enable a user group
//	 *
//	 * @param integer       $intId
//	 * @param boolean       $blnVisible
//	 * @param DataContainer $dc
//	 *
//	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
//	 */
//	public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
//	{
//		// Set the ID and action
//		Input::setGet('id', $intId);
//		Input::setGet('act', 'toggle');
//
//		if ($dc)
//		{
//			$dc->id = $intId; // see #8043
//		}
//
//		// Trigger the onload_callback
//		if (is_array($GLOBALS['TL_DCA']['tl_page']['config']['onload_callback']))
//		{
//			foreach ($GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'] as $callback)
//			{
//				if (is_array($callback))
//				{
//					$this->import($callback[0]);
//					$this->{$callback[0]}->{$callback[1]}($dc);
//				}
//				elseif (is_callable($callback))
//				{
//					$callback($dc);
//				}
//			}
//		}
//
//		// Check the field access
//		if (!$this->User->hasAccess('tl_page::published', 'alexf'))
//		{
//			throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish page ID ' . $intId . '.');
//		}
//
//		// Set the current record
//		if ($dc)
//		{
//			$objRow = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
//									 ->limit(1)
//									 ->execute($intId);
//
//			if ($objRow->numRows)
//			{
//				$dc->activeRecord = $objRow;
//			}
//		}
//
//		$objVersions = new Versions('tl_page', $intId);
//		$objVersions->initialize();
//
//		// Trigger the save_callback
//		if (is_array($GLOBALS['TL_DCA']['tl_page']['fields']['published']['save_callback']))
//		{
//			foreach ($GLOBALS['TL_DCA']['tl_page']['fields']['published']['save_callback'] as $callback)
//			{
//				if (is_array($callback))
//				{
//					$this->import($callback[0]);
//					$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
//				}
//				elseif (is_callable($callback))
//				{
//					$blnVisible = $callback($blnVisible, $dc);
//				}
//			}
//		}
//
//		$time = time();
//
//		// Update the database
//		$this->Database->prepare("UPDATE tl_page SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
//					   ->execute($intId);
//
//		if ($dc)
//		{
//			$dc->activeRecord->tstamp = $time;
//			$dc->activeRecord->published = ($blnVisible ? '1' : '');
//		}
//
//		// Trigger the onsubmit_callback
//		if (is_array($GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback']))
//		{
//			foreach ($GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'] as $callback)
//			{
//				if (is_array($callback))
//				{
//					$this->import($callback[0]);
//					$this->{$callback[0]}->{$callback[1]}($dc);
//				}
//				elseif (is_callable($callback))
//				{
//					$callback($dc);
//				}
//			}
//		}
//
//		$objVersions->create();
//
//		// The onsubmit_callback has triggered scheduleUpdate(), so run generateSitemap() now
//		$this->generateSitemap();
//	}
//}
