<?php

namespace Orkestra\Transactor\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Transactor Repository
 *
 * Responsible for injecting the DependencyInjection Container into loaded Transactors
 */
class TransactorRepository extends EntityRepository
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $_container;
    
    /**
     * Set Container
     *
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->_container = $container;
    }
    
    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        $entity = parent::find($id, $lockMode, $lockVersion);
        
        if ($entity) {
            $entity->setContainer($this->_container);
        }
        
        return $entity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $results = parent::findBy($criteria, $orderBy, $limit, $offset);
        
        foreach ($results as $result) {
            $result->setContainer($this->_container);
        }
        
        return $results;
    }
    
    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $entity = parent::findOneBy($criteria);
        
        if ($entity) {
            $entity->setContainer($this->_container);
        }
        
        return $entity;
    }
}