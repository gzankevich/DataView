<?php

namespace DataView\Adapter;

use Pagerfanta\Adapter\ArrayAdapter as ArrayAdapter;
use Pagerfanta\Pagerfanta;
use DataView\Adapter\SourceNotSetException;
use DataView\Adapter\InvalidSourceException;

/**
 * Array adapter
 */
class ArrayAdapter implements BaseAdapter
{
    public abstract function getPager()
    {
        return new Pagerfanta(new PagerfantaArrayAdapter($this->getSource()));
    }
}
