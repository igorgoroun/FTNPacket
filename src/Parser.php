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
    }}