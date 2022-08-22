<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\EventListener\DataContainer;

use Contao\Date;
use Doctrine\DBAL\Connection;

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
        $time = Date::floorToMinute();
        $stmt = $this->connection->prepare(
            "SELECT id, internalTitle FROM tl_tree WHERE pid=0 AND (start='' OR start<=?) AND (stop='' OR stop>?) AND published='1'"
        );
        $result = $stmt->executeQuery([$time, $time + 60]);

        while ($child = $result->fetchAssociative()) {
            if (!empty($child['internalTitle'])) {
                $rootNodes[$child['id']] = $child['internalTitle'];
            }
        }

        return $rootNodes;
    }
}
