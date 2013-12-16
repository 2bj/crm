<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/{id}", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function indexAction(Channel $channel)
    {
        return ['channelId' => $channel->getId()];
    }

    /**
     * @Route("/view/{id}", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_customer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Customer"
     * )
     * @Template
     */
    public function viewAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @Route("/info/{id}", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function infoAction(Customer $customer)
    {
        return ['entity' => $customer];
    }
}
