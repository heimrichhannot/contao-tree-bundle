<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\OutputType;

use HeimrichHannot\TreeBundle\Event\BeforeRenderNodeEvent;

abstract class AbstractOutputType
{
    /**
     * Return a unique output type alias. Must be lowercase without specialchars or spaces.
     * Will be used in database and as file name suffix.
     */
    abstract public static function getType(): string;

    public function onBeforeRenderEvent(BeforeRenderNodeEvent $event): void
    {
    }
}
