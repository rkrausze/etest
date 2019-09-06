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
 * The main etest configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_etest_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $PAGE, $OUTPUT;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '40'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        $this->add_intro_editor();

        // Settings about the session handling.
        $mform->addElement('header', 'sessionhandling', get_string('sessionhandling', 'etest'));

        $mform->addElement('checkbox', 'useSubusers', get_string('useSubuser', 'etest'));
        $mform->addHelpButton('useSubusers', 'useSubuser', 'etest');
        $mform->addElement('html', '<div class="fitem" id="id_subuserPanel" style="display:none"><div class="fitemtitle">'.
            get_string('subuserLogData', 'etest').'<br/>'.
            $OUTPUT->help_icon('subuserdata', 'etest', get_string('subuserdata', 'etest')).'<br/>'.
            $OUTPUT->help_icon('combodef', 'etest', get_string('combodef', 'etest')).'</div>'.
            '<div class="felement" id="subuserFields"></div>');
        $mform->addElement('text', 'subuserprotname', get_string('subuserprotname', 'etest'), array('size' => '40'));
        $mform->setType('subuserprotname', PARAM_TEXT);
        $mform->addHelpButton('subuserprotname', 'subuserprotname', 'etest');
        $mform->addElement('html', '</div>');

        $mform->addElement('text', 'timelimit', get_string('timelimit', 'etest'), array('size' => '10'));
        $mform->addRule('timelimit', null, 'numeric', null, 'client');
        $mform->setType('timelimit', PARAM_INT);
        $mform->addHelpButton('timelimit', 'timelimit', 'etest');

        $mform->addElement('text', 'maxsession', get_string('maxsession', 'etest'), array('size' => '10'));
        $mform->setType('maxsession', PARAM_INT);
        $mform->addHelpButton('maxsession', 'maxsession', 'etest');

        $mform->addElement('checkbox', 'noContinueSession', get_string('nocontinuesession', 'etest'),
            get_string('nocontinuesession_explain', 'etest'));

        $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1, 'mainfile' => true, 'accepted_types' => '*');
        $mform->addElement('filemanager', 'introref', get_string('introref', 'etest'), null, $options);
        $mform->addHelpButton('introref', 'introref', 'etest');

        // Adding the "exercises" fieldset, where all the exercises are showed.
        $mform->addElement('header', 'exercises', get_string('exercises', 'etest'));

        etest_read_details($this->current);

        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">'.
            get_string('exblocks', 'etest').' '.
            $OUTPUT->help_icon('exblocks', 'etest').'</div>'.
            '<div class="felement" id="exblocksFields">');
        for ($i = 0; $i < count($this->current->exblock); $i++) {
            $mform->addElement('html',
                '<div id="exblock_array_'.$i.'" style="border:solid 1px blue; background-color:#DDDDDD" '.
                            'onclick="etest_show_exblock('.$i.')" align=left>'.
                    '<table cellpadding="2" width="100%" style="background-color:blue"><tr>'.
                        '<td align="left" style="color:#FFFFFF"><b>'.get_string('exblock', 'etest').' '.($i + 1).'</td>'.
                        '<td align="right">'.
                            ($i != 0 ? '<input type="submit" name="upexblock_'.$i.'" value="&uarr;" title="'.
                                get_string('upexblock', 'etest').'" style="min-width:3em"/>' : '').
                            ($i != count($this->current->exblock) - 1 ? '<input type="submit" name="downexblock_'.$i.
                                '" value="&darr;" title="'.get_string('downexblock', 'etest').'" style="min-width:3em"/>' : '').
                            '<input type="submit" name="delexblock_'.$i.'" value="Del" title="'.get_string('delexblock', 'etest').
                            '" onclick="return confirm(\''.get_string('delexblock_confirm', 'etest').'\')" style="min-width:3em"/>'.
                        '</td>'.
                    '</tr></table>');
            $mform->addElement('text', 'exblock_name_'.$i, get_string('exblockname', 'etest'), array('size' => '40'));
            $mform->addRule('exblock_name_'.$i, null, 'required', null, 'client');
            $mform->setType('exblock_name_'.$i, PARAM_TEXT);
            $minpointarray = array();
            $minpointarray[] =& $mform->createElement('text', 'exblock_minpoints_'.$i);
            $mform->setType('exblock_minpoints_'.$i, PARAM_INT);
            $minpointarray[] =& $mform->createElement('static', 'st_'.$i, '', '('.get_string('maxpoints', 'etest').': '.
                etest_exblock_maxpoints($this->current, $i).')');
            $mform->addGroup($minpointarray, 'minpointarray', get_string('exblockminpoints', 'etest'), array(' '), false);

            // Ex.
            for ($j = 0; $j < count($this->current->ex[$i]); $j++) {
                $mform->addElement('html',
                    '<div id="ex_array_'.$i.'_'.$j.'" style="border:solid 1px green; background-color:#DDDDDD;margin:5px;" '.
                                'colspan="2" onclick="etest_show_ex('.$i.', '.$j.')">'.
                            '<table cellpadding="2" width="100%" style="background-color:green"><tr>'.
                                '<td align="left" style="color:#FFFFFF"><b>'.get_string('ex', 'etest').' '.($j + 1).'</td>'.
                                '<td align="right">'.
                                    ($j != 0 ? '<input type="submit" name="upex_'.$i.'_'.$j.'" value="&uarr;" title="'.
                                        get_string('upex', 'etest').'" style="min-width:3em"/>' : '').
                                    ($j != count($this->current->ex[$i]) - 1 ? '<input type="submit" name="downex_'.$i.'_'.$j.
                                        '" value="&darr;" title="'.get_string('downex', 'etest').'" style="min-width:3em"/>' : '').
                                    '<input type="submit" name="delex_'.$i.'_'.$j.'" value="Del" title="'.
                                    get_string('delex', 'etest').'" onclick="return confirm(\''.
                                    get_string('delex_confirm', 'etest').'\')" style="min-width:3em"/>'.
                                '</td>'.
                            '</tr></table>');
                $mform->addElement('text', 'ex_name_'.$i.'_'.$j, get_string('exname', 'etest'), array('size' => '40'));
                $mform->addRule('ex_name_'.$i.'_'.$j, null, 'required', null, 'client');
                $mform->setType('ex_name_'.$i.'_'.$j, PARAM_TEXT);

                $pointarray = array();
                $pointarray[] =& $mform->createElement('text', 'ex_points_'.$i.'_'.$j);
                $mform->setType('ex_points_'.$i.'_'.$j, PARAM_INT);
                $pointarray[] =& $mform->createElement('checkbox', 'ex_pointscontinous_'.$i.'_'.$j, '',
                                     get_string('expointscontinous', 'etest'));
                $mform->addGroup($pointarray, 'minpointarray', get_string('expoints', 'etest'), array(' '), false);

                $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1, 'accepted_types' => '*');
                $mform->addElement('filemanager', 'exalt_'.$i.'_'.$j, get_string('exalt_file', 'etest'), null, $options);
                $mform->addHelpButton('exalt_'.$i.'_'.$j, 'exalt_file', 'etest');

                $mform->addElement('html',
                    '</div>');
            }
            $mform->addElement('html',
                '<div id="ex_add_'.$i.'" style="display:none">');
            $mform->addElement('submit', 'addex_'.$i, get_string('addex', 'etest'));
            $mform->addElement('html',
                    '</div>'.
                    '</div>');
        }

        $mform->addElement('submit', 'addexblock', get_string('addexblock', 'etest'));
        $mform->addElement('html', '</div></div>');

        $mform->addElement('checkbox', 'useExNames', get_string('useexnames', 'etest'), get_string('useexnames_explain', 'etest'));

        // Grades.
        $mform->addElement('header', 'grades', get_string('grades', 'etest'));
        // Grade area.
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">'.
                        get_string('grades', 'etest').' '.
                        '</div><div class="felement" id="gradeFields"></div></div>');

        // Final pages.
        $mform->addElement('header', 'finalpages', get_string('finalpages', 'etest'));

        $mform->addElement('checkbox', 'noResultDiagram', get_string('noresultdiagram', 'etest'),
            get_string('noresultdiagram_explain', 'etest'));

        $mform->addElement('checkbox', 'noPrintButton', get_string('noprintbutton', 'etest'),
            get_string('noprintbutton_explain', 'etest'));

        $mform->addElement('html', '<div id="id_printformArea" style="display:none">');
        $mform->addElement('editor', 'printform', get_string('printform', 'etest'));
        $mform->addHelpButton('printform', 'printform', 'etest');
        $mform->addElement('static', 'placeHolder', get_string('printformplaceholders', 'etest'),
            get_string('printform_explain', 'etest'));
        $mform->addElement('html', '</div>');

        // Hidden.
        $mform->addElement('hidden', 'redirectAdd', '');
        $mform->setType('redirectAdd', PARAM_TEXT);
        $mform->addElement('hidden', 'curExBlock', '-1');
        $mform->setType('curExBlock', PARAM_INT);
        $mform->addElement('hidden', 'curEx', '-1');
        $mform->setType('curEx', PARAM_INT);

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        // JS.
        $PAGE->requires->js('/mod/etest/edit.js');

        etest_expand_subuserdata($this->current);
        if ( $this->is_update() ) {
            etest_read_grade($this->current);
        } else {
            $this->current->grade = array();
        }
        $PAGE->requires->js_init_call('etest_settings_init',
            array(
                array(
                    'subuserFieldname' => get_string('subuserFieldname', 'etest'),
                    'subuserFieldmust' => get_string('subuserFieldmust', 'etest'),
                    'subuserFieldtype' => get_string('subuserFieldtype', 'etest'),
                    'subuserFieldcomment' => get_string('subuserFieldcomment', 'etest'),
                    'delsufield' => get_string('delsufield', 'etest'),
                    'textfield' => get_string('textfield', 'etest'),
                    'datefield' => get_string('datefield', 'etest'),
                    'combofield' => get_string('combofield', 'etest'),
                    'su_combodata' => get_string('su_combodata', 'etest'),
                    'subuser_new' => get_string('subuser_new', 'etest'),
                    'addsufield' => get_string('addsufield', 'etest'),
                    'subuserFieldname' => get_string('subuserFieldname', 'etest'),
                    'subuserFieldname' => get_string('subuserFieldname', 'etest'),
                    'points' => get_string('points', 'etest'),
                    'grade_shortname' => get_string('grade_shortname', 'etest'),
                    'grade_longname' => get_string('grade_longname', 'etest'),
                    'grade_addtext' => get_string('grade_addtext', 'etest'),
                    'grade_empty' => get_string('grade_empty', 'etest'),
                    'grade_new' => get_string('grade_new', 'etest'),
                    'addgrade' => get_string('addgrade', 'etest')
                ),
                $this->current->sudata,
                $this->current->grade,
                $this->is_update() ? etest_maxpoints($this->current) : 0,
                array(
                    optional_param('curExBlock', -1, PARAM_NUMBER),
                    optional_param('curEx', -1, PARAM_NUMBER),
                    optional_param('xCursor', 0, PARAM_NUMBER),
                    optional_param('yCursor', 0, PARAM_NUMBER)
                )
            ), true);
    }

    public function data_preprocessing(&$etest) {

        $etest['introref'] = 0;
        if ($this->is_add()) {
            $contextid = null;
            $etest['timelimit'] = 0;
            $etest['maxsession'] = 0;
            $etest['flags'] = ETEST_USEEXNAMES;
        } else {
            $contextid = $this->context->id;
        }
        $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1);
        file_prepare_draft_area($etest['introref'], $contextid, 'mod_etest', 'sourcefile', 0, $options);

        if ( isset($etest['flags']) ) {
            $etest['useSubusers'] = (($etest['flags'] & ETEST_USESUBUSERS) != 0);
            $etest['noResultDiagram'] = (($etest['flags'] & ETEST_NORESULTDIAGRAM) != 0);
            $etest['noPrintButton'] = (($etest['flags'] & ETEST_NOPRINTBUTTON) != 0);
            $etest['noContinueSession'] = (($etest['flags'] & ETEST_NOCONTINUESESSION) != 0);
            $etest['useExNames'] = (($etest['flags'] & ETEST_USEEXNAMES) != 0);
        }

        // Exercise data.
        if ( isset($this->current->exblock) ) {
            for ($i = count($this->current->exblock) - 1; $i >= 0; $i--) {
                $etest['exblock_name_'.$i] = $this->current->exblock[$i]->name;
                $etest['exblock_minpoints_'.$i] = $this->current->exblock[$i]->minpoints;
                for ($j = count($this->current->ex[$i]) - 1; $j >= 0; $j--) {
                    $etest['ex_name_'.$i.'_'.$j] = $this->current->ex[$i][$j]->name;
                    $etest['ex_points_'.$i.'_'.$j] = $this->current->ex[$i][$j]->points;
                    $etest['ex_pointscontinous_'.$i.'_'.$j] = (($this->current->ex[$i][$j]->flags & ETEST_EX_CONTINOUSPOINTS) != 0);
                    file_prepare_draft_area($etest['exalt_'.$i.'_'.$j], $contextid, 'mod_etest', 'sourcefile',
                        $this->current->ex[$i][$j]->id, $options);
                }
            }
        }

        // Timelimit.
        if ( $etest['timelimit'] == 0 ) {
            $etest['timelimit'] = '';
        }

        // Maxsession.
        if ( $etest['maxsession'] == 0 ) {
            $etest['maxsession'] = '';
        }

        // Printform.
        if ( isset($etest['printform']) && $etest['printform'] != '' ) {
            if ( preg_match("/^(\d)\|/", $etest['printform'], $b) === 1 ) {
                $etest['printform'] = array(
                    'format' => $b[1],
                    'text' => substr($etest['printform'], 2)
                );
            }
        }
    }

    /**
     * Detects if we are adding a new etest activity
     * as opposed to updating an existing one
     *
     * Note: we could use any of the following to detect add:
     *   - empty($this->_instance | _cm)
     *   - empty($this->current->add | id | coursemodule | instance)
     *
     * @return bool True if we are adding an new activity instance, false otherwise
     */
    public function is_add() {
        if (empty($this->current->instance)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Detects if we are updating a new etest activity
     * as opposed to adding an new one
     *
     * @return bool True if we are adding an new activity instance, false otherwise
     */
    public function is_update() {
        if (empty($this->current->instance)) {
            return false;
        } else {
            return true;
        }
    }

    public function get_subuserdata() {
        $res = array();
        $sm = $this->_form->_submitValues;
        for ($i = 0; isset($this->_form->_submitValues['su_type_'.$i]); $i++) {
            if ( !isset($mform->{"su_del_".$i}) ) {
                $res[] = array(
                    "name" => $sm['su_name_'.$i],
                    "type" => $sm['su_type_'.$i],
                    "must" => isset($sm['su_must_'.$i]),
                    "comment" => $sm['su_comment_'.$i],
                    "data" => $sm['su_data_'.$i]);
            }
        }
        return $res;
    }

    public function get_gradedata() {
        $res = array();
        $sm = $this->_form->_submitValues;
        for ($i = 0; isset($this->_form->_submitValues['grade_minpoints_'.$i]); $i++) {
            $grade = new stdClass;
            $grade->id = $sm['grade_id_'.$i];
            $grade->minpoints = $sm['grade_minpoints_'.$i];
            $grade->shortname = $sm['grade_shortname_'.$i];
            $grade->longname = $sm['grade_longname_'.$i];
            $grade->addtext = $sm['grade_addtext_'.$i];
            $res[] = $grade;
        }
        return $res;
    }

    public function get_exercisedata(stdClass &$etest) {
        global $DB, $fromform;
        $sm = $this->_form->_submitValues;
        $curexblock = $sm['curExBlock'];
        $curex = $sm['curEx'];
        $reload = false;
        if ( isset($etest->exblock) ) {
            for ($i = count($etest->exblock) - 1; $i >= 0; $i--) {
                $etest->exblock[$i]->name = $sm['exblock_name_'.$i];
                $etest->exblock[$i]->minpoints = $sm['exblock_minpoints_'.$i];
                if ( isset($sm['upexblock_'.$i]) ) {
                    $v = $etest->exblock[$i]->pos;
                    $etest->exblock[$i]->pos = $etest->exblock[$i - 1]->pos;
                    $etest->exblock[$i - 1]->pos = $v;
                    $curexblock--;
                    $reload = true;
                }
                if ( isset($sm['downexblock_'.$i]) ) {
                    $v = $etest->exblock[$i]->pos;
                    $etest->exblock[$i]->pos = $etest->exblock[$i + 1]->pos;
                    $etest->exblock[$i + 1]->pos = $v;
                    $curexblock++;
                    $reload = true;
                }
                if ( isset($sm['delexblock_'.$i]) ) {
                    // Delete in DB.
                    $DB->delete_records("etest_exblock", array('id' => $etest->exblock[$i]->id));
                    for ($k = 0; $k < count($etest->ex[$i]); $k++) {
                        $DB->delete_records("etest_ex", array('id' => $etest->ex[$i][$k]->id));
                        $DB->delete_records("etest_exalt", array('ex' => $etest->ex[$i][$k]->id));
                    }
                    // Cascade arrays.
                    $l = count($etest->exblock) - 1;
                    for ($k = $i; $k < $l; $k++) {
                        $etest->exblock[$k] = $etest->exblock[$k + 1];
                        $etest->ex[$k] = $etest->ex[$k + 1];
                        $etest->exalt[$k] = $etest->exalt[$k + 1];
                        $etest->exblock[$k]->pos = $k;
                    }
                    unset($etest->exblock[$l]);
                    unset($etest->ex[$l]);
                    unset($etest->exalt[$l]);
                    $curexblock = -1;
                    $reload = true;
                    continue;
                }
                // Ex.
                for ($j = count($etest->ex[$i]) - 1; $j >= 0; $j--) {
                    $etest->ex[$i][$j]->name = $sm['ex_name_'.$i.'_'.$j];
                    $etest->ex[$i][$j]->points = $sm['ex_points_'.$i.'_'.$j];
                    $etest->ex[$i][$j]->flags = isset($sm['ex_pointscontinous_'.$i.'_'.$j]) ? ETEST_EX_CONTINOUSPOINTS : 0;
                    if ( isset($sm['upex_'.$i.'_'.$j]) ) {
                        $v = $etest->ex[$i][$j]->pos;
                        $etest->ex[$i][$j]->pos = $etest->ex[$i][$j - 1]->pos;
                        $etest->ex[$i][$j - 1]->pos = $v;
                        $curex--;
                        $reload = true;
                    }
                    if ( isset($sm['downex_'.$i.'_'.$j]) ) {
                        $v = $etest->ex[$i][$j]->pos;
                        $etest->ex[$i][$j]->pos = $etest->ex[$i][$j + 1]->pos;
                        $etest->ex[$i][$j + 1]->pos = $v;
                        $curex++;
                        $reload = true;
                    }
                    if ( isset($sm['delex_'.$i.'_'.$j]) ) {
                        // Delete in DB.
                        $DB->delete_records('etest_ex', array('id' => $etest->ex[$i][$j]->id));
                        $DB->delete_records('etest_exalt', array('ex' => $etest->ex[$i][$j]->id));
                        // Cascade arrays.
                        $l = count($etest->ex[$i]) - 1;
                        for ($k = $j; $k < $l; $k++) {
                            $etest->ex[$i][$k] = $etest->ex[$i][$k + 1];
                            $etest->exalt[$i][$k] = $etest->exalt[$i][$k + 1];
                            $etest->ex[$i][$k]->pos = $k;
                        }
                        unset($etest->ex[$i][$l]);
                        unset($etest->exalt[$i][$l]);
                        $curex = -1;
                        $reload = true;
                        continue;
                    }
                    // Exalt.
                    if ( isset($sm['addexalt_'.$i.'_'.$j]) && isset($sm['ref_'.$i.'_'.$j]) ) {
                        $exalt_ = (object)null;
                        $exalt_->ex = $etest->ex[$i][$j]->id;
                        $exalt_->location = $etest->{'loc_'.$i.'_'.$j};
                        $exalt_->reference = $etest->{'ref_'.$i.'_'.$j};
                        $etest->exalt[$i][$j][count($etest->exalt[$i][$j])] = $exalt_;
                    }

                    // New adding way.
                    for ($k = 0; true; $k++) {
                        if ( !isset($etest->{'exalt_newref_'.$i.'_'.$j.'_'.$k}) ) {
                            break;
                        }
                        $exalt_ = (object)null;
                        $exalt_->ex = $etest->ex[$i][$j]->id;
                        $exalt_->location = $etest->{'exalt_newloc_'.$i.'_'.$j.'_'.$k};
                        $exalt_->reference = $etest->{'exalt_newref_'.$i.'_'.$j.'_'.$k};
                        $etest->exalt[$i][$j][] = $exalt_;
                    }
                }
                // Add exercise.
                if ( optional_param('addex_'.$i, 0, PARAM_RAW) ) {
                    $j = count($etest->ex[$i]);
                    $ex_ = (object)null;
                    $ex_->exblock = $etest->exblock[$i]->id;
                    $ex_->name = 'Neue Aufgabe';
                    $ex_->points = 1;
                    $ex_->pos = $j;
                    $etest->ex[$i][$j] = $ex_;
                    $etest->exalt[$i][$j] = array();
                    $curex = $j;
                    $reload = true;
                }

            }
        }

        if ( optional_param('addexblock', 0, PARAM_RAW) ) {
            if ( !isset($etest->exblock) ) {
                $etest->exblock = array();
                $etest->ex = array();
                $etest->exalt = array();
            }
            $i = count($etest->exblock);
            $exblock1 = (object)null;
            $exblock1->etest = isset($etest->id) ? $etest->id : 0;
            $exblock1->name = get_string('new_block', 'etest');
            $exblock1->minpoints = 0;
            $exblock1->pos = $i;
            $etest->exblock[$i] = $exblock1;
            $etest->ex[$i] = array();
            $etest->exalt[$i] = array();
            $curexblock = $i;
            $curex = -1;
            $reload = true;
        }
        if ( $reload ) {
            $fromform->submitbutton = true;
            $_SESSION['ETEST_MOD_FORM_RELOAD'] =
                $etest->redirectAdd.
                "&curExBlock=".$curexblock.
                "&curEx=".$curex;
        }
    }

}
