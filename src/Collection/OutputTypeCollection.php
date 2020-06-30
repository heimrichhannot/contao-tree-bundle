<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TreeBundle\Collection;

use Contao\StringUtil;
use HeimrichHannot\TreeBundle\OutputType\AbstractOutputType;

class OutputTypeCollection
{
    protected $outputTypes = null;
    /**
     * @var iterable
     */
    private $outputTypesIterable;

    /**
     * OutputTypeCollection constructor.
     */
    public function __construct(iterable $outputTypesIterable)
    {
        $this->outputTypesIterable = $outputTypesIterable;
    }

    /**
     * Add output type.
     */
    public function addType(AbstractOutputType $outputType)
    {
        if ($outputType::getType() !== StringUtil::generateAlias($outputType::getType())) {
            throw new \Exception('Not a valid outputtype type.');
        }
        $this->outputTypes[$outputType::getType()] = $outputType;
    }

    public function getType(string $type): ?AbstractOutputType
    {
        $this->createIndex();

        if (isset($this->outputTypes[$type])) {
            return $this->outputTypes[$type];
        }

        return null;
    }

    /**
     * Return all output types.
     */
    public function getOutputTypes(): array
    {
        $this->createIndex();

        return $this->outputTypes;
    }

    protected function createIndex(): void
    {
        if (null !== $this->outputTypes) {
            return;
        }
        $this->outputTypes = [];
        /** @var AbstractOutputType $outputType */
        foreach ($this->outputTypesIterable as $outputType) {
            $this->addType($outputType);
        }
    }
}
