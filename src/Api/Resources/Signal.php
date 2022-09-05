<?php

namespace Mapper\Api\Resources;

use Mapper\Api\Resource;

class Signal extends Resource
{
    public function __construct($object = false, $client = null)
    {
        parent::__construct($object, $client);
    }

    public function update($description=null, $units=null, $is_virtual=null)
    {
        $updatedSignal = $this->client->updateSignal($this->id, $description, $units, $is_virtual);
        $this->fields = $updatedSignal->fields;
        return $this;
    }

    public function delete()
    {
        return $this->client->deleteSignal($this->id);
    }
}
