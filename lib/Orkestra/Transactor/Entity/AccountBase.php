<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection,
    \DateTime;

/**
 * Account Base
 *
 * Base class for all Account entities
 *
 * @ORM\Table(name="orkestra_accounts")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "BankAccount" = "Orkestra\Transactor\Entity\Account\BankAccount",
 *   "CardAccount" = "Orkestra\Transactor\Entity\Account\CardAccount"
 * })
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class AccountBase extends EntityBase
{
    /**
     * @var string
     * @ORM\Column(name="account_number", type="string")
     */
    protected $accountNumber;
    
    /**
     * @var string
     * @ORM\Column(name="ip_address", type="string")
     */
    protected $ipAddress = '';
    
    /**
     * @var Orkestra\Transactor\Entity\Transaction
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="account")
     */
    protected $transactions;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->transactions = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        parent::validate();
        
        if (empty($this->accountNumber)) {
            throw ValidationException::missingRequiredParameter('account number');
        }
    }
    
    /**
     * Set Account Number
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }
    
    /**
     * Get Account Number
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    
    /**
     * Set IP Address
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }
    
    /**
     * Get IP Address
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }
    
    /**
     * Add Transaction
     *
     * @param Orkestra\Transactor\Entity\Transaction
     */
    public function addTransaction(Transaction $transaction)
    {
        if ($transaction->getAccount() !== $this)
            $transaction->setAccount($this);
        
        $this->transactions[] = $transction;
    }
    
    /**
     * Get Transactions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}