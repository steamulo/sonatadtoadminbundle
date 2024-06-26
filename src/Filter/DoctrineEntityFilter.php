<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\Form\Type\EqualType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType as SonataChoiceType;

/**
 * @author Aurélien Soulard <aurelien@vtech.fr>
 */
class DoctrineEntityFilter extends AbstractFilter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $alias
     * @param string $field
     * @param array $value
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        if (!$queryBuilder instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        if (!isset($value['value']) || !$value['value'] || empty($value['value'])) {
            return;
        }

        $criteriaValue = $value['value'];
        if ($criteriaValue instanceof Collection) {
            $criteriaValue = $criteriaValue->toArray();
        }

        $arrayComparison = Comparison::IN;
        $objectComparison = Comparison::EQ;

        /** Si le filtre avancé "Ne contient pas" est trigger, les comparaisons deviennent des négations */
        if(isset($value['type']) && $value['type'] === SonataChoiceType::TYPE_NOT_CONTAINS) {
            $arrayComparison = Comparison::NIN;
            $objectComparison = Comparison::NEQ;
        }

        $comparisonOperator = is_array($criteriaValue) ? $arrayComparison : $objectComparison;
        if (!empty($alias)) {
            $field = sprintf('%s.%s', $alias, $field);
        }

        $queryBuilder->addCriteria(new Criteria(new Comparison($field, $comparisonOperator, $criteriaValue)));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'mapping_type' => false,
            'field_name' => false,
            'field_type' => EntityType::class,
            'field_options' => [],
            'operator_type' => EqualType::class,
            'operator_options' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ]];
    }
}
