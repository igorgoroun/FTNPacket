<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 20:47
 */

namespace IgorGoroun\FTNPacketBundle\Entity;

/**
 * Class Packet
 * @package IgorGoroun\FTNPacketBundle\Entity
 */
class Packet
{
    /**
     * @var Header|null
     */
    private $header = null;

    /**
     * @var array
     */
    private $messages = array();

    function __construct() {
        $this->header = new Header();
        $this->messages = array();
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