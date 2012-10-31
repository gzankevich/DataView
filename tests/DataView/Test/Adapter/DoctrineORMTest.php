<?php
namespace DataView\Test\Adapter;

use DataView\Adapter\DoctrineORM;

class DoctrineORMGetQueryTest extends DoctrineORM
{
    protected function applyFilters($queryBuilder)
    {
        return $queryBuilder;
    }
}

class DoctrineORMTest extends \PHPUnit_Framework_TestCase
{
    public function testJoinRelations()
    {
    }

    public function testGetAliasFromQueryBuilder()
    {

    }

    public function testApplyFilters()
    {

    }

    public function testGetQuery_noSource()
    {
        $this->setExpectedException('DataView\SourceNotSetException');
        $doctrineORM = new DoctrineORMGetQueryTest(null);
        $doctrineORM->getQuery();

    }

    public function testGetQuery_valid()
    {
//        $repository = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->getMock();
//        $repository
//            ->expects($this->once())
//            ->method('createQueryBuilder')
//            ->will($this->returnValue('test'));
//
//        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->getMock();
//        $entityManager
//            ->expects($this->once())
//            ->method('getRepository')
//            ->will($this->returnValue($repository));
//
//        $entityManager->getRepository('test');
//        die();
//
//        $doctrineORM = new DoctrineORMGetQueryTest($entityManager);
//        $doctrineORM->setSource('AcmeDemoBundle:User');
//        $doctrineORM->getQuery();
    }
}
