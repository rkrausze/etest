<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * This page returns the recalculation of exercise-entries (if an execise had to be corrected).
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  dir="ltr" lang="de" xml:lang="de" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
Save ...
<?php

$recalcstates = optional_param('recalcstates', '', PARAM_TEXT);

// Save the recalced states.
$DB->set_field('etest_session', 'recalcstates', $recalcstates, array('id' => $sessionid));
$DB->set_field('etest_session', 'recalcdate', time(), array('id' => $sessionid));

$i = 0;
while ( true ) {
	$actionid = optional_param('actionid'.$i, '', PARAM_TEXT);
    if ( $actionid == '' ) {
        break;
    }
    $pr = optional_param('pr'.$i, '', PARAM_TEXT);
    if ( isset($pr) && $pr != '' ) {
        $data = $DB->get_field('etest_action', 'data', array('id' => $actionid));
        if ( isset($data) ) {
            $data = preg_replace('/ ([0-9,]*)$/', '', $data);
            $data .= ' '.$pr;
            $DB->set_field('etest_action', 'data', $data, array('id' => $actionid));
        }
    }
    $i++;
}

// Write back html.
?>
<script type="text/javascript">
    top.control.Recalc();
</script>
</body>
</html>