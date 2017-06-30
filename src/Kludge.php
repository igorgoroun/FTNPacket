<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/22/17
 * Time: 22:56
 */

namespace IgorGoroun\FTNPacket;


class Kludge
{
    /**
     * @var string|null
     */
    private $label = null;
    /**
     * @var string|null
     */
    private $value = null;

    /**
     * @return null|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param null|string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null|string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


}