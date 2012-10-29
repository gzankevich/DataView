<?php

namespace DataView;

class Filter
{
	const COMPARISON_TYPE_EQUAL = '=';
	const COMPARISON_TYPE_NOT_EQUAL = '!=';
	const COMPARISON_TYPE_GREATER_THAN = '>';
	const COMPARISON_TYPE_LESS_THAN = '<';

	private $columnName, $comparisonType, $compareValue;

	public function __construct($columnName, $comparisonType, $compareValue)
	{
		$this->columnName = $columnName;
		$this->comparisonType = $comparisonType;
		$this->compareValue = $compareValue;
	}

	/**
	 * Set the name of the column to filter on
	 */
	public function setColumnName($columnName)
	{
		$this->columnName = $columnName;
	}

	public function getColumnName()
	{
		return $this->columnName;
	}

	/**
	 * Set the type of comparison to perform
	 */
	public function setComparisonType($comparisonType)
	{
		$this->comparisonType = $comparisonType;
	}

	public function getComparisonType()
	{
		return $this->comparisonType;
	}

	/**
	 * Set the value to compare to
	 */
	public function setCompareValue($compareValue)
	{
		$this->compareValue = $compareValue;
	}

	public function getCompareValue()
	{
		return $this->compareValue;
	}
}
