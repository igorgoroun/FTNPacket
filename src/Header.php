<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 20:50
 */

namespace snakemkua\FTNPacket;

/**
 * Class Header
 * @package snakemkua\FTNPacket
 */
class Header
{
    private $nodeFrom = null;
    private $nodeTo = null;
    private $netFrom = null;
    private $netTo = null;
    private $zoneFrom = null;
    private $zoneTo = null;
    private $dateYear = null;
    private $dateMonth = null;
    private $dateDay = null;
    private $dateHour = null;
    private $dateMinute = null;
    private $dateSecond = null;
    private $date = null;
    private $password = null;

    /**
     * @param $property
     * @param $value
     */
    public function setProperty ($property, $value) {
        if (property_exists($this,$property)) {
            $this->$property = $value;
        }
    }
    /**
     * @return bool
     */
    public function valid() {
        $params = false;
        if ($this->getNodeFrom() !== null
            && $this->getNodeTo() !== null
            && $this->getNetFrom() !== null
            && $this->getNetTo() !== null
            && $this->getZoneFrom() !== null
            && $this->getZoneTo() !== null
            && $this->getDateYear() !== null
            && $this->getDateMonth() !== null
            && $this->getDateDay() !== null
            && $this->getDateHour() !== null
            && $this->getDateMinute() !== null
            && $this->getDateSecond() !== null
            && $this->getPassword() !== null
        ) {
            $params = true;
        }

        return $params;
    }
    /**
     * @param Address $address
     */
    public function setFrom(Address $address) {
        $this->setZoneFrom($address->getZone());
        $this->setNetFrom($address->getNetwork());
        $this->setNodeFrom($address->getNode());
    }

    /**
     * @param Address $address
     */
    public function setTo(Address $address) {
        $this->setZoneTo($address->getZone());
        $this->setNetTo($address->getNetwork());
        $this->setNodeTo($address->getNode());
    }
    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        if ($this->getDateYear() == null || $this->getDateMonth() == null || $this->getDateDay() == null ||
            $this->getDateHour() == null || $this->getDateMinute() == null || $this->getDateSecond() == null)
        {
            $this->setDateYear($date->format('Y'));
            $this->setDateMonth($date->format('m')-1);
            $this->setDateDay($date->format('d'));
            $this->setDateHour($date->format('H'));
            $this->setDateMinute($date->format('i'));
            $this->setDateSecond($date->format('s'));
        }
    }
    /**
     * @param null
     */
    public function calculateDate()
    {
        $date = new \DateTime();
        $ts = mktime(
            $this->getDateHour(),
            $this->getDateMinute(),
            $this->getDateSecond(),
            $this->getDateMonth()+1,
            $this->getDateDay(),
            $this->getDateYear());
        $date->setTimestamp($ts);
        $this->setDate($date);
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
     * @return int|null
     */
    public function getZoneFrom()
    {
        return $this->zoneFrom;
    }

    /**
     * @param int|null $zoneFrom
     */
    public function setZoneFrom($zoneFrom)
    {
        $this->zoneFrom = $zoneFrom;
    }

    /**
     * @return int|null
     */
    public function getZoneTo()
    {
        return $this->zoneTo;
    }

    /**
     * @param int|null $zoneTo
     */
    public function setZoneTo($zoneTo)
    {
        $this->zoneTo = $zoneTo;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


    /**
     * @return int|null
     */
    public function getDateYear()
    {
        return $this->dateYear;
    }

    /**
     * @param int|null $dateYear
     */
    public function setDateYear($dateYear)
    {
        $this->dateYear = $dateYear;
    }

    /**
     * @return int|null
     */
    public function getDateMonth()
    {
        return $this->dateMonth;
    }

    /**
     * @param int|null $dateMonth
     */
    public function setDateMonth($dateMonth)
    {
        $this->dateMonth = $dateMonth;
    }

    /**
     * @return int|null
     */
    public function getDateDay()
    {
        return $this->dateDay;
    }

    /**
     * @param int|null $dateDay
     */
    public function setDateDay($dateDay)
    {
        $this->dateDay = $dateDay;
    }

    /**
     * @return int|null
     */
    public function getDateHour()
    {
        return $this->dateHour;
    }

    /**
     * @param int|null $dateHour
     */
    public function setDateHour($dateHour)
    {
        $this->dateHour = $dateHour;
    }

    /**
     * @return int|null
     */
    public function getDateMinute()
    {
        return $this->dateMinute;
    }

    /**
     * @param int|null $dateMinute
     */
    public function setDateMinute($dateMinute)
    {
        $this->dateMinute = $dateMinute;
    }

    /**
     * @return int|null
     */
    public function getDateSecond()
    {
        return $this->dateSecond;
    }

    /**
     * @param int|null $dateSecond
     */
    public function setDateSecond($dateSecond)
    {
        $this->dateSecond = $dateSecond;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }


}