<?php

namespace IgorGoroun\FTNPacketBundle\Tests\Controller;

use IgorGoroun\FTNPacketBundle\Entity\Address;
use IgorGoroun\FTNPacketBundle\Entity\Packet;
use IgorGoroun\FTNPacketBundle\Entity\Parser;
use IgorGoroun\FTNPacketBundle\Entity\Uuefile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use IgorGoroun\FTNPacketBundle\Entity\Message;


class ParsePacketTest extends WebTestCase
{

    public function testPacketHeader()
    {
        //$client = static::createClient();
        //$crawler = $client->request('GET', '/');
        //$this->assertContains('Hello World', $client->getResponse()->getContent());
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Echomail')->name('57B435B7.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();
            $this->assertNotEmpty($packet->getHeader()->getDate());
            $this->assertNotEmpty($packet->getHeader()->getNetFrom());
            $this->assertNotEmpty($packet->getHeader()->getNetTo());
            $this->assertNotEmpty($packet->getHeader()->getNodeFrom());
            $this->assertNotEmpty($packet->getHeader()->getNodeTo());
            $this->assertNotEmpty($packet->getHeader()->getZoneFrom());
            $this->assertNotEmpty($packet->getHeader()->getZoneTo());
            $this->assertNotEmpty($packet->getHeader()->getPassword());
            $this->assertContainsOnlyInstancesOf(Message::class,$packet->getMessages());
        }
    }

    public function testPacketEchomailMessages()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Echomail')->name('57B44611.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            //$normalizer = new ObjectNormalizer();
            //$normalizer->setIgnoredAttributes(array('bodySource'));
            //$serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertTrue($message->isEchomail());
                    $this->assertNotEmpty($message->getArea());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());

                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                }
            }
        }
    }

    public function testPacketEchomailUUE()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Echomail')->name('58c30f20.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            $normalizer = new ObjectNormalizer();
            $normalizer->setIgnoredAttributes(array('bodySource'));
            $serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertTrue($message->isEchomail());
                    $this->assertNotEmpty($message->getArea());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());
                    $this->assertContainsOnlyInstancesOf(Uuefile::class,$message->getUuefiles());
                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                    print_r($serializer->serialize($message->getUuefiles(),'yaml'));
                }
            }
        }
    }
    public function testNetmailMessageOtherZone()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B08F69.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            //$normalizer = new ObjectNormalizer();
            //$normalizer->setIgnoredAttributes(array('bodySource'));
            //$serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());
                    $this->assertInstanceOf(Address::class,$message->getDestAddr());

                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                }
            }
        }
    }

    public function testNetmailMessageFromNodeAddr()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B09980.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            //$normalizer = new ObjectNormalizer();
            //$normalizer->setIgnoredAttributes(array('bodySource'));
            //$serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());
                    $this->assertInstanceOf(Address::class,$message->getDestAddr());
                    $this->assertNull($message->getOrigAddr()->getPoint());

                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                }
            }
        }
    }

    public function testNetmailMessageFromOtherRegionAndEcho()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B099BD.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            //$normalizer = new ObjectNormalizer();
            //$normalizer->setIgnoredAttributes(array('bodySource'));
            //$serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());
                    $this->assertInstanceOf(Address::class,$message->getDestAddr());

                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                }
            }
        }
    }

    public function testNetmailMessageFromOtherRegion2()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B09D50.PKT');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();

            // for debug - normalizer and serializer
            //$normalizer = new ObjectNormalizer();
            //$normalizer->setIgnoredAttributes(array('bodySource'));
            //$serializer = new Serializer([$normalizer],[new JsonEncoder(), new YamlEncoder()]);

            foreach ($packet->getMessages() as $message) {
                $this->assertInstanceOf(Message::class,$message);
                if ($message instanceof Message) {
                    $this->assertNotEmpty($message->getDate());
                    $this->assertNotEmpty($message->getNodeFrom());
                    $this->assertNotEmpty($message->getNodeTo());
                    $this->assertNotEmpty($message->getNetFrom());
                    $this->assertNotEmpty($message->getNetTo());
                    $this->assertNotEmpty($message->getOrigName());
                    $this->assertNotEmpty($message->getDestName());
                    $this->assertNotEmpty($message->getSubject());
                    $this->assertInstanceOf(Address::class,$message->getOrigAddr());
                    $this->assertInstanceOf(Address::class,$message->getDestAddr());

                    // for debug - print json
                    //print_r($serializer->serialize($message,'yaml'));
                }
            }
        }
    }

    public function testOtherFile()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Other')->name('gedcolor.cfg');
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();
            $this->assertNotInstanceOf(Packet::class, $packet);
            $this->assertFalse($packet);
        }
    }
}
