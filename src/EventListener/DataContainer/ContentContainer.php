<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\EventListener\DataContainer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class ContentContainer
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function onHuhTreeOptionsCallback()
    {
        $rootNodes = [];
        $time = \Date::floorToMinute();
        $stmt = $this->connection->prepare("SELECT id, internalTitle FROM tl_tree WHERE pid=0 AND (start='' OR start<=?) AND (stop='' OR stop>?) AND published='1'");
        $stmt->execute([$time, $time + 60]);

        if ($stmt->rowCount() > 0) {
            while ($child = $stmt->fetch(FetchMode::STANDARD_OBJECT)) {
                $rootNodes[$child->id] = $child->internalTitle;
            }
        }

        return $rootNodes;
    }
}
