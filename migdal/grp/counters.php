<?php
# @(#) $Id$

/*
 * ttl        - Time (in hours) before new counter in series will be created.
 *              Set to 0 for endless counter.
 * period     - Time (in hours) for IP to be remembered.
 *              Set to 0 to ignore IPs.
 * grp        - Grp mask of postings that should have this counter
 *              automatically created.
 * max_serial - Maximal number of counters to store minus 1.
 *              Set to -1 for unlimited.
 */

require_once('grp/grps.php');

define('CMODE_EAR_HITS',1);
define('CMODE_EAR_CLICKS',2);

$counterModes=array(
		    CMODE_EAR_HITS
		     => array(
			      'ttl'        => 168,
			      'period'     => 0,
			      'grp'        => GRP_EARS,
			      'max_serial' => 0
			     ),
		    CMODE_EAR_CLICKS
		     => array(
			      'ttl'        => 168,
			      'period'     => 0,
			      'grp'        => GRP_EARS,
			      'max_serial' => 0
			     ),
                   );
?>
