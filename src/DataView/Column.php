<?php

namespace DataView;

/**
 * This represents a table column which is mapped to a property path on the target entity/document
 *
 * @package DataView
 * @author George Zankevich <gzankevich@gmail.com> 
 */
class Column
{
	/**
	 * Maps the column to a field/relation on the entity/document
	 *
	 * This is used to keep track of changes to the filters/sort order.
	 *
	 * @var string
	 */
	private $propertyPath = null;

	/**
	 * Maps the property path to use for displaying the cell contents for this column
	 *
	 * This is useful in case you want to sort/filter on one property ($propertyPath) but display another to the user (this attribute). The display property path just needs to map to a getter, not necessarily a real column.
	 * @var string
	 */
	private $displayPropertyPath = null;

	/**
	 * The template to use when rendering the cell contents for this column
	 *
	 * Can be used to customize how, for example, many-to-many associations are presented (e.g. in an unordered list).
	 * @var string
	 */
	private $template = null;

    /**
     * The label to display in the table heading for this column
     *
     * @var string
     */
    private $label = null;

	/**
	 * Constructor
	 *
	 * @param string $propertyPath Maps the column to a field/relation on the entity/document - must map to a real DB column
     * @param string $label The label to display in the table heading for this column
	 * @param string $displayPropertyPath Maps the property path to use for displaying the cell contents for this column - can be any getter on the entity/document
	 * @param string $template The template to use when rendering cells in this column
	 * @return null
	 */
	public function __construct($propertyPath, $label = null, $displayPropertyPath = null, $template = null) 
	{
		$this->propertyPath = $propertyPath;
        // guess the label if none is specified
        $this->label = $label ? $label : ucwords(str_replace('.', ' ', $propertyPath));
        // default to using the property path for the displayed value
		$this->displayPropertyPath = $displayPropertyPath ? $displayPropertyPath : $propertyPath;
		$this->template = $template;
	}

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

	public function setPropertyPath($propertyPath)
	{
		$this->propertyPath = $propertyPath;
	}

	public function getPropertyPath()
	{
		return $this->propertyPath;
	}

	public function setDisplayPropertyPath($displayPropertyPath)
	{
		$this->displayPropertyPath = $displayPropertyPath;
	}

	public function getDisplayPropertyPath()
	{
		return $this->displayPropertyPath;
	}

	public function setTemplate($template)
	{
		$this->template = $template;
	}

	public function getTemplate($template)
	{
		return $this->template;
	}
}
