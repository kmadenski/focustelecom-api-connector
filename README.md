## Install
``` bash
composer require kmadenski/focustelecom-api-connector
```
## Usage
```php
$connector = new \App\Core\Connector(
    "login.admin",
    "apiKey",
    "domain",
    "50char change",
    "baseUrl"
);

echo $connector->campaignList();
```
## Methods:
``` php
// Campaigns list
$connector->campaignList();

// Classifiers list
$campaigns_id = 1;
$connector->classifiersList($campaigns_id);

// Add record
$record = new stdClass();
$record->values['some field'] = "some value";
$record->numbers =  ["555555555"];
$record->emails = ["awdawjdawjdawkdwad@qwpqweqw.qwdqwd"];
$record->external_id = 999;
$records = [$record];

$connector->addRecords($campaigns_id,$records)

// ... and others -> look at App\Core\Connector class
```
