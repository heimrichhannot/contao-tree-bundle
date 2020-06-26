<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\TreeBundle\Contao;


use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Backend extends \Contao\Backend
{
    public function __construct()
    {
        parent::__construct();
    }

    public function eliminateNestedPages($arrPages, $strTable = null, $blnSorting = false)
    {
        return parent::eliminateNestedPages($arrPages, $strTable, $blnSorting);
    }

    /**
     * Add a breadcrumb menu to a tree
     *
     * @param string $strKey
     *
     * @throws AccessDeniedException
     * @throws \RuntimeException
     */
    public static function addBreadcrumb(string $table = 'tl_tree', $strKey='tl_tree_node')
    {
        /** @var AttributeBagInterface $objSession */
        $objSession = System::getContainer()->get('session')->getBag('contao_backend');

        // Set a new node
        if (isset($_GET['pn']))
        {
            // Check the path (thanks to Arnaud Buchoux)
            if (Validator::isInsecurePath(Input::get('pn', true)))
            {
                throw new \RuntimeException('Insecure path ' . Input::get('pn', true));
            }

            $objSession->set($strKey, Input::get('pn', true));
            Controller::redirect(preg_replace('/&pn=[^&]*/', '', Environment::get('request')));
        }

        $intNode = $objSession->get($strKey);

        if ($intNode < 1)
        {
            return;
        }

        // Check the path (thanks to Arnaud Buchoux)
        if (Validator::isInsecurePath($intNode))
        {
            throw new \RuntimeException('Insecure path ' . $intNode);
        }

        $arrIds   = array();
        $arrLinks = array();
        $objUser  = BackendUser::getInstance();

        // Generate breadcrumb trail
        if ($intNode)
        {
            $intId = $intNode;
            $objDatabase = Database::getInstance();

            do
            {
                $objPage = $objDatabase->prepare("SELECT * FROM $table WHERE id=?")
                    ->limit(1)
                    ->execute($intId);

                if ($objPage->numRows < 1)
                {
                    // Currently selected page does not exist
                    if ($intId == $intNode)
                    {
                        $objSession->set($strKey, 0);

                        return;
                    }

                    break;
                }

                $arrIds[] = $intId;

                // No link for the active page
                if ($objPage->id == $intNode)
                {
                    $arrLinks[] = static::addPageIcon($objPage->row(), '', null, '', true) . ' ' . $objPage->title;
                }
                else
                {
                    $arrLinks[] = static::addPageIcon($objPage->row(), '', null, '', true) . ' <a href="' . static::addToUrl('pn=' . $objPage->id) . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']) . '">' . $objPage->title . '</a>';
                }

                // Do not show the mounted pages
                if (!$objUser->isAdmin && $objUser->hasAccess($objPage->id, 'pagemounts'))
                {
                    break;
                }

                $intId = $objPage->pid;
            } while ($intId > 0 && $objPage->type != 'root');
        }

        // Check whether the node is mounted
        if (!$objUser->hasAccess($arrIds, 'pagemounts'))
        {
            $objSession->set($strKey, 0);

            throw new AccessDeniedException('Page ID ' . $intNode . ' is not mounted.');
        }

        // Limit tree
        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = array($intNode);

        // Add root link
        $arrLinks[] = Image::getHtml('pagemounts.svg') . ' <a href="' . static::addToUrl('pn=0') . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']) . '">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
        $arrLinks = array_reverse($arrLinks);

        // Insert breadcrumb menu
        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['breadcrumb'] .= '

<ul id="tl_breadcrumb">
  <li>' . implode(' › </li><li>', $arrLinks) . '</li>
</ul>';
    }


}