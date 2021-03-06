<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/22/17
 * Time: 22:56
 */

namespace snakemkua\FTNPacket;


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

    private const rfcKludges = array(
        'INTL',
        'FMPT',
        'TOPT',
        'MSGID',
        'REPLY',
        'REPLYADDR',
        'REPLYTO',
        'CHRS',
        'TZUTC',
        'TID',
        'PID',
        'Via',
        'SEEN-BY',
        'PATH'
    );

    public function __construct($label = null, $value = null) {
        if ($label !== null && $value !== null && in_array($label, Kludge::rfcKludges)) {
            $this->setLabel($label);
            $this->setValue($value);
        }
    }

    // FUNCTIONS
    public function isValid() {
        if ($this->label !== null && $this->value !== null) {
            return true;
        } else {
            return false;
        }
    }

    // GETTERS AND SETTERS

    /**
     * @return null|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $label
     * @throws \Exception
     */
    public function setLabel($label)
    {
        if (in_array($label, Kludge::rfcKludges)) {
            $this->label = $label;
        } else {
            throw new \Exception('Label not in list of available kludges');
        }
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