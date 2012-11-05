<?php
namespace DataView\Test\Adapter;

use DataView\Filter;
use DataView\Adapter\DoctrineORM;

class TestDoctrineORMGetQuery extends DoctrineORM
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

class TestDoctrineORMGetAliasFromQueryBuilder extends DoctrineORM
{
	public function getAliasFromQueryBuilder($queryBuilder)
    {
        return parent::getAliasFromQueryBuilder($queryBuilder);
    }
}

class TestDoctrineORMJoinRelations extends DoctrineORM
{
	public function joinRelations($propertyPath, $queryBuilder)
    {
        return parent::joinRelations($propertyPath, $queryBuilder);
    }
}

class TestDoctrineORMApplyFilters extends DoctrineORM
{
	public function applyFilters($queryBuilder)
    {
        return parent::applyFilters($queryBuilder);
    }

	protected function getAliasFromQueryBuilder($queryBuilder)
    {
        return 'x';
    }
}

class DoctrineORMTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers DataView\Adapter\DoctrineORM::joinRelations
     */
    public function testJoinRelations()
    {
        // mock query builder - expect two calls to join()
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder
            ->expects($this->once())
            ->method('join')
            ->with($this->equalTo('foo.bar'));

        $doctrineORM = new TestDoctrineORMJoinRelations(null, null, null);
        $doctrineORM->joinRelations('foo.bar.baz', $queryBuilder);
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


        $doctrineORM = new TestDoctrineORMGetAliasFromQueryBuilder(null);
        $alias = $doctrineORM->getAliasFromQueryBuilder($queryBuilder);

        $this->assertEquals('foo', $alias);
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::applyFilters
     */
    public function testApplyFilters()
    {
        // mock query builder - expect two calls to join()
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with($this->equalTo('foo.bar = :param_0'));
        $queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with($this->equalTo('param_0'), $this->equalTo(123));

        $doctrineORM = new TestDoctrineORMApplyFilters(null, null, null);
        $doctrineORM->setFilters(array(new Filter('foo.bar', Filter::COMPARISON_TYPE_EQUAL, 123)));
        $doctrineORM->applyFilters($queryBuilder);
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::getQuery
     *
     * No source provided.
     */
    public function testGetQuery_noSource()
    {
        $this->setExpectedException('DataView\SourceNotSetException');
        $doctrineORM = new TestDoctrineORMGetQuery(null);
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

        $doctrineORM = new TestDoctrineORMGetQuery($entityManager);
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

        $doctrineORM = new TestDoctrineORMGetQuery(null);
        $doctrineORM->setSource($queryBuilder);
        $query = $doctrineORM->getQuery();

        $this->assertEquals($queryBuilder, $query);
    }
}
