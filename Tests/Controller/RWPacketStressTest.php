<?php

namespace IgorGoroun\FTNPacket\Tests\Controller;

use IgorGoroun\FTNPacket\Address;
use IgorGoroun\FTNPacket\Packet;
use IgorGoroun\FTNPacket\Parser;
use IgorGoroun\FTNPacket\Message;
use IgorGoroun\FTNPacket\Writer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class RWPacketStressTest extends WebTestCase
{

    public function testStressNetmail()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/ftnpacket/Tests/Resources/Netmail')->name('58B08F69.PKT');
        $fs = new Filesystem();
        $tmpFile = 'src/IgorGoroun/ftnpacket/Tests/Resources/Created/current_netmails_%s.tmp';
        foreach ($finder as $file) {
            for ($j=0;$j<10;$j++) {
                $packet = (new Parser($file->getPathname()))->parsePacket();
                for ($i=0;$i<100;$i++) {
                    $wfile = sprintf($tmpFile, $i+$j*100);
                    $fs->touch($wfile);
                    $writer = new Writer($packet, $wfile);
                    try {
                        $writer->writePacket();
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
    }

    public function testStressPacketRW()
    {
        $finder = new Finder();
        $finder->files()->in('src/IgorGoroun/ftnpacket/Tests/Resources/Echomail')->name('57B44611.PKT');
        $fs = new Filesystem();
        $tmpFile = 'src/IgorGoroun/ftnpacket/Tests/Resources/Created/current_echo_%s.tmp';
        foreach ($finder as $file) {
            for ($j=0;$j<10;$j++) {
                $packet = (new Parser($file->getPathname()))->parsePacket();
                for ($i=0;$i<100;$i++) {
                    $wfile = sprintf($tmpFile, $i+$j*100);
                    $fs->touch($wfile);
                    $writer = new Writer($packet, $wfile);
                    try {
                        $writer->writePacket();
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
    }

}
