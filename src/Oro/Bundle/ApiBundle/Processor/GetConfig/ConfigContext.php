<?php

namespace Oro\Bundle\ApiBundle\Processor\GetConfig;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Config\Extra\ConfigExtraInterface;
use Oro\Bundle\ApiBundle\Config\Extra\FiltersConfigExtra;
use Oro\Bundle\ApiBundle\Config\Extra\SortersConfigExtra;
use Oro\Bundle\ApiBundle\Config\FiltersConfig;
use Oro\Bundle\ApiBundle\Config\SortersConfig;
use Oro\Bundle\ApiBundle\Processor\ApiContext;

/**
 * The execution context for processors for "get_config" action.
 * @method EntityDefinitionConfig|null getResult()
 */
class ConfigContext extends ApiContext
{
    /** FQCN of an entity */
    const CLASS_NAME = 'class';

    /** the name of the action for which the configuration is built */
    const TARGET_ACTION = 'targetAction';

    /** a flag indicates whether a configuration is requested for a list of entities or a single entity */
    const COLLECTION = 'collection';

    /** FQCN of the parent entity if a configuration is requested for a sub-resource */
    const PARENT_CLASS_NAME = 'parentClass';

    /** the association name if a configuration is requested for a sub-resource */
    const ASSOCIATION = 'association';

    /** the maximum number of related entities that can be retrieved */
    const MAX_RELATED_ENTITIES = 'maxRelatedEntities';

    /** a list of requests for configuration data that should be retrieved */
    const EXTRA = 'extra';

    /** the exclusion policy that was before CompleteDefinition processor is set it to "all" */
    private const REQUESTED_EXCLUSION_POLICY = 'requested_exclusion_policy';

