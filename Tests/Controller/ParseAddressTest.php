<?php

namespace snakemkua\FTNPacket\Tests\Controller;

use snakemkua\FTNPacket\Address;
use snakemkua\FTNPacket\Packet;
use snakemkua\FTNPacket\Parser;
use snakemkua\FTNPacket\Message;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class ParseAddressTest extends WebTestCase
{

    public function testAddressForPoint() {
        try {
            $address = new Address('2:5020/1024.123');
            $this->assertInstanceOf(Address::class, $address);
            $this->assertEquals(1024,$address->getNode());
            $this->assertEquals(123,$address->getPoint());
        } catch (\InvalidArgumentException $e) {
        }
        $address = new Address('2:5020/0');
        print_r($address);
    }
    public function testAddressForDomain() {
        try {
            $address = new Address('2:5020/1024.123@fidonet.org');
            $this->assertInstanceOf(Address::class, $address);
            $this->assertEquals(1024,$address->getNode());
            $this->assertEquals(123,$address->getPoint());
            $this->assertEquals('fidonet.org',$address->getDomain());
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function testAddressForRegion() {
        try {
            $address = new Address('2:50/15');
            $this->assertInstanceOf(Address::class, $address);
        } catch (\InvalidArgumentException $e) {
            $this->assertNull($address);
        }
    }

    public function testAddressdump() {
        try {
            $address = new Address('2:5020/1024.123@fidonet.org');
            $this->assertInstanceOf(Address::class, $address);
            $this->assertEquals('2:5020/1024.123',$address->dump());
            $this->assertEquals('2:5020/1024.123@fidonet.org',$address->dumpFull());
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function testAddressNegative() {
        $address = null;
        try {
            $address = new Address('2.50/15');
        } catch (\Exception $e) {
            $this->assertNull($address);
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

}
