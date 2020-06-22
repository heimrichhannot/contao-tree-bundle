<?php
$GLOBALS['BE_MOD']['content']['tree'] = [
    'tables' => ['tl_tree'],
];

$GLOBALS['TL_MODELS']['tl_tree'] = \HeimrichHannot\TreeBundle\Model\TreeModel::class;
