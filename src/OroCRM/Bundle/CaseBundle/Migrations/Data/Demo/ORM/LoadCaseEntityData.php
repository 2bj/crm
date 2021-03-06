<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

class LoadCaseEntityData extends AbstractFixture implements DependentFixtureInterface
{
    const FIXTURES_COUNT = 20;

    /**
     * @var array
     */
    static protected $fixtureSubjects = array(
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit',
        'Aenean commodo ligula eget dolor',
        'Aenean massa',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem',
        'Nulla consequat massa quis enim',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu',
        'In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo',
        'Nullam dictum felis eu pede mollis pretium',
        'Integer tincidunt',
        'Cras dapibus',
        'Vivamus elementum semper nisi',
        'Aenean vulputate eleifend tellus',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim',
        'Aliquam lorem ante, dapibus in, viverra quis, feugiat',
        'Aenean imperdiet. Etiam ultricies nisi vel',
        'Praesent adipiscing',
        'Integer ante arcu',
        'Curabitur ligula sapien',
        'Donec posuere vulputate'
    );

    /**
     * @var array
     */
    static protected $fixtureDescriptions = array(
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet.',
        'Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi..',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra,',
        'Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel.',
        'Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.',
        'Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed.',
        'Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus.',
        'Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus.',
        'Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales.',
        'Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus.',
        'Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus.',
        'Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum.',
        'Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing.',
        'Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum.',
        'Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet.',
        'Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi. Curabitur ligula sapien, tincidunt non.',
        'Praesent congue erat at massa. Sed cursus turpis vitae tortor. Donec posuere vulputate arcu.',
    );

    /**
     * @var array
     */
    static protected $relatedEntities = array(
        'OroCRMContactBundle:Contact'   => 'setRelatedContact',
        'OroCRMAccountBundle:Account'   => 'setRelatedAccount',
    );

    /**
     * @var array
     */
    protected $entitiesCount;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array(
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::FIXTURES_COUNT; ++$i) {
            $owner = $this->getRandomEntity('OroUserBundle:User', $manager);
            $assignedTo = $this->getRandomEntity('OroUserBundle:User', $manager);
            $source = $this->getRandomEntity('OroCRMCaseBundle:CaseSource', $manager);
            $status = $this->getRandomEntity('OroCRMCaseBundle:CaseStatus', $manager);
            $priority = $this->getRandomEntity('OroCRMCaseBundle:CasePriority', $manager);

            if (!$owner || !$assignedTo || !$source || !$status) {
                // If we don't have users, sources and status we cannot load fixture cases
                break;
            }

            if ($manager->getRepository('OroCRMCaseBundle:CaseEntity')->findOneBySubject(self::$fixtureSubjects[$i])) {
                // Case with this title is already exist
                continue;
            }

            $case = new CaseEntity();
            $case->setSubject(self::$fixtureSubjects[$i]);
            $case->setDescription(self::$fixtureDescriptions[$i]);
            $case->setReportedAt($this->getRandomDate());

            $case->setOwner($owner);
            $case->setAssignedTo($assignedTo);
            $case->setSource($source);
            $case->setStatus($status);
            $case->setPriority($priority);

            switch (rand(0, 1)) {
                case 0:
                    $contact = $this->getRandomEntity('OroCRMContactBundle:Contact', $manager);
                    if ($contact) {
                        $case->setRelatedContact($contact);
                    }
                    break;
                case 1:
                default:
                    $account = $this->getRandomEntity('OroCRMAccountBundle:Account', $manager);
                    if ($account) {
                        $case->setRelatedAccount($account);
                    }
                    break;
            }

            $manager->persist($case);
        }

        $manager->flush();
    }

    /**
     * @param string $entityName
     * @param EntityManager $manager
     * @return object|null
     */
    protected function getRandomEntity($entityName, EntityManager $manager)
    {
        $count = $this->getEntityCount($entityName, $manager);

        if ($count) {
            return $manager->createQueryBuilder()
                ->select('e')
                ->from($entityName, 'e')
                ->setFirstResult(rand(0, $count - 1))
                ->setMaxResults(1)
                ->orderBy('e.' . $manager->getClassMetadata($entityName)->getSingleIdentifierFieldName())
                ->getQuery()
                ->getSingleResult();
        }

        return null;
    }

    /**
     * @param string $entityName
     * @param EntityManager $manager
     * @return int
     */
    protected function getEntityCount($entityName, EntityManager $manager)
    {
        if (!isset($this->entitiesCount[$entityName])) {
            $this->entitiesCount[$entityName] = (int)$manager->createQueryBuilder()
                ->select('COUNT(e)')
                ->from($entityName, 'e')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->entitiesCount[$entityName];
    }

    /**
     * @return \DateTime
     */
    protected function getRandomDate()
    {
        $result = new \DateTime();
        $result->sub(new \DateInterval(sprintf('P%dDT%dM', rand(0, 30), rand(0, 1440))));

        return $result;
    }
}
