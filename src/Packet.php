<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 20:47
 */

namespace snakemkua\FTNPacket;

/**
 * Class Packet
 * @package snakemkua\FTNPacket
 */
class Packet
{
    /**
     * @var Header|null
     */
    private $header = null;

    /**
     * @var Address|null
     */
    private $realRecipient = null;

    /**
     * @var array
     */
    private $messages = array();

    function __construct() {
        $this->header = new Header();
        $this->messages = array();
    }
    public function valid() {
        // header
        if (!$this->getHeader()->valid()) {
            echo "header invalid";
            return false;
        }
        // messages
        $valid = true;
        foreach ($this->getMessages() as $message) {
            if (!$message->valid()) {
                $valid = false;
            }
        }

        //if ($this->realRecipient == null) $valid = false;

        return $valid;
    }

    public function setFrom(Address $address) {
        $this->getHeader()->setFrom($address);
    }
    public function setTo(Address $address) {
        $this->getHeader()->setTo($address);
        $this->setRealRecipient($address);
    }
    public function setDate(\DateTime $date) {
        $this->getHeader()->setDate($date);
    }
    public function setPassword(string $password) {
        $this->getHeader()->setPassword($password);
    }

    /**
     * @param Address $realRecipient
     */
    public function setRealRecipient(Address $realRecipient)
    {
        $this->realRecipient = $realRecipient;
    }

    /**
     * @return Address|null
     */
    public function getRealRecipient()
    {
        return $this->realRecipient;
    }

    /**
     * @param Header $header
     */
    public function setHeader(Header $header)
    {
        $this->header = $header;
    }

    /**
     * @return Header|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return array|null
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     */
    public function addMessage($message)
    {
        $this->messages []= $message;
    }

}