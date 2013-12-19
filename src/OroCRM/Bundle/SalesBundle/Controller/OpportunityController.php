<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Doctrine\Common\Inflector\Inflector;

use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;
use Oro\Bundle\EntityConfigBundle\Entity\Repository\OptionSetRelationRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @Route("/opportunity")
 */
class OpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function viewAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * TODO: will be refactored via twig extension
     */
    public function infoAction(Opportunity $entity)
    {
        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager  = $this->get('oro_entity_config.config_manager');
        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $viewProvider   = $this->get('oro_entity_config.provider.view');

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($viewProvider, $extendProvider) {
                $extendConfig = $extendProvider->getConfigById($config->getId());

                return
                    $config->is('owner', ExtendManager::OWNER_CUSTOM)
                    && !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && $viewProvider->getConfigById($config->getId())->is('is_displayable')
                    && !(
                        in_array($extendConfig->getId()->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
                        && $extendProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', true)
                    );
            },
            get_class($entity)
        );

        $dynamicRow = array();
        foreach ($fields as $field) {
            $fieldName = $field->getId()->getFieldName();
            $value = $entity->{'get' . ucfirst(Inflector::camelize($fieldName))}();

            /** Prepare DateTime field type */
            if ($value instanceof \DateTime) {
                $configFormat = $this->get('oro_config.global')->get('oro_locale.date_format') ? : 'Y-m-d';
                $value        = $value->format($configFormat);
            }

            /** Prepare OptionSet field type */
            if ($field->getId()->getFieldType() == 'optionSet') {
                /** @var OptionSetRelationRepository  */
                $osr = $configManager->getEntityManager()->getRepository(OptionSetRelation::ENTITY_NAME);

                $model = $extendProvider->getConfigManager()->getConfigFieldModel(
                    $field->getId()->getClassName(),
                    $field->getId()->getFieldName()
                );

                $value = $osr->findByFieldId($model->getId(), $entity->getId());
                array_walk(
                    $value,
                    function (&$item) {
                        $item = ['title' => $item->getOption()->getLabel()];
                    }
                );

                $value['values'] = $value;
            }

            /** Prepare Relation field type */
            if ($value instanceof PersistentCollection) {
                $collection     = $value;
                $extendConfig   = $extendProvider->getConfigById($field->getId());
                $titleFieldName = $extendConfig->get('target_title');

                /** generate link for related entities collection */
                $route       = false;
                $routeParams = false;

                if (class_exists($extendConfig->get('target_entity'))) {
                    /** @var EntityMetadata $metadata */
                    $metadata = $configManager->getEntityMetadata($extendConfig->get('target_entity'));
                    if ($metadata && $metadata->routeView) {
                        $route       = $metadata->routeView;
                        $routeParams = array(
                            'id' => null
                        );
                    }

                    $relationExtendConfig = $extendProvider->getConfig($extendConfig->get('target_entity'));
                    if ($relationExtendConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
                        $route       = 'oro_entity_view';
                        $routeParams = array(
                            'entity_id' => str_replace('\\', '_', $extendConfig->get('target_entity')),
                            'id'        => null
                        );
                    }
                }

                $value = array(
                    'route'        => $route,
                    'route_params' => $routeParams,
                    'values'       => array()
                );

                foreach ($collection as $item) {
                    $routeParams['id'] = $item->getId();

                    $title = [];
                    foreach ($titleFieldName as $fieldName) {
                        $title[] = $item->{Inflector::camelize('get_' . $fieldName)}();
                    }

                    $value['values'][] = array(
                        'id'    => $item->getId(),
                        'link'  => $route ? $this->generateUrl($route, $routeParams) : false,
                        'title' => implode(' ', $title)
                    );
                }
            }

            $fieldName = $field->getId()->getFieldName();
            $dynamicRow[$entityProvider->getConfigById($field->getId())->get('label') ? : $fieldName]
                = $value; //$entity->{'get' . ucfirst(Inflector::camelize($fieldName))}();
        }

        return array(
            'dynamic' => $dynamicRow,
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_sales_opportunity_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function createAction()
    {
        $entity        = new Opportunity();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:OpportunityStatus', 'in_progress');
        $entity->setStatus($defaultStatus);

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", name="orocrm_sales_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function updateAction(Opportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @param Opportunity $entity
     * @return array
     */
    protected function update(Opportunity $entity)
    {
        if ($this->get('orocrm_sales.opportunity.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.opportunity.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_sales_opportunity_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_sales_opportunity_view',
                    'parameters' => array('id' => $entity->getId()),
                )
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.opportunity.form')->createView(),
        );
    }
}
