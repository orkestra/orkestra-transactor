<?php

namespace Orkestra\Transactor\Entities;

use Doctrine\ORM\Mapping as ORM,
    \DateTime;

/**
 * Entity Base
 *
 * Base class for all entities
 *
 * @ORM\MappedSuperClass
 * @ORM\HasLifecycleCallbacks
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = true;

    /**
     * @var Model\Common\Data\DateTime $dateModified
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     */
    protected $dateModified = null;

    /**
     * @var Model\Common\Data\DateTime $dateCreated
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    public function __construct()
    {
        $this->dateCreated = new DateTime();
    }
    
    public function __toString()
    {
        return sprintf('%s:%s', get_class($this), spl_object_hash($this));
    }
    
    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * Set Active
     *
     * @param boolean
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get Active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * Is Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getActive();
    }

    /**
     * Get Date Created
     *
     * @return Model\Common\Data\DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Get Date Modified
     *
     * @return Model\Common\Data\DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @ORM\preUpdate
     */
    public function preUpdate()
    {
        $this->dateModified = new DateTime();
    }

    /**
     * {@inheritdoc}
     *
     * @ORM\prePersist
	 * @ORM\preUpdate
     */
    public function validate()
    {
    }
}
