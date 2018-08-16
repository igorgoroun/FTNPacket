<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/24/17
 * Time: 15:13
 */

namespace snakemkua\FTNPacket;

/**
 * Class Address
 * @package snakemkua\FTNPacket
 */
class Address
{
    private $zone = null;
    private $region = null;
    private $network = null;
    private $node = null;
    private $point = null;
    private $domain = null;

    function __construct(string $address) {
        if (!$this->parse(trim($address))) {
            throw new \InvalidArgumentException("Invalid source address");
        }
    }

    private function parse (string $address):bool {
        $pattern = "/(\d{1,})(?:\:)(\d{1,})(?:\/)(\d{1,})(?:(?:\.)?(\d{1,})|)(?:(?:\@)?(.+)|)/";
        if (preg_match($pattern,$address,$data)) {
            $this->setProperties($data);
            return true;
        } else {
            return false;
        }
    }

    protected function setProperties ($data) {
        $this->setZone(intval($data[1]));
        $this->setNetwork(intval($data[2]));
        $this->setNode(intval($data[3]));
        if (isset($data[4]) && $data[4] !== null) $this->setPoint(intval($data[4]));
        if (isset($data[5]) && $data[5] !== null) $this->setDomain($data[5]);
    }

    /**
     * @return bool|string
     */
    public function dump () {
        if ($this->getZone() != null && $this->getNetwork() != null && $this->getNode() != null) {
            $address = sprintf("%d:%d/%d", $this->getZone(), $this->getNetwork(), $this->getNode());
            if ($this->getPoint() != null) {
                $address .= sprintf(".%d", $this->getPoint());
            }
            return $address;
        } else return false;
    }

    /**
     * @return bool|string
     */
    public function dumpNode () {
        if ($this->getZone() != null && $this->getNetwork() != null && $this->getNode() != null) {
            $address = sprintf("%d:%d/%d", $this->getZone(), $this->getNetwork(), $this->getNode());
            return $address;
        } else return false;
    }

    /**
     * @return bool|string
     */
    public function dumpFull () {
        if ($address = $this->dump()) {
            if ($this->getDomain() != null) {
                $address .= sprintf("@%s",$this->getDomain());
            }
            return $address;
        } else return false;
    }


    /**
     * @return null
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param null $zone
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
    }

    /**
     * @return null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param null $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return null
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @param null $network
     */
    public function setNetwork($network)
    {
        $this->network = $network;
    }

    /**
     * @return null
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param null $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return null
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param null $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param null $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }



}