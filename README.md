PHPDataView
===========

A framework-agnostic PHP DataView/DataGrid library.

The purpose of this library is to sort and filter ORM/ODM result sets with a minimal amount of work. It is not responsible for doing any kind of rendering or dealing with requests - only getting the relevant result set given a set of constraints in an ORM/ODM agnostic manner.

Usage
===========

```php
// specify the adapter to use
$dataview = new \DataView\DataView(new \DataView\Adapter\DoctrineORM($this->getEntityManager()));

// specify a Doctrine repository name
$dataview->setSource('Entity\Company');

// alternatively specify a QueryBuilder if you want to pre-filter the result-set:
//$dataview->setSource($entityManager->getRepository('Entity\Company')->createQueryBuilder('c')->getSomeSubsetOfCompanies());

// filter on an attribute
$dataview->addFilter(new \DataView\Filter(
	'name', 
	\DataView\Filter::COMPARISON_TYPE_EQUAL, 
	'FreeOfficeFinder'
));

// filter on a many-to-one relation
$dataview->addFilter(new \DataView\Filter(
	'location.name', 
	\DataView\Filter::COMPARISON_TYPE_EQUAL, 
	'United Kingdom'
));

// filter on a many-to-many relation
$dataview->addFilter(new \DataView\Filter(
	'contact_associations.contact.first_name', 
	\DataView\Filter::COMPARISON_TYPE_EQUAL, 
	'Bob'
));

// Pagerfanta pager
$pager = $dataview->getPager();
```
