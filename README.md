# HeyTimeSheet
Unofficial PHP API for HeyUpdate.com

Requirement
-----------

This requires PHP 5.3 or later and a valid API key.

Usage
-----
```php
	require_once('HeyUpdateAPI.php')
	$hey = new HeyUpdateAPI("<<YOUR TOKEN>>", "https://api.heyupdate.com");
```
	
Getting Recent updates
-----

The default behaviour of the API is to return 7 days of updates:
```php
  $updates = $hey->Updates()->getRes();
```
  
Getting Custom Ranges
-----

Use UpdatesByPeriod to get custom date ranges. 
Supports 'day', 'week' and 'month'.
```php
  $day = $hey->UpdatesByPeriod("day")->getRes();
  $week = $hey->UpdatesByPeriod("week")->getRes();
  $month = $hey->UpdatesByPeriod("month")->getRes();
```

Filter results
-----
```php
  $filter_email = $hey->UpdatesByPeriod("month")->filterByEmail("hey@update.com")->getRes();
  $filter_name = $hey->UpdatesByPeriod("month")->filterByName("sam hermans")->getRes();
```
