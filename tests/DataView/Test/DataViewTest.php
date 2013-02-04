<?php
namespace DataView\Test;

use DataView\DataView;

/**
 * @author Martin Parsiegla <parsiegla@kuponjo.de>
 */
class DataViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataView
     */
    protected $dataView;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    protected function setUp()
    {
        $this->adapter = $this->getMock('DataView\Adapter\AdapterInterface');
        $this->dataView = new DataView($this->adapter);
    }

    /**
     * @covers DataView\DataView::setSource
     */
    public function testSetSource()
    {
        $source = 'Entity\Source';
        $this->adapter->expects($this->once())->method('setSource')->with($this->equalTo($source));

        $this->dataView->setSource($source);
    }

    /**
     * @covers DataView\DataView::getPager
     */
    public function testGetPager()
    {
        $this->adapter->expects($this->once())->method('getSource')->will($this->returnValue('source'));
        $this->adapter->expects($this->once())->method('setFilters')->with($this->equalTo(array()));
        $this->adapter->expects($this->once())->method('setColumns')->with($this->equalTo(array()));
        $this->adapter->expects($this->once())->method('setFilters')->with($this->equalTo(array()));
        $this->adapter->expects($this->once())->method('getPager');

        $this->dataView->getPager();
    }
}
