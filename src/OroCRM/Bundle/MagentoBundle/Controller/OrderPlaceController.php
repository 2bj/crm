<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

/**
 * @Route("/order/place")
 */
class OrderPlaceController extends Controller
{
    const FLOW_NAME       = 'oro_sales_new_order';
    const GATEWAY_ROUTE   = 'oro_gateway/do';
    const NEW_ORDER_ROUTE = 'oro_sales/newOrder';

    /**
     * @Route("/cart/{id}", name="orocrm_magento_orderplace_cart", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function cartAction(Cart $cart)
    {
        $channel = $cart->getChannel();

        $error = $sourceUrl = false;
        try {
            /** @var MagentoTransportInterface $transport */
            $transport = $this->get('oro_integration.provider.connector_context_mediator')->getTransport($channel);
            $transport->init($channel->getTransport());

            $url = $transport->getAdminUrl();
            if (false === $url) {
                throw new ExtensionRequiredException();
            }

            $successUrl = urlencode(
                $this->generateUrl(
                    'orocrm_magento_orderplace_cart_success',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $errorUrl = urlencode(
                $this->generateUrl(
                    'orocrm_magento_orderplace_external_error',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $sourceUrl = sprintf(
                '%s/%s?quote=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($url, '/'),
                self::GATEWAY_ROUTE,
                $cart->getOriginId(),
                self::NEW_ORDER_ROUTE,
                self::FLOW_NAME,
                $successUrl,
                $errorUrl
            );
        } catch (ExtensionRequiredException $e) {
            $error = $e->getMessage();
        } catch (\LogicException $e) {
            $error = 'orocrm.magento.controller.transport_not_configure';
        }

        $backUrl = $this->generateUrl('orocrm_magento_cart_view', ['id' => $cart->getId()]);
        if (false !== $error) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($error));
            return $this->redirect($backUrl);
        }

        return [
            'entity'            => $cart,
            'requireTransition' => true,
            'sourceUrl'         => $sourceUrl,
            'backUrl'           => $backUrl
        ];
    }

    /**
     * @Route("/customer/{id}", name="orocrm_magento_orderplace_customer", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function customerAction(Customer $customer)
    {
        var_dump($customer);
        // @TODO order creation from customer page
        return [];
    }

    /**
     * @Route("/cart/success", name="orocrm_magento_orderplace_cart_success"))
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function cartSuccessAction()
    {

        return [];
    }

    /**
     * @Route("/error", name="orocrm_magento_orderplace_external_error"))
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function errorAction()
    {
        return [];
    }
}
