<?php

namespace Oro\Bundle\ConfigBundle\Api\Processor\GetList;

use Oro\Bundle\ApiBundle\Config\Extra\FilterFieldsConfigExtra;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Checks whether no any "fields" filter is not specified and if so,
 * adds a configuration extra determines that only "id" field
 * should be returned for the primary entity.
 */
class HandleDefaultValueForFieldsFilter implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if (!$context->hasConfigExtra(FilterFieldsConfigExtra::NAME)) {
            $context->addConfigExtra(
                new FilterFieldsConfigExtra([$context->getClassName() => ['id']])
            );
        }
    }
}
