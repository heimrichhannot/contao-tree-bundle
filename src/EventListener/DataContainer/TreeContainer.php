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


use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Exception;
use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TreeContainer
{
    const PREPEND_PALETTE = '{title_legend},title,alias,type;';

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var NodeTypeCollection
     */
    private $nodeTypeCollection;
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(Connection $connection, NodeTypeCollection $collection, SessionInterface $session)
    {
        $this->connection         = $connection;
        $this->nodeTypeCollection = $collection;
        $this->session = $session;
    }

    /**
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
        $this->checkPermission();
        $node = TreeModel::findByPk($dc->id);
        if (0 == $node->pid) {
            $palettes = &$GLOBALS['TL_DCA']['tl_tree']['palettes'];
            $palettes[$node->type] = str_replace(',title,',',title,internalTitle,', $palettes[$node->type]);
        }
        $this->setRootType($dc);
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @throws Exception
     */
    public function onTypeSaveCallback($varValue, DataContainer $dc): string
    {
        if ($dc->activeRecord->pid == 0) {
            if (!in_array($varValue, $this->nodeTypeCollection->getRootNodeTypes())) {
                throw new Exception($GLOBALS['TL_LANG']['ERR']['huh_tree_topLevelNode']);
            }
        } else {
            $parentNodeModel = TreeModel::findByPk($dc->pid);
            if ($parentNodeModel && ($parentNodeType = $this->nodeTypeCollection->getNodeType($parentNodeModel->type))) {
                $allowedNodeTypes = $parentNodeType::allowedChilds();
                if (!in_array($varValue, $allowedNodeTypes)) {
                    throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['huh_tree_nodeTypeNotAllowed'], $varValue));
                }
            }
        }
        return $varValue;
    }


    /**
	 * Make new top-level nodes root nodes
	 *
	 * @param DataContainer $dc
	 */
	public function setRootType(DataContainer $dc)
	{
		if (Input::get('act') != 'create')
		{
			return;
		}
		$rootNodeTypes = $this->nodeTypeCollection->getRootNodeTypes();
		$defaultRootPageType = reset($rootNodeTypes);

		// Insert into
		if (Input::get('pid') == 0)
		{
			$GLOBALS['TL_DCA']['tl_tree']['fields']['type']['default'] = $defaultRootPageType;
		}
		elseif (Input::get('mode') == 1)
		{
		    $stmt = $this->connection->prepare("SELECT * FROM " . $dc->table . " WHERE id=? LIMIT 1");
		    $stmt->execute([Input::get('pid')]);

			$node = $stmt->fetch(FetchMode::CUSTOM_OBJECT);

			if ($node->pid == 0)
			{
				$GLOBALS['TL_DCA']['tl_tree']['fields']['type']['default'] = $defaultRootPageType;
			}
		}
	}

	/**
	 * Auto-generate a page alias if it has not been set yet
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate an alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = StringUtil::generateAlias($dc->activeRecord->title);

			// Generate folder URL aliases (see #4933)
//			if (Config::get('folderUrl'))
//			{
//				$objPage = PageModel::findWithDetails($dc->activeRecord->id);
//
//				if ($objPage->folderUrl != '')
//				{
//					$varValue = $objPage->folderUrl . $varValue;
//				}
//			}
		}

        $stmt = $this->connection->prepare("SELECT id FROM tl_tree WHERE id=? OR alias=?");
        $stmt->execute([$dc->id, $varValue]);

		// Check whether the page alias exists
		if ($stmt->rowCount() > ($autoAlias ? 0 : 1))
		{
            if (!$autoAlias)
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-' . $dc->id;
		}

		return $varValue;
	}

    /**
	 * Returns all allowed node types as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function onTypeOptionsCallback(DataContainer $dc)
	{
	    $user = BackendUser::getInstance();
		$arrOptions = array();
        $allowedNodeTypes = null;

		if ($dc->activeRecord && $dc->activeRecord->pid == 0) {
		    $nodeTypes = $this->nodeTypeCollection->getRootNodeTypes();
        } else {
		    $nodeTypes = $this->nodeTypeCollection->getBranchNodeTypes();
		    $parentNodeModel = TreeModel::findByPk($dc->pid);
		    if ($parentNodeModel && ($parentNodeType = $this->nodeTypeCollection->getNodeType($parentNodeModel->type))) {
		        $allowedNodeTypes = $parentNodeType::allowedChilds();
            }
        }

		foreach ($nodeTypes as $nodeType)
        {
			// Allow the currently selected option and anything the user has access to
			if ($nodeType == $dc->value || $user->hasAccess($nodeType, 'alpty'))
			{
			    if ($allowedNodeTypes && !in_array($nodeType, $allowedNodeTypes)) {
			        continue;
                }
				$arrOptions[] = $nodeType;
			}
		}

		return $arrOptions;
	}

    /**
     * Add an image to each page in the tree
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param string        $imageAttribute
     * @param boolean       $blnReturnImage
     * @param boolean       $blnProtected
     *
     * @return string
     */
    public function onLabelCallback($row, $label, DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
    {
        $nodeModel = TreeModel::findByPk($row['id']);

        if ($blnProtected)
        {
            $row['protected'] = true;
        }

        $image = \Controller::getPageStatusIcon((object) $row);
        $imageAttribute = trim($imageAttribute . ' data-icon="' . \Controller::getPageStatusIcon((object) array_merge($row, array('published'=>'1'))) . '" data-icon-disabled="' . \Controller::getPageStatusIcon((object) array_merge($row, array('published'=>''))) . '"');

        // Return the image only
        if ($blnReturnImage)
        {
            return \Image::getHtml($image, '', $imageAttribute);
        }

        if ($nodeModel->isRootNode()) {
            $label = '<span><strong>' . $row['internalTitle'] . '</strong> <br /> <span style="display: inline-block; margin-left: 20px; margin-top: 5px;">'.$label.'</span></span>';
        }

        return $label;

        // Add the breadcrumb link
//        $label = '<a href="' . \Backend::addToUrl('pn=' . $row['id']) . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']) . '">' . $label . '</a>';

        // Return the image
        return
//            '<a href="contao/main.php?do=feRedirect&amp;page=' . $row['id'] . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['view']) . '"' . (($dc->table != 'tl_page') ? ' class="tl_gray"' : '') . ' target="_blank">' .
            \Image::getHtml($image, '', $imageAttribute) .
//            '</a> ' .
            $label
            ;
    }

    /**
	 * Check permissions to edit table tl_page
	 *
	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
	 */
	public function checkPermission(): void
	{
	    $user = BackendUser::getInstance();
		if ($user->isAdmin)
		{
			return;
		}

        // entferne den hinzufügen button
        if (!$user->hasAccess('create', 'huh_treep')) {

            $GLOBALS['TL_DCA']['tl_tree']['config']['closed'] = true;
        }

        // Restrict the page tree
        if (empty($user->rootNodeMounts) || !is_array($user->rootNodeMounts))
        {
            $root = array(0);
        }
        else
        {
            $root = $user->rootNodeMounts;
        }

        $GLOBALS['TL_DCA']['tl_tree']['list']['sorting']['root'] = $root;

        switch (Input::get('act')) {
            case 'create':
            case 'select':
                break;
            case 'edit':


        }

        return;

		$session = $this->session->all();



		$GLOBALS['TL_DCA']['tl_tree']['list']['sorting']['root'] = $root;

//		// Set allowed page IDs (edit multiple)
//		if (is_array($session['CURRENT']['IDS']))
//		{
//			$edit_all = array();
//			$delete_all = array();
//
//			foreach ($session['CURRENT']['IDS'] as $id)
//			{
//				$objPage = $this->Database->prepare("SELECT id, pid, type, includeChmod, chmod, cuser, cgroup FROM tl_page WHERE id=?")
//										  ->limit(1)
//										  ->execute($id);
//
//				if ($objPage->numRows < 1 || !$this->User->hasAccess($objPage->type, 'alpty'))
//				{
//					continue;
//				}
//
//				$row = $objPage->row();
//
//				if ($this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $row))
//				{
//					$edit_all[] = $id;
//				}
//
//				// Mounted pages cannot be deleted
//				if ($this->User->isAllowed(BackendUser::CAN_DELETE_PAGE, $row) && !$this->User->hasAccess($id, 'pagemounts'))
//				{
//					$delete_all[] = $id;
//				}
//			}
//
//			$session['CURRENT']['IDS'] = (Input::get('act') == 'deleteAll') ? $delete_all : $edit_all;
//		}
//
//		// Set allowed clipboard IDs
//		if (isset($session['CLIPBOARD']['tl_page']) && is_array($session['CLIPBOARD']['tl_page']['id']))
//		{
//			$clipboard = array();
//
//			foreach ($session['CLIPBOARD']['tl_page']['id'] as $id)
//			{
//				$objPage = $this->Database->prepare("SELECT id, pid, type, includeChmod, chmod, cuser, cgroup FROM tl_page WHERE id=?")
//										  ->limit(1)
//										  ->execute($id);
//
//				if ($objPage->numRows < 1 || !$this->User->hasAccess($objPage->type, 'alpty'))
//				{
//					continue;
//				}
//
//				if ($this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $objPage->row()))
//				{
//					$clipboard[] = $id;
//				}
//			}
//
//			$session['CLIPBOARD']['tl_page']['id'] = $clipboard;
//		}

		// Overwrite session
//		$objSession->replace($session);

		// Check permissions to save and create new
		if (Input::get('act') == 'edit')
		{
			$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=(SELECT pid FROM tl_page WHERE id=?)")
									  ->limit(1)
									  ->execute(Input::get('id'));

			if ($objPage->numRows && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $objPage->row()))
			{
				$GLOBALS['TL_DCA']['tl_page']['config']['closed'] = true;
			}
		}

		// Check current action
		if (Input::get('act') && Input::get('act') != 'paste')
		{
			$permission = 0;
			$cid = CURRENT_ID ?: Input::get('id');
			$ids = ($cid != '') ? array($cid) : array();

			// Set permission
			switch (Input::get('act'))
			{
				case 'edit':
				case 'toggle':
					$permission = BackendUser::CAN_EDIT_PAGE;
					break;

				case 'move':
					$permission = BackendUser::CAN_EDIT_PAGE_HIERARCHY;
					$ids[] = Input::get('sid');
					break;

				case 'create':
				case 'copy':
				case 'copyAll':
				case 'cut':
				case 'cutAll':
					$permission = BackendUser::CAN_EDIT_PAGE_HIERARCHY;

					// Check the parent page in "paste into" mode
					if (Input::get('mode') == 2)
					{
						$ids[] = Input::get('pid');
					}
					// Check the parent's parent page in "paste after" mode
					else
					{
						$objPage = $this->Database->prepare("SELECT pid FROM tl_page WHERE id=?")
												  ->limit(1)
												  ->execute(Input::get('pid'));

						$ids[] = $objPage->pid;
					}
					break;

				case 'delete':
					$permission = BackendUser::CAN_DELETE_PAGE;
					break;
			}

			// Check user permissions
			$pagemounts = array();

			// Get all allowed pages for the current user
			foreach ($this->User->pagemounts as $root)
			{
				if (Input::get('act') != 'delete')
				{
					$pagemounts[] = $root;
				}

				$pagemounts = array_merge($pagemounts, $this->Database->getChildRecords($root, 'tl_page'));
			}

			$error = false;
			$pagemounts = array_unique($pagemounts);

			// Do not allow to paste after pages on the root level (pagemounts)
			if ((Input::get('act') == 'cut' || Input::get('act') == 'cutAll') && Input::get('mode') == 1 && in_array(Input::get('pid'), $this->eliminateNestedPages($this->User->pagemounts)))
			{
				throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to paste page ID ' . Input::get('id') . ' after mounted page ID ' . Input::get('pid') . ' (root level).');
			}

			// Check each page
			foreach ($ids as $i=>$id)
			{
				if (!in_array($id, $pagemounts))
				{
					$this->log('Page ID ' . $id . ' was not mounted', __METHOD__, TL_ERROR);

					$error = true;
					break;
				}

				// Get the page object
				$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
										  ->limit(1)
										  ->execute($id);

				if ($objPage->numRows < 1)
				{
					continue;
				}

				// Check whether the current user is allowed to access the current page
				if (Input::get('act') != 'show' && !$this->User->isAllowed($permission, $objPage->row()))
				{
					$error = true;
					break;
				}

				// Check the type of the first page (not the following parent pages)
				// In "edit multiple" mode, $ids contains only the parent ID, therefore check $id != $_GET['pid'] (see #5620)
				if ($i == 0 && $id != Input::get('pid') && Input::get('act') != 'create' && !$this->User->hasAccess($objPage->type, 'alpty'))
				{
					$this->log('Not enough permissions to  ' . Input::get('act') . ' ' . $objPage->type . ' pages', __METHOD__, TL_ERROR);

					$error = true;
					break;
				}
			}

			// Redirect if there is an error
			if ($error)
			{
				throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' page ID ' . $cid . ' or paste after/into page ID ' . Input::get('pid') . '.');
			}
		}
	}
}