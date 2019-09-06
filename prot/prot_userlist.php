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
 * This page is the protocol page providing an overview of all users/subusers.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_userlist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$infostring = ''; // Result and error messages.

$table = new html_table();

$inarchive = false;
if ( optional_param('displayarchive', '', PARAM_ALPHANUM) != '' ||
    (optional_param('inArchive', '', PARAM_ALPHANUM) == '1' && optional_param('leavearchive', '', PARAM_ALPHANUM) == '')) {
    if ( isset($_POST['archivetag']) ) {
        $archivetag = $_POST['archivetag'];
        $cond = ' AND archivetag in(\''.implode('\', \'', $archivetag).'\')';
        $sessions = etest_prot_get_sessions($etest, $cond);
        $inarchive = true;
    } else {
        $infostring = get_string('noarchivetagsselected', 'etest');
        $sessions = etest_prot_get_sessions($etest);
    }
} else {
    $sessions = etest_prot_get_sessions($etest);
}

etest_read_details($etest);

$displayregular = true;
// Special actions.
if ( optional_param('delete', '', PARAM_ALPHANUM) != '' ) {
    $delstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $delstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                '</td><td>'.strftime("%H:%M", $session->starttime).'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = get_string('deleteempty', 'etest');
    } else {
        echo etest_safe_header('prot'); ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="a" value="<?php echo $etest->id ?>">
        <div>
            <?php print_string('deleteverify', 'etest', $count) ?>
        </div>
        <table class="generaltable">
            <?php echo $delstring ?>
        </table>
        <p>
            <?php submit_button('deleteVerify'); ?>
            <?php submit_button('cancel'); ?>
        </p>
    </form>
        <?php etest_safe_footer('prot');
        $displayregular = false;
    }
} else if ( optional_param('deleteVerify', '', PARAM_ALPHANUM) != '' ) {
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $DB->delete_records('etest_session', array('id' => $session->id));
            $DB->delete_records('etest_action', array('session' => $session->id));
            $count++;
        }
    }
    $infostring = get_string('deletedone', 'etest', $count);
} else if ( optional_param('archive', '', PARAM_ALPHANUM) != '' ) {
    $arcstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $arcstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                    '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                    '</td><td>'.strftime("%H:%M", $session->starttime).'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = '<div>'.get_string('archiveempty', 'etest').'</div>';
    } else {
        echo etest_safe_header('prot'); ?>
        <form name="fm" method="POST" target="_self" action="prot_userlist.php">
            <input type="hidden" name="a" value="<?php echo $etest->id ?>">
            <div>
                <?php print_string('archiveverify', 'etest', $count) ?>
            </div>
            <table class="generaltable">
                <?php echo $arcstring ?>
            </table>
            <p>
                <?php print_string('archiveselecttag', 'etest')?>
                <table>
                    <tr>
                        <td><?php print_string('archiveselecttagnew', 'etest')?></td>
                        <td><input type="text" name="archivetagnew" value=""></td>
                    </tr>
        <?php
        $tags = $DB->get_records_sql('SELECT archivetag FROM '.$CFG->prefix.'etest_session WHERE etest = '.$etest->id.
            ' AND NOT archivetag IS NULL GROUP BY archivetag');
        if ( count($tags) > 0 ) {
            echo '<tr><td>', get_string('archiveselecttagold', 'etest'),
                '</td><td><select name="archivetagold">',
                '<option></option>';
            foreach ($tags as $tag) {
                echo '<option>', $tag->archivetag, '</option>';
            }
            echo '</select></td></tr>';
        }
        ?>
                </table>
                <p>
                    <?php submit_button('archiveVerify',
                        "if ( checkInput() ) document.forms['fm'].target = '_self'; else return false;"); ?>
                    <?php submit_button('cancel'); ?>
                </p>
            </p>
        </form>
        <script type="text/javascript">
        function checkInput()
        {
            var fm = document.forms['fm'];
            var c = 0;
            if ( (""+fm.archivetagnew.value).replace(/^\s+/, "").replace(/\s+$/, "") != "" )
                c++;
            if ( !!fm.archivetagold && fm.archivetagold.value != "" )
                c++;
            if ( c == 0 )
                alert("<?php print_string('archivetagneeded', 'etest'); ?>");
            else if ( c == 2 )
                alert("<?php print_string('archivetagonlyone', 'etest'); ?>");
            else
                return true;
            return false;
        }
        </script>
        <?php echo etest_safe_footer('prot');
        $displayregular = false;
    }
} else if ( optional_param('archiveVerify', '', PARAM_ALPHANUM) != '' ) {
    $archivetag = optional_param('archivetagnew', '', PARAM_TEXT);
    if ( $archivetag == '' ) {
        $archivetag = optional_param('archivetagold', '', PARAM_TEXT);
    }
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $DB->update_record('etest_session', array('id' => $session->id, 'archivetag' => $archivetag));
            $count++;
        }
    }
    $infostring = get_string('archivedone', 'etest', array('count' => $count, 'label' => $archivetag));
    // Reload the sessions.
    $sessions = etest_prot_get_sessions($etest);
}

