<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 20:50
 */

namespace snakemkua\FTNPacket;

/**
 * Class Message
 * @package snakemkua\FTNPacket
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
    private $cost = null;
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

    // FUNCTIONS
    public function __construct() {
        // slow generator... or not?
        // $this->setOrigMsgID(hash('crc32b', microtime()));
        // another msgid generator, thanks to Phito:
        $this->setOrigMsgID(sprintf("%08x", rand(0,0xFFFF)|(rand(0,0xFFFF)<<16)));
    }

    /**
     * Use this method after set main variables
     * to create message controls, kludges, etc...
     */
    public function prepare() {
        // generate netmail controls
        if ($this->isNetmail()) {
            $this->generateNetmailControls();
        // generate echomail controls
        } else {
            $this->generateEchomailControls();
        }
        // create CHRS
        $enc = new Kludge();
        $enc->setLabel('CHRS');
        $enc->setValue('UTF-8');
        $this->addKludge($enc);
        // create TZ
        $tz = new Kludge();
        $tz->setLabel('TZUTC');
        $tz->setValue(sprintf('%+03d00', $this->getDate()->getOffset()/3600));
        $this->addKludge($tz);
        // create MSGID
        $msgid = new Kludge();
        $msgid->setLabel('MSGID');
        $msgid->setValue($this->getOrigAddr()->dump().' '.$this->getOrigMsgID());
        $this->addKludge($msgid);

        // return validation check
        return $this->valid();
    }

    /**
     * This private method generates control paragraphs for netmail message
     */
    private function generateNetmailControls() {
        // need FMPT
        if ($this->getOrigAddr()->getPoint() !== null) {
            $fmpt = new Kludge();
            $fmpt->setLabel('FMPT');
            $fmpt->setValue($this->getOrigAddr()->getPoint());
            $this->addControl($fmpt);
        }
        // need TOPT
        if ($this->getDestAddr()->getPoint() !== null) {
            $topt = new Kludge();
            $topt->setLabel('TOPT');
            $topt->setValue($this->getDestAddr()->getPoint());
            $this->addControl($topt);
        }
        // create INTL
        $intl = new Kludge();
        $intl->setLabel('INTL');
        $intl->setValue($this->getDestAddr()->dumpNode().' '.$this->getOrigAddr()->dumpNode());
        $this->addControl($intl);
    }

    /**
     * This private method generates data for echomail message
     */
    private function generateEchomailControls() {
        return true;
    }

    public function valid() {
        $params = false;
        if ($this->nodeFrom !== null
            && $this->nodeTo !== null
            && $this->netFrom !== null
            && $this->netTo !== null
            && $this->date !== null
            && $this->origMsgID !== null
            && $this->origName !== null
            && $this->origAddr !== null
            && $this->destName !== null
            ) {
            $params = true;
        }

        if ($this->isNetmail()) {
            $controls = false;
            //if ($this->hasControl('TOPT') && $this->hasControl('FMPT') && $this->hasControl('INTL')) {
            if ($this->destAddr !== null) {
                $controls = true;
            }
            //}
        } else {
            $controls = true;
        }

        // final check
        if ($params && $controls) {
            return true;
        } else {
            return false;
        }
    }
    public function setProperty ($property, $value) {
        if (property_exists($this,$property)) {
            $this->$property = $value;
        } else {
            throw new \Exception('Property doesn\'t exists: '.$property);
        }
    }
    public function getProperty ($property) {
        if (property_exists($this,$property)) {
            return $this->$property;
        } else {
            throw new \Exception('Property doesn\'t exists: '.$property);
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
     * @param \Datetime|string|null $date
     */
    public function setDate($date=null)
    {
        if ($date instanceof \DateTime) {
            $this->date = $date;
        } elseif (is_string($date)) {
            if ($date_parsed = new \DateTime($date)) {
                $this->date = $date_parsed;
            } else {
                $this->date = new \DateTime();
            }
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
        $this->setEchomail(true);
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
        // just set address
        $this->origAddr = $origAddr;
        // set message properties
        $this->setNetFrom($origAddr->getNetwork());
        $this->setNodeFrom($origAddr->getNode());
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
        // just set address
        $this->destAddr = $destAddr;
        // set message properties
        $this->setNetTo($destAddr->getNetwork());
        $this->setNodeTo($destAddr->getNode());
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
        if (preg_match("/^(?:.+)(?:\()(.+)(?:\))$/", $origin)) {
            $originLine = $origin;
        } else {
            if ($this->getOrigAddr() instanceof Address) {
                $originLine = sprintf("%s (%s)", $origin, $this->getOrigAddr()->dump());
            } else {
                $originLine = $origin;
            }
        }
        $this->origin = $originLine;
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
     * @used-by Parser::parsePacket()
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
     * @return null
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param null $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
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