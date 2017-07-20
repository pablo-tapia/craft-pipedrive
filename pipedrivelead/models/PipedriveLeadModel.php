<?php

namespace Craft;

/**
 * Provides a read-only object representing a lead, which is returned
 * by our service class and can be used in the templates.
 * @package PipedriveLead
 * @version 1.2.0
 */
class PipedriveLeadModel extends BaseModel
{
    /**
     * Defines what's returned when someone puts {{ lead }}
     * directly on the template
     * @return string
     */
    public function __toString()
    {
        return $this->email;
    }

    /**
     * Define the attributes of the model
     * @return array
     */
    public function defineAttributes()
    {
        return [
            'name' => AttributeType::Name,
            'email' => AttributeType::Email,
            'company' => AttributeType::String,
            'telephone' => AttributeType::String,
            'description' => AttributeType::String,
        ];
    }
}
