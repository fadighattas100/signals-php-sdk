<?php

namespace Mapper\Api\Resources;

use Mapper\Api\Resource;

class OrganizationSignal extends Resource
{
    public function __construct($object = false, $client = null)
    {
        parent::__construct($object, $client);
    }

    public function update(
        $organizationId=null,
        $signalId=null,
        $units=null,
        $label=null,
        $xaxis=null
    )
    {
        $organizationSignal = $this->client->updateOrganizationSignal($this->id, $organizationId, $signalId, $units, $label, $xaxis);
        $this->fields = $organizationSignal->fields;
        return $this;
    }

    public function delete()
    {
        return $this->client->deleteOrganizationSignal($this->id);
    }
}
