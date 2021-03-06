<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2/22/17
 * Time: 22:56
 *
 * Multi-section
 * section 1 of 2 of file 155ae004.jpg < hpt/w64-mvcdll 1.9.0-cur 24-06-16 >
 * begin 644 155ae004.jpg
 * sum -r/size 18264/415423 section (from "begin" to last encoded line)
 * section 2 of 2 of file 155ae003.jpg < hpt/w64-mvcdll 1.9.0-cur 24-06-16 >
 * end
 * sum -r/size 63458/47264 section (from first encoded line to "end")
 * sum -r/size 30771/335797 entire input file
 *
 * Single-section
 * begin 644 mydraft.doc
 * end
 */

namespace snakemkua\FTNPacket;


class Uuefile
{
    /**
     * @var int
     */
    private $part = 1;
    /**
     * @var string|null
     */
    private $file = null;
    /**
     * @var string|null
     */
    private $mode = null;
    /**
     * @var array
     */
    private $content = array();

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part)
    {
        $this->part = $part;
    }

    /**
     * @return null|string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param null|string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return null|string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param null|string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param string $line
     */
    public function addContent($line)
    {
        $this->content []= $line;
    }


}
