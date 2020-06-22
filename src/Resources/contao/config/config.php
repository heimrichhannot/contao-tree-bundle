<?php
$GLOBALS['BE_MOD']['content']['tree'] = [
    'tables' => ['tl_tree'],
];

$GLOBALS['TL_MODELS']['tl_tree'] = \HeimrichHannot\TreeBundle\Model\TreeModel::class;

$GLOBALS['TL_HOOKS']['loadDataContainer']['huh_tree'] = [\HeimrichHannot\TreeBundle\EventListener\LoadDataContainerListener::class, '__invoke'];