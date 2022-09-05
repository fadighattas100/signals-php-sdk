<?php

namespace Mapper\Api\Resources;

use Mapper\Api\Resource;

class Organization extends Resource
{
    public function __construct($object = false, $client = null)
    {
        parent::__construct($object, $client);
    }

    public function update($name=null, $prod_id=null, $demo_id=null)
    {
        $updatedSignal = $this->client->updateOrganization($this->id, $name, $prod_id, $demo_id);
        $this->fields = $updatedSignal->fields;
        return $this;
    }

    public function delete()
    {
        return $this->client->deleteOrganization($this->id);
    }

    public function getSignals($ids=null, $version="prod")
    {
        $id = $version === 'prod' ? $this->prod_id : $this->demo_id;
        return $this->client->readOrganizationSignalsFromOrganization($id, $ids, $version);
    }

    public function mapSignals($toOrganization, $ids=null, $version="prod")
    {
        $id = $version === 'prod' ? $this->prod_id : $this->demo_id;
        return $this->client->mapOrganizationSignals($id, $toOrganization, $ids, $version);
    }
}
