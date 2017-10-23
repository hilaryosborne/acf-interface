<?php

namespace ACFFusion;

class Builder extends Field {

    public static $defaults = [
        'key' => '',
        'title' => '',
        'fields' => [],
        'location' => [],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ];

    public static $type = 'group';

    public $fields = [];

    public function __construct($code, $label) {
        // Call the parent first to set all the standard field values
        parent::__construct($code, $label);
        // Set the title value, this is unique to core groups
        $this->settings['title'] = $label;
    }

    public function addLocation($param, $value, $operator='==') {

        $this->settings['location'][] = [
            [
                'param' => $param,
                'operator' => $operator,
                'value' => $value,
            ]
        ];

        return $this;

    }

    public function addField($fieldObj) {
        // Set the parent for future use
        $fieldObj->setParent($this);
        // Add to the fields collection
        $this->fields[] = $fieldObj;
        // Return for chaining
        return $this;
    }

    public function addFieldset($fieldset, $prefix='') {
        // Set the parent for future use
        $fieldset->fields($this, $prefix);
        // Return for chaining
        return $this;
    }

    public function toArray() {
        // Retrieve the field settings
        $settings = $this->settings;
        // Loop through each of the fields
        foreach ($this->fields as $k => $field) {
            // Populate the subfields with the to array results
            $settings['fields'][$field->getCode()] = $field->toArray();
        }
        // return the built settings
        return $settings;
    }

    public function toSettings() {
        // Retrieve the field settings
        $settings = $this->settings;
        // Loop through each of the fields
        foreach ($this->fields as $k => $field) {
            // Populate the subfields with the to array results
            $settings['fields'][] = $field->toSettings();
        }
        // return the built settings
        return $settings;
    }

    public function toKeys() {
        // Retrieve the field settings
        $keys = [];
        // Loop through each of the fields
        foreach ($this->fields as $k => $field) {
            // Populate the subfields with the to array results
            $keys = array_merge($keys, $field->toKeys());
        }
        // return the built settings
        return $keys;
    }

    public function toNames() {
        // Retrieve the field settings
        $names = [];
        // Loop through each of the fields
        foreach ($this->fields as $k => $field) {
            // Populate the subfields with the to array results
            $names = array_merge($names, $field->toNames());
        }
        // return the built settings
        return $names;
    }

    public function getIndex($values) {
        // Retrieve the field settings
        $index = new \stdClass();
        // The collection to add items to
        $index->collection = [];
        // Loop through each of the fields
        foreach ($this->fields as $k => $fieldObj) {
            // Retrieve the value
            $value = isset($values[$fieldObj->getKey()]) ? $values[$fieldObj->getKey()] : false ;
            // Populate the subfields with the to array results
            $fieldObj->toIndex($index, $value);
        }
        // return the built settings
        return $index->collection;
    }

    public function toValues($values, $valueFormat='key', $outFormat='key', $prefix='') {
        // Retrieve the field settings
        $output = [];
        // If the values are coming from acf
        if ($valueFormat === 'acf') {
            // The filtered array
            $filtered = [];
            // Loop through each of the fields
            foreach ($this->fields as $k => $fieldObj) {
                // If the value does not exist for that field object
                if (!isset($values[$fieldObj->getCode()])) { continue; }
                // Set the filtered array
                $filtered[$fieldObj->getKey()] = $values[$fieldObj->getCode()];
            }
            // Replace the values array
            $values = $filtered;
        }
        // Loop through each of the fields
        foreach ($this->fields as $k => $fieldObj) {
            // Determine the keys we are going to look for
            $valueKey = ($valueFormat === 'key' || $valueFormat === 'acf') ? $fieldObj->getKey() : $fieldObj->getCode();
            // If no value has been set
            if (!isset($values[$valueKey])) { continue; }
            // Populate the subfields with the to array results
            $output = array_merge($output, $fieldObj->toValues($values[$valueKey], $valueFormat, $outFormat));
        }
        // return the built settings
        return $output;
    }


}