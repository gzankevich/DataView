<?php

namespace DataView\Adapter;

use Pagerfanta\Adapter\ArrayAdapter as PagerfantaArrayAdapter;
use Pagerfanta\Pagerfanta;
use DataView\Adapter\SourceNotSetException;
use DataView\Adapter\InvalidSourceException;

/**
 * Array adapter
 */
class ArrayAdapter extends BaseAdapter
{
    public function createPager()
    {
        return new Pagerfanta(new PagerfantaArrayAdapter($this->getSource()));
    }
}
