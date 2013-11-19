<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Class MagentoSoapTransport
 *
 * @package OroCRM\Bundle\MagentoBundle\Entity
 * @ORM\Entity
 */
class MagentoSoapTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="wsdl_url", type="string", length=255, nullable=false)
     */
    protected $wsdlUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="api_user", type="string", length=255, nullable=false)
     */
    protected $apiUser;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=false)
     */
    protected $apiKey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    protected $syncStartDate;

    /**
     * @var \DateInterval
     *
     * @ORM\Column(type="string", length=50)
     */
    protected $syncRange;

    /**
     * @param string $wsdlUrl
     *
     * @return $this
     */
    public function setWsdlUrl($wsdlUrl)
    {
        $this->wsdlUrl = $wsdlUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * @param string $apiUser
     *
     * @return $this
     */
    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param \DateTime $syncStartDate
     *
     * @return $this
     */
    public function setSyncStartDate(\DateTime $syncStartDate)
    {
        $this->syncStartDate = $syncStartDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSyncStartDate()
    {
        return $this->syncStartDate;
    }

    /**
     * @param \DateInterval $syncRange
     * @return $this
     */
    public function setSyncRange($syncRange)
    {
        $this->syncRange = $syncRange;

        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getSyncRange()
    {
        return $this->syncRange;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        return new ParameterBag(
            [
                'api_user' => $this->getApiUser(),
                'api_key'  => $this->getApiKey(),
                'wsdl_url' => $this->getWsdlUrl(),
            ]
        );
    }
}
