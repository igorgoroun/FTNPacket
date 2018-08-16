<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/25/17
 * Time: 15:21
 */

namespace snakemkua\FTNPacket;

use wapmorgan\BinaryStream\BinaryStream;

/**
 * Class Writer
 * @package snakemkua\FTNPacket
 */
class Writer
{
    /**
     * @var Packet $packet
     */
    private $packet;
    /**
     * @var string $file
     */
    private $file;

    /**
     * @var BinaryStream $binary
     */
    private $binary;

    /**
     * @var bool $decode
     */
    private $decode = false;

    /**
     * Writer constructor.
     * @param Packet $packet
     * @param string $file
     * @throws \Exception
     */
    public function __construct(Packet &$packet, string $file) {
        if (!$packet->valid()) {
            throw new \Exception('Packet is not valid');
        }
        $this->packet = $packet;
        $this->file = $file;
        try {
            $this->binary = new BinaryStream($this->file, BinaryStream::RECREATE);
            $this->binary->setEndian(BinaryStream::LITTLE);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    public function writePacket () {
        // write header
        $this->writePacketHeader();

        // write messages
        foreach ($this->packet->getMessages() as $message) {
            if ($message instanceof Message) {
                $this->writeMessageHeader($message);
                // write netmail controls
                if ($message->isNetmail()) {
                    $this->writeMessageControls($message,false,['Via']);
                }
                $this->writeMessageKludges($message,false,['PATH']); //write header kludges except via
                $this->writeMessageBody($message);

                // write message footer (tearLine & origin)
                $this->writeMessageFooter($message);

                // write PATH
                $this->writeMessageKludges($message,['PATH']); //write header kludges except via

                // write Via controls
                if ($message->isNetmail() && $message->hasControl('Via')) {
                    $this->writeMessageControls($message,['Via']);
                }
                $this->binary->writeChar(0x00);
            }
        }

        // finalize packet with zero
        $this->binary->writeInteger(0,16);
    }

    /**
     * @param Message $message
     */
    private function writeMessageHeader(Message &$message) {
        $binary = &$this->binary;
        $binary->writeInteger(2,16);
        $binary->writeInteger($message->getNodeFrom(),16);
        $binary->writeInteger($message->getNodeTo(),16);
        $binary->writeInteger($message->getNetFrom(),16);
        $binary->writeInteger($message->getNetTo(),16);
        $binary->writeInteger($message->getAttribute(),16);
        $binary->writeInteger(0,16); // cost
        // write date
        $binary->writeString($message->getDate()->format('d M y  H:i:s'));
        $binary->writeChar(0x00);
        $binary->writeString($message->getDestName());
        $binary->writeChar(0x00);
        $binary->writeString($message->getOrigName());
        $binary->writeChar(0x00);
        $binary->writeString($message->getSubject());
        $binary->writeChar(0x00);
        // write area name if echomail message
        if ($message->isEchomail()) {
            $binary->writeString("AREA:".$message->getArea());
            $binary->writeChar(0x0D);
        }
    }

    private function writeMessageKludges(Message &$message, $include=false, $except=false) {
        $binary = &$this->binary;
        foreach ($message->getKludges() as $kludge) {
            if ($kludge instanceof Kludge && (!is_array($include) || in_array($kludge->getLabel(), $include))) {
                if (!is_array($except) || !in_array($kludge->getLabel(), $except)) {
                    $binary->writeChar(0x01);
                    $binary->writeString($kludge->getLabel().": ".$kludge->getValue());
                    $binary->writeChar(0x0D);
                }
            }
        }
    }
    // TODO: Make ONE method instead of two, difference is only in ':' and ' ' as a separator
    private function writeMessageControls(Message &$message, $include=false, $except=false) {
        $binary = &$this->binary;
        foreach ($message->getControls() as $kludge) {
            if ($kludge instanceof Kludge && (!is_array($include) || in_array($kludge->getLabel(), $include))) {
                if (!is_array($except) || !in_array($kludge->getLabel(), $except)) {
                    $binary->writeChar(0x01);
                    $binary->writeString($kludge->getLabel()." ".$kludge->getValue());
                    $binary->writeChar(0x0D);
                }
            }
        }
    }

    private function writeMessageFooter (Message &$message) {
        $binary = &$this->binary;
        // Tearline
        if ($message->getTearline() != null && strlen($message->getTearline())>0) {
            $binary->writeString(sprintf("--- %s",$message->getTearline()));
            $binary->writeChar(0x0D);
        }
        // Origin
        if ($message->getOrigin() != null && strlen($message->getOrigin())>0) {
            if (preg_match("/^(?:.+)(?:\()(.+)(?:\))$/", $message->getOrigin())) {
                $originLine = sprintf(" * %s", $message->getOrigin());
            } else {
                $originLine = sprintf(" * %s (%s)", $message->getOrigin(), $message->getOrigAddr()->dump());
            }
            $binary->writeString($originLine);
            $binary->writeChar(0x0D);
        }
        // Seen-by
        if ($message->getSeenby() != null && strlen($message->getSeenby())>0) {

        }
    }

    private function writeMessageBody(Message &$message) {
        $binary = &$this->binary;
        // message body
        $binary->writeString($message->getBody());
        $binary->writeChar(0x0D);
    }

    /**
     * Packet Header reference: http://ftsc.org/docs/fts-0001.016
     *
     */
    private function writePacketHeader() {
        $binary = &$this->binary;
        $packet = &$this->packet;
        $binary->writeInteger($packet->getHeader()->getNodeFrom(),16);
        $binary->writeInteger($packet->getHeader()->getNodeTo(),16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('Y'),16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('n')-1,16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('j'),16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('H'),16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('i'),16);
        $binary->writeInteger($packet->getHeader()->getDate()->format('s'),16);
        $binary->writeInteger(0,16); // baud
        $binary->writeInteger(2,16); // 00 02
        $binary->writeInteger($packet->getHeader()->getNetFrom(),16);
        $binary->writeInteger($packet->getHeader()->getNetTo(),16);
        $binary->writeInteger(0,16); // prodCode & serial 00 00
        // write password with null filled
        $passData = $this->passZeroFill($packet->getHeader()->getPassword());
        $binary->writeString($passData[0]);
        if ($passData[1]>0) {
            for ($i=0;$i<$passData[1];$i++) {
                $binary->writeChar(0x00);
            }
        }
        $binary->writeInteger($packet->getHeader()->getZoneFrom(),16);
        $binary->writeInteger($packet->getHeader()->getZoneTo(),16);
        // fill 20 bytes
        //$binary->writeString('snakemkua.ftnpacket');
        $binary->writeString('ftnp');
        $binary->writeInteger(0,64);
        $binary->writeInteger(0,64);
    }

    private function passZeroFill (string $password) {
        $maxLength = 8;
        $passLength = strlen($password);
        if ($passLength>$maxLength) {
            $password = substr($password,0,$maxLength);
            $fill = 0;
        } else {
            $fill = $maxLength - $passLength;
        }
        return [$password,$fill];
    }

    /**
     * @return bool
     */
    public function isDecode(): bool
    {
        return $this->decode;
    }

    /**
     * @param bool $decode
     */
    public function setDecode(bool $decode)
    {
        $this->decode = $decode;
    }


}