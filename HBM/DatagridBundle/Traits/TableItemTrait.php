<?php

namespace HBM\DatagridBundle\Traits;


trait TableItemTrait
{
    private function hasOption($option){
        return isset($this->options[$option]);
    }

    private function getOption($option, $default = NULL){
        if ($this->hasOption($option)) {
        	return $this->options[$option];
        }

        return $default;
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

}