if ( optional_param('unarchive', '', PARAM_ALPHANUM) != '' ) {
    $unarcstring = '';
    $count = 0;
    foreach ($sessions as $session) {
        if ( optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ) {
            $unarcstring .= '<tr><td>'.$session->displayname.'<input type = "hidden" name="cb_'.$session->id.'" value="1"/>'.
                    '</td><td>'.strftime("%d.%m.%y", $session->starttime).
                    '</td><td>'.strftime("%H:%M", $session->starttime).'</td>'.
                    '</td><td>'.$session->archivetag.'</td></tr>';
            $count++;
        }
    }
    if ( $count == 0 ) {
        $infostring = get_string('unarchiveempty', 'etest');
    } else {
        echo etest_safe_header('prot'); ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="a" value="<?php echo $etest->id ?>">
        <div>
            <?php print_string('unarchiveverify', 'etest', $count) ?>
        </div>
        <table class="generaltable">
            <?php echo $unarcstring ?>
        </table>
        <p>
            <?php submit_button('unarchiveVerify'); ?>
            <?php submit_button('cancel'); ?>
        </p>
    </form>
        <?php echo etest_safe_footer('prot');
        $displayregular = false;
    }
} else if ( optional_param('unarchiveVerify', '', PARAM_ALPHANUM) != '' ) {
    $count = 0;
    foreach ($_POST as $key => $val) {
        if ( substr($key, 0, 3) == 'cb_' && $val == 1 ) {
            $sesid = substr($key, 3);
            if ( is_number($sesid) ) {
                $DB->update_record('etest_session', array('id' => $sesid, 'archivetag' => null));
                $count++;
            }
        }
    }
    $infostring = get_string('unarchivedone', 'etest', $count);
    // Reload the sessions.
    $sessions = etest_prot_get_sessions($etest);
}

