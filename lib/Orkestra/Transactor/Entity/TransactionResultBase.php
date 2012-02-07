<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM,
    \DateTime;

/**
 * Transaction Result Base
 *
 * Base class for all Transaction Results
 *
 * @ORM\Table(name="orkestra_transaction_results")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "ApprovedResult" = "Orkestra\Transactor\Entity\TransactionResult\ApprovedResult",
 *   "DeclinedResult" = "Orkestra\Transactor\Entity\TransactionResult\DeclinedResult",
 *   "ErrorResult" = "Orkestra\Transactor\Entity\TransactionResult\ErrorResult"
 * })
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class TransactionResultBase extends EntityBase
{
    /**
     * @var string $message
     * @ORM\Column(name="message", type="string")
     */
    protected $message = '';
    
    /**
     * @var array $data
     * @ORM\Column(name="data", type="array")
     */
    protected $data = array();
        
    /**
     * @var Orkestra\Transactor\Entity\Transaction
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\Transaction", inversedBy="result", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    protected $transaction;
    
    /**
     * @var Orkestra\Transactor\Entity\Transactor
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\TransactorBase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transactor_id", referencedColumnName="id")
     * })
     */
    protected $transactor;
    
    /**
     * Constructor
     *
     * @param Orkestra\Transactor\Entity\TransactorBase $transactor
     * @param Orkestra\Transactor\Entity\Transaction $transaction
     */
    public function __construct(TransactorBase $transactor, Transaction $transaction, $message = '', $data = array())
    {
        parent::__construct();
        
        $this->transactor = $transactor;
        $this->transaction = $transaction;
        $this->message = $message;
        $this->data = (array)$data;
        $transaction->setResult($this);
    }
    
    /**
     * Set Message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Set Data
     *
     * @param string $key The key of which data to set
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Get Data
     *
     * @param string $key The key of which data to get
     * @return mixed
     */
    public function getData($key)
    {
        return empty($this->data[$key]) ? null : $this->data[$key];
    }
}