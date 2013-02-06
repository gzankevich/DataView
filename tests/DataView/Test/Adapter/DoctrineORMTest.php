<?php
namespace DataView\Test\Adapter;

use DataView\Column;
use DataView\Filter;
use DataView\DataView;
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

    protected function getAliasFromQueryBuilder($queryBuilder)
    {
        return 'x';
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

	protected function getAliasFromQueryBuilder($queryBuilder)
    {
        return 'foo';
    }
}

class TestDoctrineORMApplyFilters extends DoctrineORM
{
	public function applyFilters($queryBuilder, $alias)
    {
        return parent::applyFilters($queryBuilder, $alias);
    }

	protected function getAliasFromQueryBuilder($queryBuilder)
    {
        return 'x';
    }
}

class TestDoctrineORMApplyOrderBy extends DoctrineORM
{
	public function applyOrderBy($queryBuilder, $alias)
    {
        return parent::applyOrderBy($queryBuilder, $alias);
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
            ->method('leftJoin')
            ->with($this->equalTo('foo.test'));

        $doctrineORM = new TestDoctrineORMJoinRelations(null, null, null);
        $doctrineORM->joinRelations('test.bar', $queryBuilder);
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
        $doctrineORM->applyFilters($queryBuilder, 'x');
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
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue('test'));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
			->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

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
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue('test'));

        $doctrineORM = new TestDoctrineORMGetQuery(null);
        $doctrineORM->setSource($queryBuilder);
        $query = $doctrineORM->getQuery();

        $this->assertEquals('test', $query);
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::applyOrderBy
     */
    public function testApplyOrderBy_nonRelation()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo('orderBy'), $this->equalTo('x.foo DESC'));

        $column = $this->getMockBuilder('\DataView\Column')->disableOriginalConstructor()->getMock();
        $column
            ->expects($this->once())
            ->method('getPropertyPath')
            ->will($this->returnValue('foo'));
        $column
            ->expects($this->exactly(2))
            ->method('getSortOrder')
            ->will($this->returnValue(Column::SORT_ORDER_DESCENDING));

        $doctrineORM = new TestDoctrineORMApplyOrderBy(null);
        $doctrineORM->setColumns(array($column));

        $doctrineORM->applyOrderBy($queryBuilder, 'x');
    }

    /**
     * @covers DataView\Adapter\DoctrineORM::applyOrderBy
     */
    public function testApplyOrderBy_relation()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo('orderBy'), $this->equalTo('foo.bar DESC'));

        $column = $this->getMockBuilder('\DataView\Column')->disableOriginalConstructor()->getMock();
        $column
            ->expects($this->once())
            ->method('getPropertyPath')
            ->will($this->returnValue('foo.bar'));
        $column
            ->expects($this->exactly(2))
            ->method('getSortOrder')
            ->will($this->returnValue(Column::SORT_ORDER_DESCENDING));

        $doctrineORM = new TestDoctrineORMApplyOrderBy(null);
        $doctrineORM->setColumns(array($column));

        $doctrineORM->applyOrderBy($queryBuilder, 'x');
    }
}
