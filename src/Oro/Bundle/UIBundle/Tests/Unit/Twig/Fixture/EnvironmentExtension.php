<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Fixture;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * A test stub
 */
class EnvironmentExtension extends AbstractExtension implements GlobalsInterface
{
    public function getTokenParsers()
    {
        return array(
            new EnvironmentTokenParser(),
        );
    }

    public function getNodeVisitors()
    {
        return array(
            new EnvironmentNodeVisitor(),
        );
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('foo_filter', 'foo_filter'),
        );
    }

    public function getTests()
    {
        return array(
            new TwigTest('foo_test', 'foo_test'),
        );
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('foo_function', 'foo_function'),
        );
    }

    public function getOperators()
    {
        return array(
            array('foo_unary' => array()),
            array('foo_binary' => array()),
        );
    }

    public function getGlobals()
    {
        return array(
            'foo_global' => 'foo_global',
        );
    }

    public function getName()
    {
        return 'environment_test';
    }
}
