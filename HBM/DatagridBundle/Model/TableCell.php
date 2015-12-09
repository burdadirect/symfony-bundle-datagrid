<?php

namespace HBM\DatagridBundle\Model;

abstract class TableCell {

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var label
	 */
	protected $label;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var RouteLink
	 */
    protected $theadLink;

    /* GETTER/SETTER **********************************************************/

    public function setKey($key) { $this->key = $key; }
    public function getKey() { return $this->key; }

    public function setLabel($label) { $this->label = $label; }
    public function getLabel() { return $this->label; }

    public function setTheadLink($theadLink) { $this->theadLink = $theadLink; }
    public function getTheadLink() { return $this->theadLink; }

    public function setOptions($options){
    	$this->validateOptions($options, $this->validOptions);

    	$this->options = $options;
    }
    public function getOptions() { return $this->options; }

    /* CUSTOM *****************************************************************/

    public function getTemplate($column, $row, $obj, $default = NULL) {
    	if ($this->hasOption('template')){
    		$template = $this->getOption('template');
    		if(is_string($template)){
    			return $template;
    		} else if(is_callable($template)){
    			return $template($column, $row, $obj);
    		} else {
    			throw new \Exception("How come?");
    		}
    	}

    	return $default;
    }

    public function getTemplateParams() {
    	if ($this->hasOption('templateParams')){
    		$templateParams = $this->getOption('templateParams');

    		if(is_array($templateParams)){
    			return $templateParams;
    		} else {
    			throw new \Exception("How come?");
    		}
    	}

    	return array();
    }

    public function getOption($key, $default = NULL) {
    	if (isset($this->options[$key])) {
    		return $this->options[$key];
    	}

    	return $default;
    }

    public function hasOption($key) {
    	if (isset($this->options[$key])) {
    		return TRUE;
    	}

    	return FALSE;
    }

    public function getAttr($scope) {
    	return $this->getHtmlAttrString($this->getOption($scope.'_attr', array()));
    }

    public function parseValue($column, $row, $obj){
    	if ($this->hasOption("value")){
    		$value = $this->getOption("value");
    		if(is_string($value)){
    			return $value;
    		} else if(is_callable($value)){
    			return $value($column, $row, $obj);
    		} else {
    			throw new \Exception("How come?");
    		}
    	} else if (method_exists($obj, 'get'.ucfirst($this->getKey()))){
    		$value = $obj->{'get'.ucfirst($this->getKey())}();

    		if ($value instanceof \DateTime){
    			$format = 'Y-m-d H:i:s';
    			if (isset($this->options['format'])) {
    				$format = $this->options['format'];
    			}

    			return $value->format($format);
    		} else {
    			return $value;
    		}
    	}
    }

    private function validateOptions($options, $validOptions){
    	foreach ($options as $option => $value){
    		$types = $this->getOptionTypes($option, $validOptions);

    		$valid = false;

    		foreach($types as $type){
    			if($type === 'string'){
    				if(is_string($value)) $valid = true;
    			} else if($type === 'array'){
    				if(is_array($value)) $valid = true;
    			} else if($type === 'callback'){
    				if(is_callable($value)) $valid = true;
    			} else {
    				throw new \Exception("Unknown type for option '$option'");
    			}
    		}

    		if(!$valid){
    			throw new \Exception("Option '$option' is not valid");
    		}
    	}
    }

    private function getOptionTypes($option, $validOptions){
    	if(!isset($validOptions[$option])) throw new \Exception("Not a valid option '$option'");

    	$types = $validOptions[$option];

    	if(strstr($types, "|") !== false){
    		$types = preg_split("/\|/", $types);
    	} elseif(!is_array($types)) {
    		$types = [$types];
    	}

    	return $types;
    }

    private function getHtmlAttrString($attributes) {
    	$parts = array();
    	foreach ($attributes as $key => $value) {
    		$parts[] = $key.'="'.$value.'"';
    	}

    	return implode(' ', $parts);
    }

    public function isSortable(){
    	return $this->hasOption("sort_key");
    }

    public function getSortKey(){
    	return $this->getOption("sort_key");
    }

}
