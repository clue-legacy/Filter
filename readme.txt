== Description ==
Construct SQL WHERE clauses, filter arrays/iterators, etc.

Author:   Christian Lück <christian@lueck.tv>
Homepage: https://github.com/clue/Filter
License:  MIT-style license

== Example ==
<?php

$filter = Filter::all(Filter::keyEq('name','Pete'),Filter::keyGt('id',10));
$filter->toSql(); // `name`='Pete' AND 'id'>10

foreach($filter->toIterator($users) as $user){ // iterate over all users matching the filter
}

$filtered = Filter::negate($filter)->apply($users); // only keep users that did NOT match filter

$filter->matches(array('name'=>'Pete','id'=>100)); // true

?>
== Requirements / Dependencies ==
* PHP 5.3+

== Known issues ==
* Converting a filter to a SQL query string (WHERE clause) requires access to a proprietary/unreleased database class.
  Altough a possible option, I personally feel releasing yet another database abstraction layer class is totally redundant and should be avoided. 
  Any ideas on how to avoid this issue welcome!