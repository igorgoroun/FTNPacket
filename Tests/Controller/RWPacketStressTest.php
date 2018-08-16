<?php

namespace snakemkua\FTNPacket\Tests\Controller;

use snakemkua\FTNPacket\Address;
use snakemkua\FTNPacket\Packet;
use snakemkua\FTNPacket\Parser;
use snakemkua\FTNPacket\Message;
use snakemkua\FTNPacket\Writer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class RWPacketStressTest extends WebTestCase
{

    public function testStressNetmail()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../Resources/Netmail')->name('58B08F69.PKT');
        $fs = new Filesystem();
        $tmpFile = __DIR__.'/../Resources/Created/current_netmails_%s.tmp';
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
        $finder->files()->in(__DIR__.'/../Resources/Echomail')->name('57B44611.PKT');
        $fs = new Filesystem();
        $tmpFile = __DIR__.'/../Resources/Created/current_echo_%s.tmp';
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