    /** @var ConfigExtraInterface[] */
    protected $extras = [];

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();
        $this->set(self::EXTRA, []);
    }

    /**
     * Gets FQCN of an entity.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->get(self::CLASS_NAME);
    }

    /**
     * Sets FQCN of an entity.
     *
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->set(self::CLASS_NAME, $className);
    }

    /**
     * Gets the name of the action for which the configuration is built
     *
     * @return string|null
     */
    public function getTargetAction()
    {
        return $this->get(self::TARGET_ACTION);
    }

    /**
     * Sets the name of the action for which the configuration is built
     *
     * @param string $action
     */
    public function setTargetAction($action)
    {
        $this->set(self::TARGET_ACTION, $action);
    }

    /**
     * Indicates whether a configuration is requested for a list of entities or a single entity.
     *
     * @return bool TRUE for a list of entities resource, FALSE for a single entity resource
     */
    public function isCollection()
    {
        return (bool)$this->get(self::COLLECTION);
    }

    /**
     * Sets a flag indicates whether a configuration is requested for a list of entities or a single entity.
     *
     * @param bool $value TRUE for a list of entities resource, FALSE for a single entity resource
     */
    public function setIsCollection($value)
    {
        $this->set(self::COLLECTION, $value);
    }

    /**
     * Gets FQCN of the parent entity if a configuration is requested for a sub-resource.
     *
     * @return string
     */
    public function getParentClassName()
    {
        return $this->get(self::PARENT_CLASS_NAME);
    }

    /**
     * Sets FQCN of the parent entity if a configuration is requested for a sub-resource.
     *
     * @param string $parentClassName
     */
    public function setParentClassName($parentClassName)
    {
        $this->set(self::PARENT_CLASS_NAME, $parentClassName);
    }

    /**
     * Gets the association name if a configuration is requested for a sub-resource.
     *
     * @return string
     */
    public function getAssociationName()
    {
        return $this->get(self::ASSOCIATION);
    }

    /**
     * Sets the association name if a configuration is requested for a sub-resource.
     *
     * @param string $associationName
     */
    public function setAssociationName($associationName)
    {
        $this->set(self::ASSOCIATION, $associationName);
    }

    /**
     * Gets the maximum number of related entities that can be retrieved
     *
     * @return int|null
     */
    public function getMaxRelatedEntities()
    {
        return $this->get(self::MAX_RELATED_ENTITIES);
    }

    /**
     * Sets the maximum number of related entities that can be retrieved
     *
     * @param int|null $limit
     */
    public function setMaxRelatedEntities($limit = null)
    {
        if (null === $limit) {
            $this->remove(self::MAX_RELATED_ENTITIES);
        } else {
            $this->set(self::MAX_RELATED_ENTITIES, $limit);
        }
    }

    /**
     * Gets the exclusion policy that was before CompleteDefinition processor is set it to "all".
     *
     * @return string|null
     */
    public function getRequestedExclusionPolicy()
    {
        return $this->get(self::REQUESTED_EXCLUSION_POLICY);
    }

    /**
     * Sets the exclusion policy that was before CompleteDefinition processor is set it to "all".
     *
     * @param string|null $exclusionPolicy
     */
    public function setRequestedExclusionPolicy($exclusionPolicy)
    {
        if ($exclusionPolicy) {
            $this->set(self::REQUESTED_EXCLUSION_POLICY, $exclusionPolicy);
        } else {
            $this->remove(self::REQUESTED_EXCLUSION_POLICY);
        }
    }

    /**
     * Checks whether some configuration data is requested.
     *
     * @param string $extraName
     *
     * @return bool
     */
    public function hasExtra($extraName)
    {
        return \in_array($extraName, $this->get(self::EXTRA), true);
    }

    /**
     * Gets a request for configuration data.
     *
     * @param string $extraName
     *
     * @return ConfigExtraInterface|null
     */
    public function getExtra($extraName)
    {
        $result = null;
        foreach ($this->extras as $extra) {
            if ($extra->getName() === $extraName) {
                $result = $extra;
                break;
            }
        }

        return $result;
    }

    /**
     * Gets a list of requests for configuration data.
     *
     * @return ConfigExtraInterface[]
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Sets requests for configuration data.
     *
     * @param ConfigExtraInterface[] $extras
     *
     * @throws \InvalidArgumentException if $extras has invalid elements
     */
    public function setExtras(array $extras)
    {
        $names = [];
        foreach ($extras as $extra) {
            if (!$extra instanceof ConfigExtraInterface) {
                throw new \InvalidArgumentException(
                    'Expected an array of "Oro\Bundle\ApiBundle\Config\ConfigExtraInterface".'
                );
            }
            $names[] = $extra->getName();
            $extra->configureContext($this);
        }

        $this->extras = $extras;
        $this->set(self::EXTRA, $names);
    }

    /**
     * Removes a request for some configuration data.
     *
     * @param string $extraName
     */
    public function removeExtra(string $extraName)
    {
        $keys = array_keys($this->extras);
        foreach ($keys as $key) {
            if ($this->extras[$key]->getName() === $extraName) {
                unset($this->extras[$key]);
            }
        }
        $this->extras = array_values($this->extras);

        $names = $this->get(self::EXTRA);
        $key = array_search($extraName, $names, true);
        if (false !== $key) {
            unset($names[$key]);
            $this->set(self::EXTRA, array_values($names));
        }
    }

    /**
     * Gets a list of requests for configuration data that can be used
     * to get configuration of related entities.
     *
     * @return ConfigExtraInterface[]
     */
    public function getPropagableExtras()
    {
        $result = [];
        foreach ($this->extras as $extra) {
            if ($extra->isPropagable()) {
                $result[] = $extra;
            }
        }

        return $result;
    }

    /**
     * Checks whether a definition of filters exists.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return $this->has(FiltersConfigExtra::NAME);
    }

    /**
     * Gets a definition of filters.
     *
     * @return FiltersConfig|null
     */
    public function getFilters()
    {
        return $this->get(FiltersConfigExtra::NAME);
    }

    /**
     * Sets a definition of filters.
     *
     * @param FiltersConfig|null $filters
     */
    public function setFilters(FiltersConfig $filters = null)
    {
        $this->set(FiltersConfigExtra::NAME, $filters);
    }

    /**
     * Checks whether a definition of sorters exists.
     *
     * @return bool
     */
    public function hasSorters()
    {
        return $this->has(SortersConfigExtra::NAME);
    }

    /**
     * Gets a definition of sorters.
     *
     * @return SortersConfig|null
     */
    public function getSorters()
    {
        return $this->get(SortersConfigExtra::NAME);
    }

    /**
     * Sets a definition of sorters.
     *
     * @param SortersConfig|null $sorters
     */
    public function setSorters(SortersConfig $sorters = null)
    {
        $this->set(SortersConfigExtra::NAME, $sorters);
    }
}
