<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use OroCRM\Bundle\ReportBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ReportController extends Controller
{
    /**
     * @Route(
     *      "/static/{reportGroupName}/{reportName}/{_format}",
     *      name="orocrm_report_static_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("OroCRMReportBundle:Report:static.html.twig")
     * @AclAncestor("orocrm_report_view")
     */
    public function indexStaticAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['orocrm_report', $reportGroupName, $reportName]);
        $pageTitle = $this->get('oro_datagrid.datagrid.manager')->getConfigurationForGrid($gridName)['pageTitle'];

        $this->get('oro_navigation.title_service')->setParams(array('%reportName%' => $pageTitle));

        return [
            'pageTitle' => $pageTitle,
            'gridName'  => $gridName,
            'params'    => [
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            ]
        ];
    }

    /**
     * @Route("/view/{id}", name="orocrm_report_view", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Acl(
     *      id="orocrm_report_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function viewAction(Report $entity)
    {
        return $this->render(
            sprintf('OroCRMReportBundle:Report:%s/view.html.twig', strtolower($entity->getType()->getName())),
            array(
                'entity' => $entity
            )
        );
    }

    /**
     * @Route("/create", name="orocrm_report_create")
     * @Template("OroCRMReportBundle:Report:update.html.twig")
     * @Acl(
     *      id="orocrm_report_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function createAction()
    {
        return $this->update(new Report());
    }

    /**
     * @Route("/update/{id}", name="orocrm_report_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_report_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function updateAction(Report $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_report_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     *
     * @Template
     * @AclAncestor("orocrm_report_view")
     */
    public function indexAction()
    {
        return array();
    }

    protected function update(Report $entity)
    {
        if ($this->get('orocrm_report.form.handler.report')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.report.controller.report.saved')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_report_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_report_index',
                    // @todo: WILL BE IMPLEMENTER LATER
                    //'route'      => 'orocrm_report_view',
                    'parameters' => array()
                )
            );
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_report.form.report')->createView(),
            'entities' => $this->get('orocrm_report.entity_provider')->getEntities(),
            'metadata' => $this->get('oro_querydesigner.querydesigner.manager')->getMetadata()
        );
    }
}
