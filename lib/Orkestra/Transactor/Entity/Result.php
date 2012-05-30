<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM;
use Orkestra\Common\Entity\EntityBase;
use Orkestra\Transactor\TransactorInterface;

/**
 * Represents the result of a transaction
 *
 * @ORM\Table(name="orkestra_results")
 * @ORM\Entity
 */
class Result extends EntityBase
{
    /**
     * @var string $externalId
     *
     * @ORM\Column(name="external_id", type="string")
     */
    protected $externalId = '';

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="string")
     */
    protected $message = '';

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="array")
     */
    protected $data = array();

    /**
     * @var \Orkestra\Transactor\Entity\Result\ResultType
     *
     * @ORM\Column(name="type", type="enum.orkestra.result_type")
     */
    protected $type;

    /**
     * @var \Orkestra\Transactor\Entity\Transaction
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\Transaction", inversedBy="result", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    protected $transaction;

    /**
     * @var string
     *
     * @ORM\Column(name="transactor", type="string")
     */
    protected $transactor;

    /**
     * Constructor
     *
     * @param \Orkestra\Transactor\TransactorInterface $transactor
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param string $externalId
     * @param string $message
     * @param array $data
     */
    public function __construct(TransactorInterface $transactor, Transaction $transaction, $externalId = '', $message = '', $data = array())
    {
        $this->transactor = $transactor->getType();
        $this->transaction = $transaction;
        $this->externalId = $externalId;
        $this->message = $message;
        $this->data = (array)$data;
        $transaction->setResult($this);
    }

    /**
     * Gets the associated Transaction
     *
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    public function getTransaction()
    {
    	return $this->transaction;
    }

    /**
     * Set External ID
     *
     * @param string $externalId
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * Get External ID
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
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

    /**
     * Sets the result type
     *
     * @param \Orkestra\Transactor\Entity\Result\ResultType $type
     */
    public function setType(Result\ResultType $type)
    {
        $this->type = $type;
    }

    /**
     * Gets the result type
     *
     * @return \Orkestra\Transactor\Entity\Result\ResultType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the transactor
     *
     * @param string $transactor
     */
    public function getTransactor()
    {
        return $this->transactor;
    }
}
