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
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Exception;
use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\Model\TreeModel;

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

    public function __construct(Connection $connection, NodeTypeCollection $collection)
    {
        $this->connection         = $connection;
        $this->nodeTypeCollection = $collection;
    }

    /**
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
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
        $varValue = $this->checkRootType($varValue, $dc);
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
     * Make sure that top-level nodes are root nodes
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function checkRootType($varValue, DataContainer $dc)
    {
        if (!in_array($varValue, $this->nodeTypeCollection->getRootNodeTypes()) && $dc->activeRecord->pid == 0) {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['topLevelRoot']);
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

		if ($dc->activeRecord && $dc->activeRecord->pid == 0) {
		    $nodeTypes = $this->nodeTypeCollection->getRootNodeTypes();
        } else {
		    $nodeTypes = $this->nodeTypeCollection->getBranchNodeTypes();
        }

		foreach ($nodeTypes as $nodeType)
        {
			// Allow the currently selected option and anything the user has access to
			if ($nodeType == $dc->value || $user->hasAccess($nodeType, 'alpty'))
			{
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
        $node = $this->nodeTypeCollection->getNodeType($row['type']);

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

        // Mark root pages
        if ($row['type'] == 'root' || \Input::get('do') == 'article')
        {
            $label = '<strong>' . $label . '</strong>';
        }

        // Add the breadcrumb link
        $label = '<a href="' . \Backend::addToUrl('pn=' . $row['id']) . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']) . '">' . $label . '</a>';

        // Return the image
        return
//            '<a href="contao/main.php?do=feRedirect&amp;page=' . $row['id'] . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['view']) . '"' . (($dc->table != 'tl_page') ? ' class="tl_gray"' : '') . ' target="_blank">' .
            \Image::getHtml($image, '', $imageAttribute) .
//            '</a> ' .
            $label
            ;
    }
}