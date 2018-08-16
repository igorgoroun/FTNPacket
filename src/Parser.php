<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/21/17
 * Time: 15:23
 * @package FTNPacket
 */

namespace snakemkua\FTNPacket;

use Psr\Log\LoggerInterface;
use wapmorgan\BinaryStream\BinaryStream;


/**
 * Parses the unarchived fts-001 packet
 *
 * Class Parser
 * @see http://ftsc.org/docs/fts-0001.016
 * @see http://ftsc.org/docs/fts-4001.001
 * @package FTNPacket
 * @author Igor Goroun
 * @author https://snake.mk.ua
 * @author https://fido.snake.mk.ua
 * @author snake@snake.mk.ua
 * @author 2:466/4@Fidonet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright &copy; 2018
 */
class Parser
{
    /** @internal @var string */
    private $file;
    /** @internal @var bool Decode/encode parsed packet */
    private $decode = true;
    /** @internal @var null|LoggerInterface */
    private $log = null;
    /** @internal @var null|BinaryStream  */
    private $binary = null;

    /**
     * Parser constructor.
     * Takes path to file for parsing and logger.
     * @uses Psr\Log\LoggerInterface
     * @uses BinaryStream
     * @see parsePacket()
     * @param string $file Path to unarchived fts-001 packet file to parse (unpack)
     * @param LoggerInterface|null $logger Where to send log messages
     */
    function __construct(string $file, LoggerInterface $logger = null) {
        $this->log = $logger;
        $this->file = $file;
        try {
            $this->log->debug("Parsing file {$this->file}");
            $this->binary = new BinaryStream($this->file);
        } catch (\Exception $e) {
            $this->log->critical("Cannot initialize BinaryStream Bundle on file {$this->file} with exception: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Run file parser
     * @see Header
     * @see Message
     * @see Address
     * @see Kludge
     * @see Uuefile
     * @uses BinaryStream
     * @example ../tests/Controller/ParsePacketTest.php 30 1 Test example
     * @return Packet|bool
     */
    public function parsePacket () {

        if ($this->binary instanceof BinaryStream) {
            $binary = $this->binary;
            $this->log->debug("Binary stream opened");
        } else return false;

        $packet = new Packet();

        // Set binary reading groups for packet header, zoneinfo, message header
        $this->setBinaryReadingGroups($binary);
        $this->parsePacketHeader($binary, $packet);
        
        // set read in revert way
        $binary->setEndian(BinaryStream::LITTLE);
        // parse header
        $this->log->debug("Parsing packet header", [$this->file]);
        if (!$this->parsePacketHeader($binary,$packet)) {
            $this->log->critical("Cannot parse packet header", [$this->file]);
            return false;
        }
        // parse messages
        $this->log->debug("Parsing messages in packet");
        if (!$this->parsePacketMessages($binary,$packet)) {
            $this->log->critical("Cannot parse messages in packet", [$this->file]);
            return false;
        }
        // return packet if all validations passed
        return $packet;
    }

    /**
     * @internal
     * @param BinaryStream $binary
     * @param Packet $packet
     * @return bool
     */
    private function parsePacketHeader (BinaryStream &$binary, Packet &$packet) {
        // read header data
        $header_data = $binary->readGroup('Header');
        if ($header_data['unused_skip2'] != 2) {
            $this->log->critical("Invalid packet header", [$this->file]);
            return false;
        }
        // skip prodCode & serialNo
        $binary->skip(2);
        // read password
        $packet->getHeader()->setPassword(trim($binary->readString(8)));
        // read zone from/to
        $header_data = array_merge($header_data,$binary->readGroup('Zoneinfo'));
        // set packet properties
        foreach ($header_data as $code => $value) {
            $packet->getHeader()->setProperty($code,$value);
            $this->log->debug("Header property", [$code=>$value]);
        }
        // create date from parts
        $packet->getHeader()->calculateDate();
        return true;
    }

    /**
     * @internal
     * @param BinaryStream $binary
     * @param Packet $packet
     * @return bool
     */
    private function parsePacketMessages (BinaryStream &$binary, Packet &$packet) {
        // Go to messages offset
        $binary->go(58);

        // iterate over messages in packet until file end
        while (!$binary->isEnd()) {

            // new message object
            $message = new Message();

            // Parse message header
            $this->log->debug("Parsing message header");
            if (!$this->parseMessageHeader($binary, $message)) {
                $this->log->critical("Invalid message header");
                return false;
            }

            // Check is netmail or echomail message and set Area name
            $this->log->debug("Check message type");
            $this->setMessageArea($binary, $message);

            // Check top kludges
            $this->log->debug("Checking header kludges");
            $this->parseMessageKludges($binary, $message);

            // Get message body
            $this->log->debug("Parsing message body");
            try {
                $this->parseMessageBody($binary, $message);
            } catch (\Exception $e) {
                $this->log->critical("Cannot parse message body");
            }

            // Check final kludges (Via/PATH)
            $this->log->debug("Checking footer kludges");
            $this->parseMessageKludges($binary, $message);

            // Set orig/dest addresses from message data
            $this->log->info("Parse addresses from kludges");
            $this->setAddressesFromKludges($message);

            // Decode message from it's codepage to my local utf-8
            if ($this->isDecode()) {
                (new Encoder($message))->decode();
            }

            // add message to packet
            $packet->addMessage($message);

            // skip null terminated
            $binary->skip(1);

            // Check is end of message(s)
            if ($binary->compare(1,[0x00])) {
                break;
            }
        }
        return true;
    }

    /**
     * @internal
     * @param BinaryStream $binary
     * @param Message $message
     * @return bool
     */
    private function parseMessageHeader (BinaryStream &$binary, Message &$message)
    {
        // read message packed header
        $message_data = $binary->readGroup('MessageHeader');
        if ($message_data['02_start'] != 2) {
            return false;
        }
        unset($message_data['02_start']);

        // read message date/time and format it to \DateTime
        $message->setDate(trim($binary->readString(20)));

        // Calculate nameTo length and read it
        $nameToLength = $this->lengthUntilNullTerminated($binary, 36);
        $message_data['destName'] = trim($binary->readString($nameToLength));

        // skip null terminated
        $binary->skip(1);

        // Calculate nameFrom length and read it
        $fromNameLength = $this->lengthUntilNullTerminated($binary, 36);
        $message_data['origName'] = trim($binary->readString($fromNameLength));

        // skip null terminated
        $binary->skip(1);

        // Calculate subject length and read it
        $subjectLength = $this->lengthUntilNullTerminated($binary, 72);
        //$message_data['subject'] = iconv('cp866','utf-8',trim($binary->readString($subjectLength)));
        $message_data['subject'] = trim($binary->readString($subjectLength));

        // set message properties
        foreach ($message_data as $code => $value) {
            try {
                $message->setProperty($code, $value);
            } catch (\Exception $e) {
                $this->log->warning("Property setting error: " . $e->getMessage());
            }
        }

        // skip null terminated
        $binary->skip(1);

        return true;
    }

    private function setMessageArea (BinaryStream &$binary, Message &$message) {
        $message->setEchomail(false);
        if (!$binary->compare(1,[0x01])) {
            $echoLineLength = $this->lengthUntilNullTerminated($binary, 80, 0x0D);
            $echoLine = trim($binary->readString($echoLineLength));
            if (preg_match("/^AREA\:(.+)$/",$echoLine,$data)) {
                $message->setEchomail(true);
                $message->setArea($data[1]);
            }
            $binary->skip(1);
        }
    }

    private function parseMessageKludges (BinaryStream &$binary, Message &$message) {
        $kludgeFlag = $binary->compare(1,[0x01]);
        while ($kludgeFlag) {
            $binary->skip(1);
            $kludge = new Kludge();
            $lineLength = $this->lengthUntilNullTerminated($binary, 80, 0x0D);
            $line = trim($binary->readString($lineLength));
            if (preg_match("/^(\w+)(\:\ )(.+)$/",$line,$data)) {
                $kludge->setLabel(trim($data[1]));
                $kludge->setValue($data[3]);
                $message->addKludge($kludge);
            } elseif (preg_match("/^(\w+)(\ )(.+)$/",$line,$data)) {
                $kludge->setLabel(trim($data[1]));
                $kludge->setValue($data[3]);
                $message->addControl($kludge);
            }
            unset($kludge);
            $binary->skip(1);
            $kludgeFlag = $binary->compare(1,[0x01]);
        }
    }

    private function parseMessageBody (BinaryStream &$binary, Message &$message) {
        $kludgeFlag = $binary->compare(1,[0x01]);
        //$kludgeFlag = false;
        $bodyLines = [];
        $seenByLines = [];
        $onUue = false;
        while (!$kludgeFlag) {
            $lineLength = $this->lengthUntilNullTerminated($binary, false, 0x0D);
            // zero-length line
            if ($lineLength == 0 && !$onUue) {
                $bodyLines [] = "";
                $binary->skip(1);
                continue;
            } elseif ($lineLength == 0 && $onUue) {
                // close Uuefile
                $onUue = false;
                $binary->skip(1);
                continue;
            }

            // common: read line
            $line = $binary->readString($lineLength);

            // OnUUE parsing
            if ($onUue) {
                if ('end' == trim($line)) {
                    // close UUefile
                    $onUue = false;
                } else {
                    $message->lastUuefile()->addContent(trim($line));
                }
            }
            // Tearline
            if (preg_match("/^(---\ )(.+)$/", trim($line), $data)) {
                $message->setTearline($data[2]);
                // Origin
            } elseif (preg_match("/^(\*\ Origin\:\ )(.+)$/", trim($line), $data)) {
                $message->setOrigin($data[2]);
                // Seen-by
            } elseif (preg_match("/^(SEEN-BY\:\ )(.+)$/", trim($line), $data)) {
                $seenByLines [] = $data[2];
                // UUencoded file start
            } elseif (preg_match("/^begin\ (\d{3,4})\ (.+)$/", trim($line), $data)) {
                $onUue = true;
                $uue = new Uuefile();
                $uue->setFile($data[2]);
                $uue->setMode($data[1]);
                $message->addUuefile($uue);
                //$bodyLines [] = "[UUE:{$uue->getFile()}]";
                $bodyLines [] = rtrim($line);
                // Text
            } else {
                $bodyLines [] = rtrim($line);
            }

            $binary->skip(1);
            $kludgeFlag = $binary->compare(1,[0x01]);
        }
        $message->setBody(implode("\n",$bodyLines));
        if (count($seenByLines)>0) {
            $message->setSeenby(implode(" ", $seenByLines));
        }
    }


    private function setAddressesFromKludges (Message &$message) {
        // Check important kludges: MSGID, REPLYTO, INTL, FMPT, TOPT
        $msgid = $message->hasKludge('MSGID');

        // set msgID
        if ($msgid instanceof Kludge) {
            $message->setOrigMsgID((explode(' ', $msgid->getValue()))[1]);
        }

        // Check sender address in origin by regex pattern
        if (preg_match("/^(?:.+)(?:\()(.+)(?:\))$/", $message->getOrigin(), $data)) {
            // try to get address from parsed origin
            try {
                $origAddr = new Address($data[1]);
                $message->setOrigAddr($origAddr);
            } catch (\InvalidArgumentException $e) {
                // TODO: Add debug message
            }
        }

        // Parse addresses for NETMAIL message
        if ($message->isNetmail()) {
            $intl = $message->hasControl('INTL');
            // Secondary tries to get origAddr
            if ($message->getOrigAddr() == null) {
                // try to get from INTL & FMPT
                if ($intl instanceof Kludge) {
                    try {
                        $origAddr = new Address((explode(' ', $intl->getValue()))[1]);
                        $fmpt = $message->hasControl('FMPT');
                        if ($fmpt instanceof Kludge) {
                            $origAddr->setPoint($fmpt->getValue());
                        }
                        $message->setOrigAddr($origAddr);
                    } catch (\InvalidArgumentException $e) {
                        // TODO: Add debug message
                    }
                }
            }
            // Try to get recipient address from INTL+TOPT
            if ($intl instanceof Kludge) {
                try {
                    $destAddr = new Address((explode(' ', $intl->getValue()))[0]);
                    $topt = $message->hasControl('TOPT');
                    if ($topt instanceof Kludge) {
                        $destAddr->setPoint($topt->getValue());
                    }
                    $message->setDestAddr($destAddr);
                } catch (\InvalidArgumentException $e) {
                    // TODO: Add debug message
                }
            }
        }

        // Parse addresses for ECHOMAIL message
        elseif ($message->isEchomail()) {
            // Secondary tries to get sender address
            if ($message->getOrigAddr() == null) {
                $replyto = $message->hasKludge('REPLYTO');
                // try to get sender address from REPLYTO kludge
                if ($replyto instanceof Kludge) {
                    try {
                        $origAddr = new Address((explode(' ', $replyto->getValue()))[0]);
                        $message->setOrigAddr($origAddr);
                    } catch (\InvalidArgumentException $e) {
                        // TODO: add debug message
                    }
                }
            }
            // Try to get recipient address from REPLY
            $rply = $message->hasKludge('REPLY');
            if ($rply instanceof Kludge) {
                try {
                    $destAddr = new Address((explode(' ', $rply->getValue()))[0]);
                    $message->setDestAddr($destAddr);
                } catch (\InvalidArgumentException $e) {
                    // TODO: add debug message
                }
            }
        }

        // If unknown type or bad message
        // TODO: What to do with unknown message types?
        else {
            unset($message);
        }

        // try to get sender address
        // from MSGID both for netmail and echomail
        // if it is null after previous checks
        if (isset($message) && $message->getOrigAddr() == null && $msgid instanceof Kludge) {
            try {
                $origAddr = new Address((explode(' ', $msgid->getValue()))[0]);
                $message->setOrigAddr($origAddr);
            } catch (\InvalidArgumentException $e) {
                // TODO: Add debug message
            }
        }

    }

    private function lengthUntilNullTerminated(BinaryStream &$binary, $maxLength=false, $delim=0x00) {
        $binary->mark('offsetStart');
        $end = false;
        $length = 0;
        while (!$end) {
            $end = $binary->compare(1, [$delim]);
            $binary->skip(1);
            if (!$end) $length++;
            if ($maxLength && $length>=$maxLength) break;
        }
        $binary->go('offsetStart');
        return $length;
    }

    private function setBinaryReadingGroups(BinaryStream &$binary) {
        // Packet header
        $binary->saveGroup('Header',[
            'i:nodeFrom' => 16,
            'i:nodeTo' => 16,
            'i:dateYear' => 16,
            'i:dateMonth' => 16,
            'i:dateDay' => 16,
            'i:dateHour' => 16,
            'i:dateMinute' => 16,
            'i:dateSecond' => 16,
            'i:unused_baud' => 16,
            'i:unused_skip2' => 16,
            'i:netFrom' => 16,
            'i:netTo' => 16,
        ]);
        // Packet zone info
        $binary->saveGroup('Zoneinfo',[
            'i:zoneFrom' => 16,
            'i:zoneTo' => 16,
        ]);
        // Message header
        $binary->saveGroup('MessageHeader',[
            'i:02_start' => 16,
            'i:nodeFrom' => 16,
            'i:nodeTo' => 16,
            'i:netFrom' => 16,
            'i:netTo' => 16,
            'i:attribute' => 16,
            'i:cost' => 16,
        ]);

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