<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 20:50
 */

namespace IgorGoroun\FTNPacketBundle\Entity;

/**
 * Class Message
 * @package IgorGoroun\FTNPacketBundle\Entity
 */
class Message
{
    private $nodeFrom = null;
    private $nodeTo = null;
    private $netFrom = null;
    private $netTo = null;
    private $date = null;
    private $subject = null;
    private $echomail = false;
    private $area = null;
    private $origMsgID = null;
    private $origName = null;
    private $origAddr = null;
    private $destName = null;
    private $destAddr = null;
    private $attribute = null;
    private $body = null;
    private $origin = null;
    private $tearline = null;
    private $kludges = array();
    private $controls = array();
    private $uuefiles = array();
    private $seenby = null;

    private $encoded_fields = array(
        'subject',
        'origName',
        'destName',
        'body',
        'origin',
        'tearline'
    );


    public function setProperty ($property, $value) {
        if (property_exists($this,$property)) {
            $this->$property = $value;
        }
    }
    public function getProperty ($property) {
        if (property_exists($this,$property)) {
            return $this->$property;
        }
    }

    /**
     * @return int|null
     */
    public function getNodeFrom()
    {
        return $this->nodeFrom;
    }

    /**
     * @param int|null $nodeFrom
     */
    public function setNodeFrom($nodeFrom)
    {
        $this->nodeFrom = $nodeFrom;
    }

    /**
     * @return int|null
     */
    public function getNodeTo()
    {
        return $this->nodeTo;
    }

    /**
     * @param int|null $nodeTo
     */
    public function setNodeTo($nodeTo)
    {
        $this->nodeTo = $nodeTo;
    }

    /**
     * @return int|null
     */
    public function getNetFrom()
    {
        return $this->netFrom;
    }

    /**
     * @param int|null $netFrom
     */
    public function setNetFrom($netFrom)
    {
        $this->netFrom = $netFrom;
    }

    /**
     * @return int|null
     */
    public function getNetTo()
    {
        return $this->netTo;
    }

    /**
     * @param int|null $netTo
     */
    public function setNetTo($netTo)
    {
        $this->netTo = $netTo;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string|null $date
     */
    public function setDate($date)
    {
        if ($date = new \DateTime($date)) {
            $this->date = $date;
        } else {
            $this->date = new \DateTime();
        }
    }

    /**
     * @return null
     */
    public function getOrigName()
    {
        return $this->origName;
    }

    /**
     * @param null $origName
     */
    public function setOrigName($origName)
    {
        $this->origName = $origName;
    }

    /**
     * @return null
     */
    public function getDestName()
    {
        return $this->destName;
    }

    /**
     * @param null $destName
     */
    public function setDestName($destName)
    {
        $this->destName = $destName;
    }

    /**
     * @return null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param null $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }


    /**
     * @return bool
     */
    public function isEchomail(): bool
    {
        return $this->echomail;
    }

    /**
     * @return bool
     */
    public function isNetmail(): bool
    {
        return (!$this->echomail && $this->area == null);
    }

    /**
     * @param bool $echomail
     */
    public function setEchomail(bool $echomail)
    {
        $this->echomail = $echomail;
    }

    /**
     * @return null
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param null $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return Address|null
     */
    public function getOrigAddr()
    {
        return $this->origAddr;
    }

    /**
     * @param Address|null $origAddr
     */
    public function setOrigAddr(Address $origAddr)
    {
        $this->origAddr = $origAddr;
    }

    /**
     * @return Address|null
     */
    public function getDestAddr()
    {
        return $this->destAddr;
    }

    /**
     * @param Address $destAddr
     */
    public function setDestAddr(Address $destAddr)
    {
        $this->destAddr = $destAddr;
    }

    /**
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return null
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param null $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return null
     */
    public function getTearline()
    {
        return $this->tearline;
    }

    /**
     * @param null $tearline
     */
    public function setTearline($tearline)
    {
        $this->tearline = $tearline;
    }

    /**
     * @return array
     */
    public function getKludges()
    {
        return $this->kludges;
    }

    /**
     * @param Kludge $kludge
     */
    public function addKludge($kludge)
    {
        $this->kludges []= $kludge;
    }

    public function hasKludge($kludgeName)
    {
        $result = false;
        foreach ($this->getKludges() as $num=>$kludge) {
            if ($kludge->getLabel() == $kludgeName) {
                $result = $kludge;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getControls()
    {
        return $this->controls;
    }

    /**
     * @param Kludge $kludge
     */
    public function addControl($kludge)
    {
        $this->controls []= $kludge;
    }

    public function hasControl($controlName)
    {
        $result = false;
        foreach ($this->getControls() as $num=>$kludge) {
            if ($kludge->getLabel() == $controlName) {
                $result = $kludge;
            }
        }
        return $result;
    }
    /**
     * @return array
     */
    public function getEncodedFields(): array
    {
        return $this->encoded_fields;
    }

    /**
     * @return null
     */
    public function getSeenby()
    {
        return $this->seenby;
    }

    /**
     * @param null $seenby
     */
    public function setSeenby($seenby)
    {
        $this->seenby = $seenby;
    }

    /**
     * @return null
     */
    public function getOrigMsgID()
    {
        return $this->origMsgID;
    }

    /**
     * @param null $origMsgID
     */
    public function setOrigMsgID($origMsgID)
    {
        $this->origMsgID = $origMsgID;
    }

    /**
     * @return null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param null $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }


    /**
     * @return array
     */
    public function getUuefiles()
    {
        return $this->uuefiles;
    }

    /**
     * @param Uuefile $uuefile
     */
    public function addUuefile($uuefile)
    {
        $this->uuefiles []= $uuefile;
    }

    /**
     * @return Uuefile|null
     */
    public function lastUuefile() {
        if (count($this->uuefiles)>0) {
            return $this->uuefiles[count($this->uuefiles)-1];
        } else {
            return null;
        }
    }
}