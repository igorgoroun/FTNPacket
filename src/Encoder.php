<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/23/17
 * Time: 19:37
 */

namespace snakemkua\FTNPacket;


class Encoder
{
    private $default_local = 'utf-8';
    private $default_outer = 'cp866';
    private $message_chrs = null;
    private $message = null;

    public function __construct(Message &$message) {
        $this->message = $message;
        $this->setCHRS();
    }

    public function decode () {
        foreach ($this->message->getEncodedFields() as $encodedField) {
            $this->message->setProperty($encodedField,iconv($this->message_chrs,$this->default_local,$this->message->getProperty($encodedField)));
        }
    }

    public function encode () {
        foreach ($this->message->getEncodedFields() as $encodedField) {
            $this->message->setProperty($encodedField,iconv($this->default_local,$this->message_chrs,$this->message->getProperty($encodedField)));
        }
    }

    private function setCHRS () {
        $kludge = $this->message->hasKludge('CHRS');
        if ($kludge instanceof Kludge) {
            $parts = explode(' ', $kludge->getValue());
            /* Hardcoded invalid codepages */
            if (in_array(mb_strtoupper($parts[0]), ['IBMPC', 'FIDO'])) {
                $parts[0] = $this->default_outer;
            }
            # set codepage
            $this->message_chrs = mb_strtolower($parts[0]);
        } else {
            $this->message_chrs = $this->default_outer;
        }
    }

}