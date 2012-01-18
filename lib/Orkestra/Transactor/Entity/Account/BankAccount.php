<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Transactor\Entity\AccountBase,
    \DateTime;

/**
 * Bank Account Entity
 *
 * Represents a single bank account
 *
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class BankAccount extends AccountBase
{
    const HOLDER_TYPE_BUSINESS = 'bank.holder.business';
    const HOLDER_TYPE_PERSONAL = 'bank.holder.personal';
    
    const TYPE_CHECKING = 'bank.type.checking';
    const TYPE_SAVINGS = 'bank.type.savings';
    
    /**
     * @var string
     * @ORM\Column(name="ach_routing_number", type="string", nullable=true)
     */
    protected $routingNumber;
    
    /**
     * @var string
     * @ORM\Column(name="account_holder_type", type="string")
     */
    protected $accountHolderType;
    
    /**
     * @var string
     * @ORM\Column(name="account_type", type="string")
     */
    protected $accountType;
    
    /**
     * Set Account Holder Type
     *
     * @param string $accountHolderType A valid account holder type
     */
    public function setAccountHolderType($accountHolderType)
    {
        if (!in_array($type, array(self::HOLDER_TYPE_BUSINESS, self::HOLDER_TYPE_PERSONAL))) {
            throw new \InvalidArgumentException('Invalid account holder type specified.');
        }
        
        $this->accountHolderType = $accountHolderType;
    }

    /**
     * Get Account Holder Type
     *
     * @return string
     */
    public function getAccountHolderType()
    {
        return $this->accountHolderType;
    }
    
    /**
     * Set Account Type
     *
     * @param string $accountType A valid account type
     */
    public function setAccountType($accountType)
    {
        if (!in_array($type, array(self::TYPE_CHECKING, self::TYPE_SAVINGS))) {
            throw new \InvalidArgumentException('Invalid account type specified.');
        }
        
        $this->accountType = $accountType;
    }

    /**
     * Get Account Type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }
}