<?php

namespace HBM\DatagridBundle\Model;

class TableCellValue extends TableCell {

	protected $validOptions = [
		'value' => 'string|callback',
		'th_attr' => 'string|array',
		'td_attr' => 'string|array',
		'sort_key' => 'string',
		'template' => 'string',
		'templateParams' => 'array',
		'format' => 'string',
	];

    public function __construct($key, $label, $options = []){
        $this->key = $key;
        $this->label = $label;

        $this->setOptions($options);
    }

    /* CUSTOM *****************************************************************/

    public function getTemplate($column, $row, $obj, $default = 'HBMDatagridBundle:Datagrid/table-cell:table-cell-value.html.twig') {
   		return parent::getTemplate($column, $row, $obj, $default);
    }

}
