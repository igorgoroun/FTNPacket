<?php

namespace IgorGoroun\FTNPacketBundle\Tests\Controller;

use IgorGoroun\FTNPacketBundle\Entity\Address;
use IgorGoroun\FTNPacketBundle\Entity\Packet;
use IgorGoroun\FTNPacketBundle\Entity\Parser;
use IgorGoroun\FTNPacketBundle\Entity\Message;
use IgorGoroun\FTNPacketBundle\Entity\Writer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class WritePacketTest extends WebTestCase
{

    public function testWriteNetmail()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B08F69.PKT');
        $fs = new Filesystem();
        $tmpFile = 'src/IgorGoroun/FTNPacketBundle/Tests/Resources/Created/current_netmails_%s.tmp';
        $i = 0;
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();
            $file = sprintf($tmpFile,$i);
            $fs->touch($file);
            $writer = new Writer($packet, $file);
            try {
                $writer->writePacket();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $i++;
        }
    }

    public function testWriteCollected()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/FTNPacketBundle/Tests/Resources/Netmail')->name('58B099BD.PKT');
        $fs = new Filesystem();
        $tmpFile = 'src/IgorGoroun/FTNPacketBundle/Tests/Resources/Created/current_collect_%s.tmp';
        $i = 0;
        foreach ($finder as $file) {
            $packet = (new Parser($file->getPathname()))->parsePacket();
            $file = sprintf($tmpFile,$i);
            $fs->touch($file);
            $writer = new Writer($packet, $file);
            try {
                $writer->writePacket();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $i++;
        }
    }
    /*
    public function testCompilePacket() {
        if (count($source->getMessages())>0) {
            foreach ($source->getMessages() as $message) {
                if ($message instanceof Message) {

                }
            }
        }
    }
    */

}
