Compredict's Signals Mapper PHP Client
=========================================

**Communicate with Signals Mapper RESTful API**

Further documentation on Signals Mapper API can be found [here](https://signals.compredict.de/docs).

Requirements
-----------------------------
- **PHP** (8.1.2 or greater)
- **cURL** extension

Namespace
------------------------------
All the examples below assume the Mapper\Api\Client class is imported into the scope with the following namespace 
declaration:

    use Mapper\Api\Client as Mapper;

Signal, Organization and Organization Signal structure
-------------------------------------------------------

**Signal**

description: (required) str, indicates role of the signal

units: (optional) str, units of measure,by default: None

is_virtual: (optional) bool, indicates if signal is virtual or not, by default: False


**Organization**

name: (required) str, name of the organization

prod_id: (optional) int, id of organization in prod version of AI CORE

demo_id: (optional) int, id of organization in demo version of AI CORE

***IMPORTANT!***

Organization are read based on their prod_id or demo_id, not based on id assigned in database. 

**Organization Signal**

name: (required) name of the organization's signal

organization_id: (required) id of organization that signal belong to

units: (optional) units of measurement for organization's signal

label: (optional) label used for visualization of organization's signal

xaxis: (optional) json file used for visualization of signal data; needs to contain label
         unit, filter for x and y axis.
         
signal_id: (optional) id of the signal that organization signal is mapped to


Functionality
--------------------------------

Signals Mapper PHP SDK is able to:

1. Run all CRUD operations on Signal, Organization and Organization Signal
2. Generate json xaxis value for Organization Signal without manual creating of json file
3. Map Organization Signals from one Organization to Organization Signals of another Organization

Examples of usage
--------------------------------

**Reading, creating, updating and deleting Signal**

    // initilize mapper
    $mapper = new Mapper();
    
    // read signals, if ids passed - read signals with specified ids
    $signals = $mapper->getSignals();
    $signalsFromIds = $mapper->getSignals([1,2,3]);
    $oneSignal = $mapper->getSignals(1);

    // create signal
    $signal = $mapper->createSignal("test description", "s", true);

    // update signal units and setting is_virtual to false
    $signal->update(null, "m", false);

    // delete signal
    $signal->delete();

**Reading, creating, updating and deleting Organization**

    // reading Organization and all Organizations
    $organization = $mapper->readOrganization(2);
    $allOrganizations = $mapper->readAllOrganizations();

    // creating organization
    $organization = $mapper->createOrganization("TestOrganization");

    // update organization with prod_id=12 and demo_id=13
    $organization->update(null, 12, 13);

    // delete created organization
    $organization->delete()

**Read OrganizationSignals that belong to specific organization**

    // get organization
    $organization = $mapper->readOrganization(2);
    
    // get all organization signals that belong to organization
    $organizationSignals = $organization->getSignals()

    // get organization signals mapped to specific Signals Ids from organization
    $organizationSignals = $organization->getSignals([1,2,30]);

    // get organization signals that belong to organization directly from client
    // in this case we get organization signals mapped to signals [1,2,3] and belonging to
    // organization with demo_id = 1
    $organizationSignals = $mapper->readOrganizationSignalsFromOrganization(1, [1,2,3], "demo");

**Map organization signals of one Organization to organization signals of another Organization**

    // call directly from client; map organization signals from organization with prod_id=1 to 
    // organization signals from Organization with prod_id 2
    $map = $mapper->mapOrganizationSignals(1, 2, null);

    // exact the same call, but with usage of resource class
    $organization = $mapper->readOrganziation(1);
    $map = $organization->mapSignals(2, null);

    // if user would like to read map based on specific signals ids
    $map = $organziation->mapSignals(2, [1,2,3]);

**Creating json with xaxis**

    $xaxis = $mapper->constructXaxis("label1", "unit1", "label2", "unit2");

**Reading, creating, updating and deleting organization signals**

    // if called with null - reads all organziation signals
    $organizationSignals = $mapper->getOrganizationSignals(null);
    
    // if called with ids - read organziation signals based on ids
    $organziationSignals = $mapper->getOrganizationSignals([1,2,3]);

    // create organization signal
    $organizationSignal = $mapper->createOrganizationSignal("Testing signal", 1);
    
    // update organization signal with signal_id=3
    $organizationSignal->update(null, 3);

    // delete organziation signal
    $organizationSignal->delete();

Exception handling
---------------------------

***In case exception occurs during running client methods, user has two choices:***
1. Set Signals Mapper PHP SDK to throw exception at the moment of encounter

        // set client method failOnError to true (this is default value)
        $mapper->failOnError(true);

2. Set Signals Mapper PHP SDK to save encountered exceptions and not throw exception at the moment of encounter

        // set client method failOnError to false
        $mapper->failOnError(false);

**In case of first option, exceptions can be caught easily, for example:**
    
    try { 
        $mapper->getSignals(1);
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
        

