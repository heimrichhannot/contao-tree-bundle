<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_tree'];

$lang['TYPES']['mainroot'] = 'Baumstrukturen';
$lang['TYPES']['simple'] = 'Einfacher Knoten';
$lang['TYPES']['member'] = 'Mitglied-Knoten';
$lang['TYPES']['member_groups'] = 'Mitgliedergruppen-Knoten';

$lang['OUTPUTTYPES'][\HeimrichHannot\TreeBundle\OutputType\ListOutputType::getType()] = 'Liste';
$lang['OUTPUTTYPES'][\HeimrichHannot\TreeBundle\OutputType\Bootstrap4AccordionOutputType::getType()] = 'Bootstrap 4 Accordion';

/*
 * Buttons
 */
$lang['new'] = ['Neuer Knoten', 'Neuen Baumknoten erstellen'];
$lang['edit'] = ['Knoten bearbeiten', 'Baumknoten ID %s bearbeiten'];
$lang['editheader'] = ['Knoten-Einstellungen bearbeiten', 'Baumknoten-Einstellungen ID %s bearbeiten'];
$lang['copy'] = ['Knoten duplizieren', 'Baumknoten ID %s duplizieren'];
$lang['copyChilds'] = ['Knoten und Kindknoten duplizieren', 'Baumknoten ID %s mit Kindknoten duplizieren'];
$lang['cut'] = ['Knoten verschieben', 'Baumknoten ID %s mit Kindknoten verschieben'];
$lang['delete'] = ['Knoten löschen', 'Baumknoten ID %s löschen'];
$lang['toggle'] = ['Knoten veröffentlichen', 'Baumknoten ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Knoten Details', 'Baumknoten-Details ID %s anzeigen'];

/*
 * Fields
 */
$lang['alias'][0] = 'Alias';
$lang['alias'][1] = 'Der Alias ist eine eindeutige Referenz, welche anstelle der numerischen ID aufgerufen werden kann.';
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Bitte geben Sie den Titel des Baumknoten an.';
$lang['internalTitle'][0] = 'Baum-Titel';
$lang['internalTitle'][1] = 'Bitte geben Sie den Titel des Baumes an.';
$lang['type'][0] = 'Knoten-Typ';
$lang['type'][1] = 'Bitte wählen Sie den Typ des Baumknoten aus.';
$lang['outputType'][0] = 'Ausgabeformat';
$lang['outputType'][1] = 'Bitte wählen Sie das Ausgabeformat des Baumes.';
$lang['description'][0] = 'Beschreibung';
$lang['description'][1] = 'Hier können Sie eine Beschreibung zum Knoten angeben. Diese kann im Frontend ausgegeben werden.';
$lang['member'][0] = 'Mitglied';
$lang['member'][1] = 'Hier können Sie dem Knoten ein Mitglied zuordnen';
$lang['groups'][0] = 'Mitgliedergruppen';
$lang['groups'][1] = 'Hier können Sie dem Knoten Mitgliedergruppen zuordnen';
$lang['published'][0] = 'Knoten veröffentlichen';
$lang['published'][1] = 'Diesen Baumknoten veröffentlichen. Ist der Baumknoten versteckt, werden auch Kindknoten nicht ausgegeben.';
$lang['start'][0] = 'Anzeigen ab';
$lang['start'][1] = 'Diesen Baumknoten erst ab diesem Tag auf der Webseite anzeigen.';
$lang['stop'][0] = 'Anzeigen bis';
$lang['stop'][1] = 'Diesen Baumknoten nur bis zu diesem Tag auf der Webseite anzeigen.';

/*
 * Legends
 */
$lang['tree_legend'] = 'Baum-Einstellungen';
$lang['type_legend'] = 'Baumknoten und -typ';
$lang['content_legend'] = 'Inhalt';
$lang['publish_legend'] = 'Veröffentlichung';
