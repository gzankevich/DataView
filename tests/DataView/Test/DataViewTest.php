<?php

namespace DataView\Test;

use DataView\DataView;

/**
 * @author Martin Parsiegla <parsiegla@kuponjo.de>
 * @author George Zankevich <gzankevich@gmail.com>
 */
class DataViewTest extends BaseUnitTest
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
        $this->adapter = $this->getMockForAbstractClass('\DataView\Adapter\BaseAdapter');
        $this->dataView = new DataView($this->adapter);
    }

    /**
     * @covers DataView\DataView::setSource
     */
    public function testSetSource()
    {
        $source = 'Entity\Source';

        $adapter = $this->getMockForAbstractClass('\DataView\Adapter\BaseAdapter', array(), '', false, true, true, array('setSource'));
        $adapter->expects($this->once())->method('setSource')->with($this->equalTo($source));

        $dataView = new DataView($adapter);

        $dataView->setSource($source);
    }

    /**
     * @covers DataView\DataView::getPager
     */
    public function testGetPager_noColumns()
    {
        $pager = $this->getMockBuilder('\Pagerfanta\Pagerfanta')->disableOriginalConstructor()->getMock();

        $this->setExpectedException('\DataView\NoColumnsAddedException');
        $this->dataView->getPager();
    }

    /**
     * @covers DataView\DataView::getPager
     */
    public function testGetPager_valid()
    {
        $column = $this->getMockBuilder('\DataView\Column')->disableOriginalConstructor()->getMock();

        $pager = $this->getMockBuilder('\Pagerfanta\Pagerfanta')->disableOriginalConstructor()->getMock();

        $adapter = $this->getMockForAbstractClass('\DataView\Adapter\BaseAdapter', array(), '', false, true, true, array('setFilters', 'setColumns', 'getPager'));
        $adapter->expects($this->once())->method('setFilters')->with($this->equalTo(array()));
        $adapter->expects($this->once())->method('setColumns')->with($this->equalTo(array($column)));
        $adapter->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $dataView = new DataView($adapter);
        $dataView->addColumn($column);
        $dataView->getPager();
    }

    /**
     * @covers DataView\DataView::getEntityValueByPropertyPath
     */
    public function testGetEntityValueByPropertyPath_noEntity()
    {
        $this->assertEquals(null, $this->dataView->getEntityValueByPropertyPath(null, 'foo'));
    }

    /**
     * @covers DataView\DataView::getEntityValueByPropertyPath
     */
    public function testGetEntityValueByPropertyPath_noPropertyPath()
    {
        $this->assertEquals(null, $this->dataView->getEntityValueByPropertyPath('foo', null));
    }

    /**
     * @covers DataView\DataView::getEntityValueByPropertyPath
     */
    public function testGetEntityValueByPropertyPath_simpleProperty()
    {
        $entity = $this->getMock('stdClass', array('getFoo'));
        $entity->expects($this->once())->method('getFoo')->will($this->returnValue('bar'));

        $this->assertEquals('bar', $this->dataView->getEntityValueByPropertyPath($entity, 'foo'));
    }

    /**
     * @covers DataView\DataView::getEntityValueByPropertyPath
     */
    public function testGetEntityValueByPropertyPath_associationOneLevel()
    {
        $associatedEntity = $this->getMock('stdClass', array('getFoo'));
        $associatedEntity->expects($this->once())->method('getFoo')->will($this->returnValue('bar'));

        $entity = $this->getMock('stdClass', array('getAssociatedEntity'));
        $entity->expects($this->once())->method('getAssociatedEntity')->will($this->returnValue($associatedEntity));

        $this->assertEquals('bar', $this->dataView->getEntityValueByPropertyPath($entity, 'associatedEntity.foo'));
    }

    /**
     * @covers DataView\DataView::getEntityValueByPropertyPath
     */
    public function testGetEntityValueByPropertyPath_associationTwoLevels()
    {
        $associatedEntity2 = $this->getMock('stdClass', array('getFoo'));
        $associatedEntity2->expects($this->once())->method('getFoo')->will($this->returnValue('bar'));

        $associatedEntity = $this->getMock('stdClass', array('getAssociatedEntity2'));
        $associatedEntity->expects($this->once())->method('getAssociatedEntity2')->will($this->returnValue($associatedEntity2));

        $entity = $this->getMock('stdClass', array('getAssociatedEntity'));
        $entity->expects($this->once())->method('getAssociatedEntity')->will($this->returnValue($associatedEntity));

        $this->assertEquals('bar', $this->dataView->getEntityValueByPropertyPath($entity, 'associatedEntity.associatedEntity2.foo'));
    }
}
