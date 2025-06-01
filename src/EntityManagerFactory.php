<?php

namespace PHPMaker2025\ucarsip;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;

class EntityManagerFactory
{

    public static function create(Connection $connection, Configuration $config, EventManager $eventManager, bool $softDeleteable = false)
    {
        if ($softDeleteable) {
            $config->addFilter("soft-deleteable", SoftDeleteableFilter::class);
            $eventManager->addEventSubscriber(new SoftDeleteableListener());
        }
        $em = new EntityManager($connection, $config, $eventManager);
        if ($softDeleteable) {
            $em->getFilters()->enable("soft-deleteable");
        }
        return $em;
    }
}
