<?php
namespace DataView\Test\Adapter;

use DataView\Adapter\DoctrineORM;

class DoctrineORMGetQueryTest extends DoctrineORM
{
	public function getQuery()
    {
        return parent::getQuery();
    }
    
    protected function applyFilters($queryBuilder)
    {
        return $queryBuilder;
    }
}

class DoctrineORMGetAliasFromQueryBuilderTest extends DoctrineORM
{
	public function getAliasFromQueryBuilder($queryBuilder)
    {
        return parent::getAliasFromQueryBuilder($queryBuilder);
    }
}

class DoctrineORMTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers DataView\Adapter\DoctrineORM::joinRelations
     */
    public function testJoinRelations()
    {
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::getAliasFromQueryBuilder
     */
    public function testGetAliasFromQueryBuilder()
    {
        $dqlSelectParts = array();
        $dqlSelectParts[0] = $this->getMockBuilder('Doctrine\ORM\Query\Expr\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $dqlSelectParts[0]
            ->expects($this->once())
            ->method('getParts')
            ->will($this->returnValue(array('foo')));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder
            ->expects($this->once())
            ->method('getDqlPart')
            ->with($this->equalTo('select'))
            ->will($this->returnValue($dqlSelectParts));


        $doctrineORM = new DoctrineORMGetAliasFromQueryBuilderTest(null);
        $alias = $doctrineORM->getAliasFromQueryBuilder($queryBuilder);

        $this->assertEquals('foo', $alias);
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::applyFilters
     */
    public function testApplyFilters()
    {

    }

    /**
     * @covers DataView\Adapter\DoctrineORM::getQuery
     *
     * No source provided.
     */
    public function testGetQuery_noSource()
    {
        $this->setExpectedException('DataView\SourceNotSetException');
        $doctrineORM = new DoctrineORMGetQueryTest(null);
        $doctrineORM->getQuery();
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::getQuery
     *
     * Source is a table name.
     */
    public function testGetQuery_tableName()
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
			->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue('test'));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
			->disableOriginalConstructor()
            ->getMock();
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $doctrineORM = new DoctrineORMGetQueryTest($entityManager);
        $doctrineORM->setSource('AcmeDemoBundle:User');
        $query = $doctrineORM->getQuery();

        $this->assertEquals('test', $query);
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::getQuery
     *
     * Source is a QueryBuilder instance.
     */
    public function testGetQuery_queryBuilder()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineORM = new DoctrineORMGetQueryTest(null);
        $doctrineORM->setSource($queryBuilder);
        $query = $doctrineORM->getQuery();

        $this->assertEquals($queryBuilder, $query);
    }
}
