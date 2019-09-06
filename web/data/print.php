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
 * Page to print out the result.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');

$PAGE->set_context(null);
$PAGE->set_url('/mod/etest/web/data/print.php');
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('embedded');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('test');
?>
<script type="text/javascript">
    var s = top.control.PrintForm;
    s = s.replace(/^\s*/g, "");
    s = s.replace(/\s*$/g, "");
    if ( s == "" || s == "<br />" )
        s = '<p align="right">$TITLE$, $DATE$</p>$USERDATA$' +
            '<p align="center"><font face="Arial"><?php print_string('print1', 'etest'); ?></font></p>' +
            '<p align="center"><font face="Arial"><b>$COURSENAME$</b></font></p>' +
            '<p align="center"><font face="Arial">$RESULTLIST$</font></p>';
    s = s.replace(/\$FIRSTNAME\$/g, unescape(top.control.UserNameV));
    s = s.replace(/\$LASTNAME\$/g, unescape(top.control.UserNameN));
    s = s.replace(/\$BIRTHDAY\$/g, unescape(top.control.UserGebDat));
    s = s.replace(/\$TITLE\$/g, unescape(top.control.Title));
    s = s.replace(/\$DATE\$/g, '<?php echo userdate(time()); ?>');
    s = s.replace(/\$COURSENAME\$/g, top.control.CourseName);
    s = s.replace(/\$ADDTEXT\$/g, top.control.GradeAddText);
    s = s.replace(/\$RESULTLIST\$/g, top.control.sResultList());
    var sData = '<table border="0">';
    sData += outData("<?php print_string("name") ?>", top.control.UserNameN != '' ? unescape(top.control.UserNameV)+" "+
            unescape(top.control.UserNameN) : unescape(top.control.UserDisplayName));
    sData += outData("<?php print_string("birthday", "etest") ?>", unescape(top.control.UserGebDat));
    for (var i = 0; i < top.control.UserData.length; i += 2)
        sData += outData(top.control.UserData[i], top.control.UserData[i+1]);
    sData += '</table>';
    s = s.replace(/\$USERDATA\$/g, sData);
    for (var i = 0; i < top.control.UserData.length; i += 2)
        s = s.replace(eval('/\\$USER'+(i/2)+'\\$/g'), top.control.UserData[i+1]);
    for (var i = 0; i < top.control.UserData.length; i += 2) {
        try {
            s = s.replace(eval('/\\$'+top.control.UserData[i]+'\\$/g'), top.control.UserData[i+1]);
        }
        catch (e) {
        }
    }

    document.write(s);

    function outData(name, value) {
        return ( value != "" ) ?
            '<tr><td width="50%"><b>' + name + ':</b></td><td width="50%">' + value + '</td></tr>' : "";
    }

    setTimeout("window.print()", 200);
</script>
<?php
echo etest_safe_footer('test');
