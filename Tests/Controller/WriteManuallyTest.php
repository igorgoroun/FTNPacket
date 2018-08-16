<?php

namespace snakemkua\FTNPacket\Tests\Controller;

use snakemkua\FTNPacket\Address;
use snakemkua\FTNPacket\Kludge;
use snakemkua\FTNPacket\Packet;
use snakemkua\FTNPacket\Parser;
use snakemkua\FTNPacket\Message;
use snakemkua\FTNPacket\Writer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class WriteManuallyTest extends WebTestCase
{

    public function testCreateNetmail()
    {
        // for debug - normalizer and serializer
        $normalizer = new ObjectNormalizer();
        $normalizer->setIgnoredAttributes(array('bodySource'));
        $serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

        // create packet
        $packet = new Packet();

        // Fill packet header
        $packet->setDate(new \DateTime());
        $packet->setFrom(new Address('2:466/41'));
        $packet->setTo(new Address('2:466/4'));
        $packet->setPassword('ghbvfhf12');
        //print_r($serializer->serialize($packet->getHeader(),'yaml'));

        if ($message = $this->netmailMessageCreate()) {
            // Add message to packet
            $packet->addMessage($message);
        }

        //print_r($serializer->serialize($packet,'yaml'));

        // Check packet validation in writer when packet is empty
        try {
            // file to save packet
            $writeFile = '/Users/snake/Projects/ftn-components/src/IgorGoroun/ftnpacket/tests/Resources/Created/manual_netmail.tmp';
            $writer = new Writer($packet, $writeFile);
            $writer->writePacket();
        } catch (\Exception $e) {
            $this->assertEquals('Packet is not valid', $e->getMessage());
        }

    }


    private function netmailMessageCreate() {
        // Create message
        $message = new Message();
        $message->setDate(new \DateTime());
        $message->setOrigAddr(new Address('2:466/41.45'));
        $message->setDestAddr(new Address('2:466/4.1'));
        $message->setOrigName('Snake');
        $message->setDestName('Igor Goroun');
        $message->setSubject('Test manually generating message');
        $message->setBody("Hi Rec!\n\nHow are you?\n\n");
        $message->setOrigin('Snakes Lair');
        $message->setTearline('It\'s a kind of fun to do the impossible');

        // set message control kludges
        if ($message->prepare()) {
            $message->addControl(new Kludge('Via', 'ftnode-1.0.1'));
            return $message;
        } else {
            return false;
        }

    }



}
