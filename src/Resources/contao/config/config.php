<?php
/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['tree'] = [
    'tables' => ['tl_tree'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_tree'] = \HeimrichHannot\TreeBundle\Model\TreeModel::class;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh_tree'] = [\HeimrichHannot\TreeBundle\EventListener\LoadDataContainerListener::class, '__invoke'];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'rootNodeMounts';
$GLOBALS['TL_PERMISSIONS'][] = 'huh_treep';