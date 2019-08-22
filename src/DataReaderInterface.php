<?php

namespace Horoshop;

/**
 * Class DataReader
 * @package Horoshop
 */
interface DataReaderInterface
{
    /**
     * Read data from file and set $info property
     * @param string $fileName
     */
    public function readFile( string $fileName );
}