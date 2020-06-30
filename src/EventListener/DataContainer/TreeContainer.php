<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Exception;
use HeimrichHannot\TreeBundle\Collection\NodeTypeCollection;
use HeimrichHannot\TreeBundle\Contao\Backend;
use HeimrichHannot\TreeBundle\Event\ModifiyNodeLabelEvent;
use HeimrichHannot\TreeBundle\Model\TreeModel;
use HeimrichHannot\TreeBundle\TreeNode\AbstractTreeNode;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TreeContainer
{
    const PREPEND_PALETTE = '{type_legend},title,alias,type;';
    const APPEND_PALETTE = '{publish_legend},published,start,stop;';

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
        $this->connection = $connection;
        $this->nodeTypeCollection = $collection;
        $this->session = $session;
    }

    public function onLoadCallback(DataContainer $dc)
    {
        $this->checkPermission();
        $node = TreeModel::findByPk($dc->id);

        if ($node && 0 == $node->pid) {
            $palettes = &$GLOBALS['TL_DCA']['tl_tree']['palettes'];

            if (!isset($palettes[$node->type])) {
                $node->type = 'default';
            }
            $palettes[$node->type] = '{tree_legend},internalTitle;'.$palettes[$node->type];
        }
        $this->setRootType($dc);
    }

    /**
     * @param $varValue
     *
     * @throws Exception
     */
    public function onTypeSaveCallback($varValue, DataContainer $dc): string
    {
        if (0 == $dc->activeRecord->pid) {
            if (!\in_array($varValue, $this->nodeTypeCollection->getRootNodeTypes())) {
                throw new Exception($GLOBALS['TL_LANG']['ERR']['huh_tree_topLevelNode']);
            }
        } else {
            $parentNodeModel = TreeModel::findByPk($dc->pid);

            if ($parentNodeModel && ($parentNodeType = $this->nodeTypeCollection->getNodeType($parentNodeModel->type))) {
                $allowedNodeTypes = $parentNodeType->getAllowedChildren();

                if (!\in_array($varValue, $allowedNodeTypes)) {
                    throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['huh_tree_nodeTypeNotAllowed'], $varValue));
                }
            }
        }

        return $varValue;
    }

    /**
     * Make new top-level nodes root nodes.
     */
    public function setRootType(DataContainer $dc)
    {
        if ('create' != Input::get('act')) {
            return;
        }
        $rootNodeTypes = $this->nodeTypeCollection->getRootNodeTypes();
        $defaultRootPageType = reset($rootNodeTypes);

        // Insert into
        if (0 == Input::get('pid')) {
            $GLOBALS['TL_DCA']['tl_tree']['fields']['type']['default'] = $defaultRootPageType;
        } elseif (1 == Input::get('mode')) {
            $stmt = $this->connection->prepare('SELECT * FROM '.$dc->table.' WHERE id=? LIMIT 1');
            $stmt->execute([Input::get('pid')]);

            $node = $stmt->fetch();

            if (0 == $node['pid']) {
                $GLOBALS['TL_DCA']['tl_tree']['fields']['type']['default'] = $defaultRootPageType;
            }
        }
    }

    /**
     * Auto-generate a page alias if it has not been set yet.
     *
     * @param mixed $varValue
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate an alias if there is none
        if ('' == $varValue) {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->title);
        }

        $stmt = $this->connection->prepare('SELECT id FROM tl_tree WHERE id=? OR alias=?');
        $stmt->execute([$dc->id, $varValue]);

        // Check whether the page alias exists
        if ($stmt->rowCount() > ($autoAlias ? 0 : 1)) {
            if (!$autoAlias) {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-'.$dc->id;
        }

        return $varValue;
    }

    /**
     * Returns all allowed node types as array.
     *
     * @return array
     */
    public function onTypeOptionsCallback(DataContainer $dc)
    {
        $user = BackendUser::getInstance();
        $arrOptions = [];
        $allowedNodeTypes = null;

        if ($dc->activeRecord && 0 == $dc->activeRecord->pid) {
            $nodeTypes = $this->nodeTypeCollection->getRootNodeTypes();
        } else {
            $nodeTypes = $this->nodeTypeCollection->getBranchNodeTypes();
            $parentNodeModel = TreeModel::findByPk($dc->activeRecord->pid);

            if ($parentNodeModel && ($parentNodeType = $this->nodeTypeCollection->getNodeType($parentNodeModel->type))) {
                $allowedNodeTypes = $parentNodeType->getAllowedChildren();
            }
        }

        foreach ($nodeTypes as $nodeType) {
            if ($nodeType != $dc->value && $allowedNodeTypes && !\in_array($nodeType, $allowedNodeTypes)) {
                continue;
            }

            $arrOptions[] = $nodeType;
        }

        return $arrOptions;
    }

    /**
     * Add an image to each page in the tree.
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param string        $imageAttribute
     * @param bool          $blnReturnImage
     * @param bool          $blnProtected
     *
     * @return string
     */
    public function onLabelCallback($row, $label, DataContainer $dc = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
    {
        $nodeModel = TreeModel::findByPk($row['id']);
        $nodeType = $this->nodeTypeCollection->getNodeType($row['type']);

        if (!$nodeType) {
            return $label;
        }

        if ($blnProtected) {
            $row['protected'] = true;
        }

        $time = \Date::floorToMinute();

        $published = (('' == $row['start'] || $row['start'] <= $time) && ('' == $row['stop'] || $row['stop'] > ($time + 60)) && '1' == $row['published']);

        $stmt = $this->connection->prepare("SELECT id FROM tl_tree WHERE pid=?");
        $stmt->execute([$row['id']]);
        $hasChilds = ($stmt->rowCount() > 0);


        $image = $nodeType->getIcon($published ? AbstractTreeNode::ICON_STATE_PUBLISHED : AbstractTreeNode::ICON_STATE_UNPUBLISHED);

        $imageAttribute = trim($imageAttribute.' data-icon="'.$nodeType->getIcon(AbstractTreeNode::ICON_STATE_PUBLISHED).'" data-icon-disabled="'.$nodeType->getIcon(AbstractTreeNode::ICON_STATE_UNPUBLISHED).'"');

        // Return the image only
        if ($blnReturnImage) {
            return Image::getHtml($image, '', $imageAttribute);
        }

        if ($nodeModel->isRootNode()) {
            if ($hasChilds) {
                $margin = '40';
            } else {
                $margin = '20';
            }
            $label = '<span><strong>'.$row['internalTitle'].'</strong> <br /> <span style="display: inline-block; margin-left: '.$margin.'px; margin-top: 5px;">'.$label.'</span></span>';
        }

        $nodeTypeLabel = isset($GLOBALS['TL_LANG']['tl_tree']['TYPES'][$row['type']])
            ? $GLOBALS['TL_LANG']['tl_tree']['TYPES'][$row['type']]
            : $row['type'];

        $label = '<a href="'.Backend::addToUrl('do=feRedirect').'" onclick="return false;">'.Image::getHtml($image, '', $imageAttribute).'</a> <a href="" onclick="return false;">'.$label.'</a> <span style="color:#999;padding-left:3px">['.$nodeTypeLabel.']</span>';

        $label = $nodeType->onLabelCallback(new ModifiyNodeLabelEvent($label, $row, $image, $imageAttribute, $dc, $hasChilds));

        return $label;
    }

    /**
     * Check permissions to edit table tl_page.
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(): void
    {
        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // entferne den hinzufügen button
        if (!$user->hasAccess('create', 'huh_treep')) {
            $GLOBALS['TL_DCA']['tl_tree']['config']['closed'] = true;
        }
        // entferne den hinzufügen button
        if ($user->hasAccess('createRoot', 'huh_treep')) {
            $GLOBALS['TL_DCA']['tl_tree']['list']['sorting']['rootPaste'] = true;
        }

        // Restrict the page tree
        if (empty($user->rootNodeMounts) || !\is_array($user->rootNodeMounts)) {
            $root = [0];
        } else {
            $root = $user->rootNodeMounts;
        }

        $GLOBALS['TL_DCA']['tl_tree']['list']['sorting']['root'] = $root;

        $session = $this->session->all();

        // Set allowed page IDs (edit multiple)
        if (\is_array($session['CURRENT']['IDS'])) {
            $edit_all = [];
            $delete_all = [];

            $stmt = $this->connection->prepare('SELECT id, pid, type FROM tl_tree WHERE id=? LIMIT 1');

            foreach ($session['CURRENT']['IDS'] as $id) {
                $stmt->execute([$id]);
                $nodeData = $stmt->fetch(FetchMode::STANDARD_OBJECT);

//                $objPage = $this->Database->prepare("SELECT id, pid, type, includeChmod, chmod, cuser, cgroup FROM tl_page WHERE id=?")
//                    ->limit(1)
//                    ->execute($id);

//                if ($objPage->numRows < 1 || !$user->hasAccess($objPage->type, 'alpty'))
                if (!$nodeData) {
                    continue;
                }

                if ($this->hasAccess((int) $nodeData->id, $user, $root)) {
                    $edit_all[] = $id;
                }

                if (0 === $nodeData->pid) {
                    if ($user->hasAccess('deleteRoot', 'huh_treep')) {
                        $delete_all[] = $id;
                    }
                } else {
                    if ($user->hasAccess('delete', 'huh_treep')) {
                        $delete_all[] = $id;
                    }
                }
            }

            $session['CURRENT']['IDS'] = ('deleteAll' == Input::get('act')) ? $delete_all : $edit_all;
        }

        // Set allowed clipboard IDs
        if (isset($session['CLIPBOARD']['tl_tree']) && \is_array($session['CLIPBOARD']['tl_tree']['id'])) {
            $clipboard = [];

            $stmt = $this->connection->prepare('SELECT id, pid, type FROM tl_tree WHERE id=? LIMIT 1');

            foreach ($session['CLIPBOARD']['tl_tree']['id'] as $id) {
                $stmt->execute([$id]);
                $nodeData = $stmt->fetch(FetchMode::STANDARD_OBJECT);

//                $objPage = $this->Database->prepare("SELECT id, pid, type, includeChmod, chmod, cuser, cgroup FROM tl_page WHERE id=?")
//                    ->limit(1)
//                    ->execute($id);

                if (!$nodeData) {
                    continue;
                }

//                if ($objPage->numRows < 1 || !$this->User->hasAccess($objPage->type, 'alpty'))
//                {
//                    continue;
//                }

                if ($this->hasAccess((int) $nodeData->id, $user, $root)) {
                    $clipboard[] = $id;
                }
            }

            $session['CLIPBOARD']['tl_tree']['id'] = $clipboard;
        }

        // Overwrite session
        $this->session->replace($session);

        // Check permissions to save and create new
//        if (Input::get('act') == 'edit')
//        {
//            $objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=(SELECT pid FROM tl_page WHERE id=?)")
//                ->limit(1)
//                ->execute(Input::get('id'));
//
//            if ($objPage->numRows && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $objPage->row()))
//            {
//                $GLOBALS['TL_DCA']['tl_page']['config']['closed'] = true;
//            }
//        }

        // Check current action
        if (Input::get('act') && 'paste' != Input::get('act')) {
            $permission = 0;
            $cid = CURRENT_ID ?: Input::get('id');
            $ids = ('' != $cid) ? [$cid] : [];

            // Set permission
            switch (Input::get('act')) {
                case 'edit':
                case 'toggle':
//                    $permission = BackendUser::CAN_EDIT_PAGE;
                    break;

                case 'move':
//                    $permission = BackendUser::CAN_EDIT_PAGE_HIERARCHY;
                    $ids[] = Input::get('sid');

                    break;

                case 'create':
                case 'copy':
                case 'copyAll':
                case 'cut':
                case 'cutAll':
                    $permission = BackendUser::CAN_EDIT_PAGE_HIERARCHY;

                    // Check the parent page in "paste into" mode
                    if (2 == Input::get('mode')) {
                        $ids[] = Input::get('pid');
                    }
                    // Check the parent's parent page in "paste after" mode
                    else {
                        $stmt = $this->connection->prepare('SELECT pid FROM tl_tree WHERE id=?');
                        $stmt->execute([Input::get('pid')]);
                        $node = $stmt->fetch(FetchMode::STANDARD_OBJECT);
                        $ids[] = $node->pid;
                    }

                    break;

                case 'delete':
                    $permission = BackendUser::CAN_DELETE_PAGE;

                    break;
            }

            // Check user permissions
            $nodeMounts = [];

            // Get all allowed pages for the current user
            foreach ($user->rootNodeMounts as $rootMount) {
                if ('delete' != Input::get('act')) {
                    $nodeMounts[] = $rootMount;
                }

                $nodeMounts = array_merge($nodeMounts, Database::getInstance()->getChildRecords($rootMount, 'tl_tree'));
            }

            $error = false;
            $nodeMounts = array_unique($nodeMounts);

            $contaoBackend = new Backend();

            // Do not allow to paste after pages on the root level (pagemounts)
            if (
                ('cut' == Input::get('act') || 'cutAll' == Input::get('act'))
                && 1 == Input::get('mode')
                && \in_array(Input::get('pid'), $contaoBackend->eliminateNestedPages($user->rootNodeMounts, 'tl_tree'))) {
                throw new AccessDeniedException('Not enough permissions to paste tree element ID '.Input::get('id').' after mounted tree element ID '.Input::get('pid').' (root level).');
            }

            $stmt = $this->connection->prepare('SELECT * FROM tl_tree WHERE id=? LIMIT 1');
            // Check each tree node element
            foreach ($ids as $i => $id) {
                if (!\in_array($id, $nodeMounts)) {
//                    $this->log('Page ID ' . $id . ' was not mounted', __METHOD__, TL_ERROR);

                    $error = true;

                    break;
                }

                // Get the page object
                $stmt->execute([$id]);
//                $objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
//                    ->limit(1)
//                    ->execute($id);
                $nodeData = $stmt->fetch(FetchMode::STANDARD_OBJECT);

                if (!$nodeData) {
                    continue;
                }

                // Check whether the current user is allowed to access the current page
//                if (Input::get('act') != 'show' && !$this->User->isAllowed($permission, $nodeData->row()))
                if ('show' != Input::get('act') && !$this->hasAccess($nodeData->id, $user, $root)) {
                    $error = true;

                    break;
                }

                // Check the type of the first page (not the following parent pages)
                // In "edit multiple" mode, $ids contains only the parent ID, therefore check $id != $_GET['pid'] (see #5620)
//                if ($i == 0 && $id != Input::get('pid') && Input::get('act') != 'create' && !$user->hasAccess($nodeData->type, 'alpty'))
                if (0 == $i && $id != Input::get('pid') && 'create' != Input::get('act') && !$this->hasAccess($nodeData->id, $user, $root)) {
//                    $this->log('Not enough permissions to  ' . Input::get('act') . ' ' . $nodeData->type . ' pages', __METHOD__, TL_ERROR);

                    $error = true;

                    break;
                }
            }

            // Redirect if there is an error
            if ($error) {
                throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' tree node element with ID '.$cid.' or paste after/into tree node element with ID '.Input::get('pid').'.');
            }
        }
    }

    /**
     * Return if user has access to the current node.
     *
     * @param TreeModel|int $currentNode
     * @param BackendUser   $user
     * @param array         $root
     */
    public function hasAccess($currentNode, $user = null, ?array $root = null): bool
    {
        if (!$user) {
            $user = BackendUser::getInstance();
        }

        if ($user->isAdmin) {
            return true;
        }

        if (is_numeric($currentNode)) {
            $currentNode = TreeModel::findByPk($currentNode);
        }

        if (!$currentNode instanceof TreeModel) {
            return false;
        }
        $rootNode = $currentNode;

        if (!$currentNode->isRootNode()) {
            $rootNode = $currentNode->getRootNode();
        }

        if (!$root) {
            // Restrict the page tree
            if (empty($user->rootNodeMounts) || !\is_array($user->rootNodeMounts)) {
                $root = [0];
            } else {
                $root = $user->rootNodeMounts;
            }
        }

        if (\in_array($rootNode->id, $root)) {
            return true;
        }

        return false;
    }

    /**
     * Return the paste tree element button.
     *
     * @param array  $row
     * @param string $table
     * @param bool   $cr
     * @param array  $arrClipboard
     *
     * @return string
     */
    public function onPasteButtonCallback(DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        $user = BackendUser::getInstance();

        // Disable all buttons if there is a circular reference
        if (false !== $arrClipboard && (('cut' == $arrClipboard['mode'] && (1 == $cr || $arrClipboard['id'] == $row['id'])) || ('cutAll' == $arrClipboard['mode'] && (1 == $cr || \in_array($row['id'], $arrClipboard['id']))))) {
            $disablePA = true;
            $disablePI = true;
        }

        // Prevent adding non-root pages on top-level
        if ('create' != Input::get('mode') && 0 == $row['pid']) {
            $stmt = $this->connection->prepare('SELECT * FROM '.$table.' WHERE id=? LIMIT 1');
            $stmt->execute([Input::get('id')]);
            $objPage = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            if (!$objPage || !$this->nodeTypeCollection->isRootType($objPage->type)) {
                $disablePA = true;

                if (0 == $row['id']) {
                    $disablePI = true;
                }
            }
        }

        // Check permissions if the user is not an administrator
        if (!$user->isAdmin) {
            // Disable "paste into" button if there is no permission 2 (move) or 1 (create) for the current page
            if (!$disablePI) {
                //				if (!$user->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row) || (Input::get('mode') == 'create' && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $row)))
                if (!$this->hasAccess($row['id'], $user)) {
                    $disablePI = true;
                }
            }

            $stmt = $this->connection->prepare('SELECT * FROM '.$table.' WHERE id=? LIMIT 1');
            $stmt->execute([$row['pid']]);
            $objPage = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            // Disable "paste after" button if there is no permission 2 (move) or 1 (create) for the parent page
            if (!$disablePA && $objPage) {
                //				if (!$user->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $objPage->row()) || (Input::get('mode') == 'create' && !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row())))
                if (!$this->hasAccess($objPage->id, $user)) {
                    $disablePA = true;
                }
            }

            // Disable "paste after" button if the parent page is a root page and the user is not an administrator
            //			if (!$disablePA && ($row['pid'] < 1 || in_array($row['id'], $dc->rootIds)))
            if (!$disablePA && $row['pid'] < 1 && !$user->hasAccess('createRoot', 'huh_treep')) {
                $disablePA = true;
            }
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.svg').' ' : '<a href="'.Backend::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
        }

        return $return.($disablePI ? Image::getHtml('pasteinto_.svg').' ' : '<a href="'.Backend::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!\is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
    }

    /**
     * Return the copy page button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     * @param string $table
     *
     * @return string
     */
    public function onCopyButtonCallback($row, $href, $label, $title, $icon, $attributes, $table)
    {
        if ($GLOBALS['TL_DCA'][$table]['config']['closed']) {
            return '';
        }

        $user = BackendUser::getInstance();

        //		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        return ($this->hasAccess($row['id'], $user)) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the copy page with subpages button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     * @param string $table
     *
     * @return string
     */
    public function onCopyChildsButtonCallback($row, $href, $label, $title, $icon, $attributes, $table)
    {
        if ($GLOBALS['TL_DCA'][$table]['config']['closed']) {
            return '';
        }

        $stmt = $this->connection->prepare('SELECT * FROM tl_tree WHERE pid=? LIMIT 1');
        $stmt->execute([$row['id']]);
        $childNodes = $stmt->fetch(FetchMode::STANDARD_OBJECT);

        //		return ($childNodes->numRows && $this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        return ($childNodes && $this->hasAccess($row['id'])) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the cut page button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onCutButtonCallback($row, $href, $label, $title, $icon, $attributes)
    {
        //		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE_HIERARCHY, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        return ($this->hasAccess($row['id'])) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : ' ';
    }

    /**
     * Return the edit tree node button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onEditButtonCallback($row, $href, $label, $title, $icon, $attributes)
    {
        //		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $row)) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        return ($this->hasAccess($row['id'])) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete tree node button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onDeleteButtonCallback($row, $href, $label, $title, $icon, $attributes)
    {
        //		$root = func_get_arg(7);

        $user = BackendUser::getInstance();

        //		return ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(BackendUser::CAN_DELETE_PAGE, $row) && ($this->User->isAdmin || !in_array($row['id'], $root))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        return ((0 == $row['pid'] && $user->hasAccess('deleteRoot', 'huh_treep')) || (0 != $row['pid'] && $user->hasAccess('delete', 'huh_treep'))) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the "toggle visibility" button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     */
    public function onToggleButtonCallback($row, $href, $label, $title, $icon, $attributes): string
    {
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (1 == Input::get('state')), (@func_get_arg(12) ?: null));
            Backend::redirect(Backend::getReferer());
        }

        $user = BackendUser::getInstance();

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_tree::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        //		$stmt = $this->connection->prepare("SELECT * FROM tl_page WHERE id=? LIMIT 1");
        //		$stmt->execute([$row['id']]);
        //		$objPage = $stmt->fetch(FetchMode::ASSOCIATIVE);
//
        //		if (!$this->User->hasAccess($row['type'], 'alpty') || !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row()))
        if (!$this->hasAccess($row['id'], $user)) {
            return Image::getHtml($icon).' ';
        }

        return '<a href="'.Backend::addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    /**
     * Disable/enable a user group.
     *
     * @param int           $intId
     * @param bool          $blnVisible
     * @param DataContainer $dc
     *
     * @throws AccessDeniedException
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_tree']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_tree']['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $user = BackendUser::getInstance();

        // Check the field access
        if (!$user->hasAccess('tl_tree::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish tree element ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $stmt = $this->connection->prepare('SELECT * FROM tl_tree WHERE id=? LIMIT 1');
            $stmt->execute([$intId]);
            $objRow = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            if ($objRow) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_tree', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_tree']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_tree']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $stmt = $this->connection->prepare("UPDATE tl_tree SET tstamp=$time, published='".($blnVisible ? '1' : '')."' WHERE id=?");
        $stmt->execute([$intId]);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_tree']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_tree']['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
