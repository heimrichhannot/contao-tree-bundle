<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\OutputType;

class Bootstrap4AccordionOutputType extends AbstractOutputType
{
    public static function getType(): string
    {
        return 'bs4accordion';
    }
}