if ( $displayregular ) {
    // Regular page.

    // Table.
    $table->name = get_string('userlist', 'etest');
    if ( $inarchive ) {
        $table->name .= ' ('.implode(', ', $archivetag).')';
    }
    $table->head = array();
    $table->align = array();
    $table->ordertype = array();
    if ( $format == 'showashtml' ) {
        array_push($table->head, '<input type="checkbox" name="cbAll" onclick="top.control.SwitchAll(this.checked)"/>');
        array_push($table->align, 'center');
        array_push($table->ordertype, '');
    }
    array_push($table->head, get_string('users'), get_string('date'), get_string('time'), get_string('duration', 'etest'));
    array_push($table->align , 'left', 'left', 'left', 'left');
    array_push($table->ordertype, 'a', 'd', 't', 'u');

    if ( $inarchive ) {
        array_push($table->head, get_string('archivetag', 'etest'));
        array_push($table->align, 'center');
        array_push($table->ordertype, '');
    }

    $nexercises = etest_n_exercises($etest);
    for ($i = 0; $i < $nexercises; $i++) {
        array_push($table->head, 'Ex '.($i + 1));
        array_push($table->align, 'center');
        array_push($table->ordertype, '');
    }

    array_push($table->head, get_string('worse', 'etest'), get_string('origPts', 'etest'), get_string('origGrade', 'etest'),
        get_string('recalcPts', 'etest'), get_string('recalcGrade', 'etest'),
        get_string('oldPts', 'etest'), get_string('oldGrade', 'etest'));
    array_push($table->align, 'center', 'center', 'center', 'center', 'center', 'center', 'center');
    array_push($table->ordertype, '', '', '', '', '', '', '');

    // Subuser data.
    if ( $etest->flags & ETEST_USESUBUSERS ) {
        etest_expand_subuserdata($etest);
        foreach ($etest->sudata as $field) {
            array_push($table->head, $field['name']);
            array_push($table->align, 'left');
            array_push($table->ordertype, '');
        }
    }

    etest_read_grade($etest);
    $ngrades = count($etest->grade);
    if ( $ngrades > 0 ) {
        $origcourses = array_fill(0, $ngrades, 0);
        $rccourses = array_fill(0, $ngrades, 0);
    } else {
        $origcourses = array();
        $rccourses = array();
    }

    // Whether recalc is ever used.
    $rcused = false;

    $i = 0;
    foreach ($sessions as $session) {
        if ( !isset($session->excombi) || $session->excombi == "" ) {
            continue;
        }
        etest_prot_load_prot($session, $etest);
        $line = array();
        if ( $format == "showashtml" ) {
            array_push($line, '<input type="checkbox" name="cb_'.$session->id.'" value="'.$session->displayname.'"'.
                            (optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ? ' checked="checked"' : '').'/>');
        }
        array_push($line,
            $format == "showashtml" ?
               '<a href="javascript:parent.control.SelectHist(\''.$session->id.'\')" target="control"><B>'.
               $session->displayname.
               '</b></a>'
               : $session->displayname,
            $format == "showashtml" ?
                 '<nobr><a href="javascript:parent.control.StartDate('.usergetmidnight($session->starttime).')" title="'.
                 get_string('use_as_startdate', 'etest').'"><b>[</b></a> '.strftime("%d.%m.%y", $session->starttime).
                 ' <a href="javascript:parent.control.EndDate('.usergetmidnight($session->starttime).')" title="'.
                 get_string('use_as_enddate', 'etest').'"><b>]</b></a></nobr>'
                 : strftime("%d.%m.%y", $session->starttime),
            strftime("%H:%M", $session->starttime));

        $p = etest_prot_summary_prot($session, $etest);

        array_push($line, $p->duration);

        if ( $inarchive ) {
            array_push($line, $session->archivetag);
        }

        if ( $format == "showashtml" ) {
            for ($i = 0; $i < count($p->ex); $i++) {
                $addstring = $p->exBest[$i];
                if ( $addstring != '' ) {
                    $addstring = ' ['.$addstring.']';
                }
                array_push($line, $p->ex[$i].$addstring);
            }
        } else {
            for ($i = 0; $i < count($p->ex); $i++) {
                array_push($line, $p->ex[$i]);
            }
        }
        array_push($line, $p->worsed);

        array_push($line, $p->orig[0], $p->orig[2]);
        array_push($line, $p->recalc[0], $p->recalc[2]);
        array_push($line, $p->old[0], $p->old[2]);

        if ( $p->recalc[0] != "" ) {
            $rcused = true;
        }

        // Subuser data.
        if ( $etest->flags & ETEST_USESUBUSERS ) {
            $subdat = isset($session->data) ? explode(ETEST_X01, $session->data) : array();
            for ($i = 0; $i < count($etest->sudata); $i++) {
                array_push($line, isset($subdat[$i]) ? $subdat[$i] : '');
            }
        }

        $table->data[] = $line;

        if ( 0 <= $origcourse && $origcourse < $ngrades ) {
            $origcourses[$origcourse]++;
        }
        if ( $p->recalc[0] != "" && 0 <= $rccourse && $rccourse < $ngrades ) {
            $rccourses[$rccourse]++;
        }
    }

    $taborig = new html_table();
    $taborig->name = get_string('orig-data', 'etest');
    $taborig->head = array(get_string('users'), get_string('grades', 'etest'));
    $taborig->align = array("left", "left");
    for ($i = 0; $i < $ngrades; $i++) {
        $taborig->data[] = array($origcourses[$i], $etest->grade[$i]->shortname);
    }

    $tabrecalc = new html_table();
    $tabrecalc->name = get_string('recalc-data', 'etest');
    $tabrecalc->head = array(get_string('users'), get_string('grades', 'etest'));
    $tabrecalc->align = array('left', 'left');
    for ($i = 0; $i < $ngrades; $i++) {
        $tabrecalc->data[] = array($rccourses[$i], $etest->grade[$i]->shortname);
    }

    $tables = array($table, $taborig);
    if ( $rcused == true ) {
        array_push($tables, $tabrecalc);
    }

    if ( $format == "showashtml" ) {
        echo etest_safe_header('prot');
        if ( $infostring != '' ) {
            echo $OUTPUT->box($infostring, 'noticebox');
        }
        ?>
    <form name="fm" method="POST" target="_self" action="prot_userlist.php">
        <input type="hidden" name="a" value="<?php echo $etest->id ?>">
        <input type="hidden" name="inArchive" value="<?php echo $inarchive ? '1' : ''; ?>">
        <input type="hidden" name="sortinfo" value="<?php echo $sortinfo ?>">
        <?php print_tables_html(array($table)); ?>
    <p>
        <?php submit_button('delete'); echo '<br />';
        if ( !$inarchive ) {
            submit_button('archive'); echo '<br />';
        } else {
            submit_button('unarchive'); echo '<br />';
        }

        $tags = $DB->get_records_sql('SELECT archivetag, count(1) as cnt FROM '.$CFG->prefix.'etest_session WHERE etest = '.
            $etest->id.' AND NOT archivetag IS NULL GROUP BY archivetag');
        if ( count($tags) > 0 ) {
            submit_button('displayarchive');
            echo '<select name="archivetag[]" size="1" multiple="multiple" style="height:35px;">';
            foreach ($tags as $tag) {
                echo '<option value="', $tag->archivetag, '"',
                    $inarchive && array_search($tag->archivetag, $archivetag) !== false ? ' selected="selected"' : '',
                    '>', $tag->archivetag, '  (#', $tag->cnt, ')</option>';
            }
            echo '</select><br />';
        }
        if ( $inarchive ) {
            submit_button('leavearchive'); echo '<br />';
        }
        ?>
        </p>
    </form>
    <p>[..]  ... bester Wert (bei Verschlechterung, m&ouml;glicherweise Umschaltproblem)
    <?php
        if ( $rcused ) { ?>
            <br />(..)  ... Recalc-Wert (Neuberechnung)<?php
            print_tables_html(array($taborig));
        } ?>
    </p>
    <?php
        echo etest_safe_footer('prot');
    } else if ( $format == "downloadascsv" ) {
        print_tables_csv("Userlist", $tables);
    } else if ( $format == "downloadasexcel" ) {
        print_tables_xls("Userlist", $tables);
    }
}

function submit_button($id, $onclick = "document.forms['fm'].target = '_self';") {
    echo '<input type="submit" name="', $id, '" onclick="', $onclick, '" value="',
        get_string(str_replace('Verify', '', $id), 'etest'), '" target="data"> ';
    if (get_string_manager()->string_exists($id.'_explain', 'etest')) {
        print_string($id.'_explain', 'etest');
    }
}