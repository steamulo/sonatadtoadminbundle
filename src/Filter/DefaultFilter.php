<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;

class DefaultFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        if (!$queryBuilder instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        $criteriaValue = $value['value'];
        if (!empty($alias)) {
            $field = sprintf('%s.%s', $alias, $field);
        }

        $queryBuilder->addCriteria(new Criteria(Criteria::expr()->eq($field, $criteriaValue)));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'field_type' => TextType::class,
            'operator_type' => HiddenType::class,
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
