<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;

class ListBuilder implements ListBuilderInterface
{
    private $templates = [];

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ($fieldDescription->getName() === '_action' || $fieldDescription->getType() === 'actions') {
            $this->buildActionFieldDescription($fieldDescription);
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf(
                'Please define a type for field `%s` in `%s`',
                $fieldDescription->getName(),
                get_class($admin)
            ));
        }

        $fieldMapping = [
            'id' => false,
            'fieldName' => $fieldDescription->getFieldName(),
        ];

        if (in_array($fieldDescription->getName(), $admin->getModelManager()->getIdentifierFieldNames($admin->getClass()))) {
            $fieldMapping['id'] = true;
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setFieldMapping($fieldMapping);
        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (false !== $fieldDescription->getOption('sortable')) {
            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
            $fieldDescription->setOption('sort_parent_association_mappings', $fieldDescription->getOption('sort_parent_association_mappings', $fieldDescription->getParentAssociationMappings()));
            $fieldDescription->setOption('sort_field_mapping', $fieldDescription->getOption('sort_field_mapping', $fieldDescription->getFieldMapping()));
        }

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));

            if (!$fieldDescription->getTemplate()) {
                throw new \RuntimeException(sprintf('Unable to find template for type: %s', $fieldDescription->getType()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseList(array $options = [])
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type === null) {
            $type = 'text';
        }

        $fieldDescription->setType($type);

        $this->fixFieldDescription($admin, $fieldDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        $this->buildField($type, $fieldDescription, $admin);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('actions');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null === $fieldDescription->getOption('code')) {
            $fieldDescription->setOption('code', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('SonataAdminBundle:CRUD:list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    private function getTemplate($type)
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }

    /**
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }
}
