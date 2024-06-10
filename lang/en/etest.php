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
 * Language strings for german.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General strings.
$string['etest'] = 'E-Test';

$string['modulenameplural'] = 'E-Tests';
$string['modulename'] = 'E-Test';

$string['modulename_help'] = 'The E-Test module enables the teacher to build sequences of exercises (made by EF-Editor) that can be attempted under controled conditions.  '.
'For the exercises/tasks you can assign small pools of exercises from which the exercise to attempt is choosen randomly.

Information about E-Test can be found under  '.'
[http://studierplatz2000.tu-dresden.de](http://studierplatz2000.tu-dresden.de "Studierplatz Homepage")';
$string['modulename_link'] = 'mod/etest/view';

$string['pluginadministration'] = 'E-Test administration';
$string['pluginname'] = 'E-Test';

// Access rights.
$string['etest:addinstance'] = 'Add a new E-Test';
$string['etest:attempt'] = 'Ability to attempt the E-Test';
$string['etest:viewmyattempts'] = 'View the own attempts';
$string['etest:viewallattempts'] = 'View anyone\'s attempts';
$string['etest:deleteattempts'] = 'Delete attempts';
$string['etest:addpoints'] = 'Assign extra points';
$string['etest:recalc'] = 'Recalc an E-Test';
$string['etest:archiveattempt'] = 'Archive attempts';
$string['etest:viewarchive'] = 'View archived attempts';

// Mod form.
$string['introref'] = 'File for intro page';
$string['introref_help'] = 'Here you can upload a HTML-file, that will be displayed immediately on opening the E-Test. It should contain instructions, comments, links to example tasks etc.  '.'
Additional files (immages, example tasks) have to be also uploaded and the links from the HTML-file have to be relative. If you upload more than one file please mark the HTML-file as main file.';

$string['sessionhandling'] = 'Session handling / subusers';

// Subuser.
$string['subuser'] = 'Subuser';
$string['useSubuser'] = 'Use subuser';
$string['useSubuser_help'] = 'If several persons shall use an E-Test via only one login
(i.e. also guest login), it makes sense to collect additional data about the persons.  '.'
Here you can define which additional data has to be entered.
When somebody starts the E-Test, then he/she will be requested to enter these data.';
$string['subuserLogData'] = 'Data that shall be collected at the start';
$string['subuserFieldname'] = 'Description';
$string['subuserFieldmust'] = 'Required';
$string['subuserFieldtype'] = 'Type';
$string['subuserFieldcomment'] = 'Comment';
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['birthday'] = 'Birthday';
$string['dd.mm.yyyy'] = '(dd.mm.yyyy)';
$string['subuser_new'] = 'Here you can define a new data field.';
$string['textfield'] = 'Textfield';
$string['datefield'] = 'Date';
$string['combofield'] = 'Combobox';
$string['addsufield'] = 'Add new data field';
$string['delsufield'] = 'Erase data field';
$string['su_combodata'] = 'Entries of combobox';
$string['subuserdata'] = 'Data of subusers';
$string['subuserdata_help'] = 'For these data you can define:

* the description (short name)
* whether the entry is required or optional
* the type (free text field, date field or combobox)
* an explaining comment, that is displayed to the users

First name, last name and birthday are provided by default
(if you don\'t want to collect these data simply delete these field via the "Del"-button at the beginning of the line).  '.'
To define a new data field enter the appropriate values in the lower line and confirm by "Add"-button.';
$string['combodef'] = 'Define comboboxes';
$string['combodef_help'] = 'To define comboboxes simply select the type "Combobox".  '.'
Now you can enter the values within the new opening text area below, that will be displayed as entries of the combobox.  '.'
Every line represents an entry';
$string['subuserprotname'] = 'Display within protocol';
$string['subuserprotname_help'] = 'Here you can define from wich subuser data the display name of a person is composed.
Simply enter the names of the desired data fields surrounded by \'$\'.  '.'
If you have e.g. the data fields \'First name\', \'Last name\' and \'Form\' and you provide for the display
> *$Last name$, $First name$, ($Form$)*

then the users will be displayed as e.g.
> Mueller, Nina (4b)

.  '.'
If you insert *$USER$*, then you get the name of the "main user" und *$SESSION$* deliveres the session-number/id.';

$string['resultpage'] = 'Result page';
$string['noresultdiagram'] = '<b>No</b> result chart';
$string['noresultdiagram_explain'] = 'display <b>no</b> no bar chart of results at result page';
$string['noprintbutton'] = '<b>No</b> print-button';
$string['noprintbutton_explain'] = 'display <b>no</b> print-button at result page';

$string['timelimit'] = 'Time limit';
$string['timelimit_help'] = 'Time limit for an attempt (in minutes). No value or 0 means no limit.';

$string['maxsession'] = 'Maximum attempts';
$string['maxsession_help'] = 'How often a user or subuser may is allowed to attempt the test. No value means no limit.';

$string['nocontinuesession'] = 'No resuming';
$string['nocontinuesession_explain'] = 'Interrupted sessions can **not** be continued.';

$string['alwaysopen'] = 'Always open';
$string['neverclosed'] = 'Never closed';
$string['specifictime'] = 'Specific time';

$string['exercises'] = 'Exercises';
$string['exblock'] = 'Exercise block';
$string['exblocks'] = 'Exercise blocks';
$string['exblocks_help'] = 'Several exercises that belong together (e.g. with respect to contents) will be combined to exercise blocks,
e.g. "Grammar", "Translation".
Later in the menu of the E-Test they will appear as main items with sub-items (the exercises).  '.'
For such blocks you can also assign minimum points.
If the student fails to reach this number of points within this block he/she will be judged one grade worse automatically.';
$string['upexblock'] = 'Move exercise block upwards';
$string['downexblock'] = 'Move exercise block downwards';
$string['delexblock'] = 'Delete exercise block';
$string['delexblock_confirm'] = 'Shall this exercise block really be deleted?';
$string['exblockname'] = 'Name';
$string['exblockminpoints'] = 'Minimum points';
$string['maxpoints'] = 'Maximal points';
$string['addexblock'] = 'Add new exercise block';
$string['expointscontinous'] = 'relative point assignment';
$string['new_block'] = 'New block';

$string['ex'] = 'Exercise';
$string['upex'] = 'Move exercise upwards';
$string['downex'] = 'Move exercise downwards';
$string['delex'] = 'Delete exercise';
$string['delex_confirm'] = 'Do you really want to remove this exercise?';
$string['exname'] = 'Name';
$string['expoints'] = 'Points';
$string['addex'] = 'Add new exercise';

$string['exalt_file'] = 'Exercise files';
$string['exalt_file_help'] = 'Here you can upload several exercise files...TODO';

$string['useexnames'] = 'Use exercise names';
$string['useexnames_explain'] = ' display exercise names in test, otherwiese generic names "Exercise <i>x</i>" are used';

$string['grades'] = 'Grades';
$string['points'] = 'Points';
$string['grade_shortname'] = 'Short name';
$string['grade_longname'] = 'Long name';
$string['grade_addtext'] = 'Additional text';
$string['delgrade'] = 'Delete grade';
$string['delgrade_confirm'] = 'Shall this grade really be deleted?';
$string['grade_empty'] = 'No grade assigned.';
$string['grade_new'] = 'Here you can assign a new grading:';
$string['addgrade'] = 'Add grade';

$string['finalpages'] = 'Result pages';
$string['printform'] = 'Alternative "print page"';
$string['printform_help'] = 'Here you can input an alternatve layout for the print page at the end.  '.'
You may also include images via external links etc.
The actual data of the user is inserted via placeholders.  '.
'If you leave this field blank, a standard page with the user date is used.';
$string['printformplaceholders'] = 'Placeholders';
$string['printform_explain'] =
    'You can use the following placeholders:<br>'.
    '<table border="0" cellpadding="2">'.
    '<tr><td>$FIRSTNAME$ ... first name</td><td>&nbsp;</td><td>$COURSENAME$ ... long name of reached grade</td></tr>'.
    '<tr><td>$LASTNAME$ ... last name</td><td>&nbsp;</td><td>$ADDTEXT$ ... additional text of grade</td></tr>'.
    '<tr><td>$BIRTHDAY$ ... birthday</td><td>&nbsp;</td><td>$RESULTLIST$ ... list with reached points within blocks</td></tr>'.
    '<tr><td>$TITLE$ ... titel of E-Test</td><td>&nbsp;</td><td>$USERDATA$ ... table with user data</td></tr>'.
    '<tr><td>$DATE$ ... current date</td><td>&nbsp;</td><td>$<i>description</i>$ or $USER0$, $USER1$ etc. ... entered values of subuser data</td></tr>'.
    '</table>';

// Settings page.
$string['usedesignsfortest'] = 'Use Moodle desings for embedded pages whithin E-Test';
$string['usedesignsfortest_explain'] = 'Sometimes Moodle desings add undesired margins an borders to embeded pages. With this option you can decide whether moodle design shall be applied to these pages.';
$string['usedesignsforprot'] = 'Use Moodle desings for embedded pages whithin protocol';
$string['usedesignsforprot_explain'] = 'Sometimes Moodle desings add undesired margins an borders to embeded pages. With this option you can decide whether moodle design shall be applied to these pages.';

// Index page.
$string['subusers'] = 'Subusers';
$string['sessions'] = 'Sessions';
$string['archivedusers'] = 'Archived users';

$string['protocol'] = 'Protocol';

// View page.
$string['startbutton'] = 'Start E-Test';
$string['START'] = 'START';

// On attempt.
$string['BACK'] = 'BACK';
$string['FINISH_TEST'] = 'FINISH TEST';
$string['NEXT'] = 'NEXT';
$string['remaining_info1'] = 'You have still {$a} minute left.';
$string['remaining_info2'] = 'You have still {$a} minutes left.';
$string['ptsOf'] = 'pts. of ';

$string['intro'] = 'Introduction';
$string['exercises'] = 'Exercises';

$string['finish_test'] = 'Finish test';
$string['result'] = 'Result';

// Subuser login.
$string['fillFields'] = 'Please fill in the form fields.';
$string['dateComment'] = '(dd.mm.yyyy)';
$string['loginButton'] = 'Next';
$string['combo_please_select'] = 'Please choose!';
$string['missingField'] = 'Please fill in the form field \'{$a}\'.';
$string['formatField'] = 'Please check the form field \'{$a}\'. It does not match the expected format.';
$string['missingSelect'] = 'Please select in the form field \'{$a}\' a valid option.';

// Login page.
$string['ses_exists1'] = 'Attempted at {$a}';
$string['ses_exists0'] = 'Following sessions could be continued:';
$string['ses_exists3'] = 'But you can also start a new session.';
$string['ses_exist'] = 'For the user <b>{$a}</b> there are already existing sessions/attepts.';
$string['ses_continue_button'] = 'Continue this session';
$string['ses_new_button'] = 'Start a new E-Test session';
$string['ses_cancel_button'] = 'Cancel, back to login';
$string['maxSessionReached'] = 'Note: You can not start a further attempt on this E-Test. This E-Test may be attempted only {$a} times per person.';

$string['testOver'] = 'Test already finished';
$string['testOver_explain'] = 'For these user data the E-Test is already finished. Therefore it can not be continued.';

// Cancel page.
$string['cancel_question'] = 'Do you really want to quit the test?';
$string['cancel_state'] = '(You have #SOLVED# of #TOTAL# exercises attempted.)';
$string['cancel_button_yes'] = 'Yes, I want to quit the test now.';
$string['cancel_button_no'] = 'No, I want to be back to the exercises.';

// Final page.
$string['print_button'] = 'Print result';
$string['points_of'] = '#POINTS# of #TOTALPOINTS# points';
$string['total'] = 'Total';
$string['correctly_solved'] = 'correctly solved';
$string['partially_solved'] = 'partially solved';
$string['wrong_solved'] = 'not correctly solved';
$string['not_solved'] = 'not attepmted';

// Print page.
$string['print1'] = 'You have finished the ebntrance test and reached following level:';

// Report.

// Header.
$string['userlist'] = 'User list';
$string['courselist'] = 'Grade list';
$string['leveling'] = 'Leveling';
$string['userhist'] = 'History';
$string['recalc'] = 'Recalc';
$string['exoverview'] = 'Exercise oveview';
$string['exentries'] = 'Exercise entries';
$string['usermatrix'] = 'User matrix';

$string['displayonpage'] = 'Display (HTML)';
$string['downloadtext'] = 'Text-Export (CSV)';
$string['downloadexcel'] = 'Excel-Export';
$string['refresh'] = 'Refresh';

// User list.
$string['duration'] = 'Duration';
$string['worse'] = 'worse';
$string['origPts'] = 'orig. pt.';
$string['origGrade'] = 'orig. grade';
$string['recalcPts'] = 'recalc pt.';
$string['recalcGrade'] = 'recalc grade';
$string['oldPts'] = 'old lev. pt.';
$string['oldGrade'] = 'old lev. grade';

$string['orig-data'] = 'orig. data';
$string['recalc-data'] = 'recalc data';

$string['use_as_startdate'] = 'Use this date as \'from:\'-date to limit the users.';
$string['use_as_enddate'] = 'Use thsi date as \'to:\'-date to limit the users.';

$string['delete'] = 'Delete';
$string['delete_explain'] = 'the marked sessions';
$string['deleteempty'] = 'No session selected for deletion.';
$string['deleteverify'] = 'Should this {$a} sessions really be (unrecoverably) deleted?';
$string['deletedone'] = '{$a} sessions have been (unrecoverably) deleted.';
$string['cancel'] = 'Cancel';

$string['archivetag'] = 'Archive label';
$string['archive'] = 'Archive';
$string['archive_explain'] = 'the marked sessions';
$string['archiveempty'] = 'No session selected for archivation.';
$string['archiveverify'] = 'Should this {$a} sessions really be archived and removed from the list?';
$string['archiveselecttag'] = 'Please label these session(s) with a tag, so that these sessions can be easily found again in archive.';
$string['archiveselecttagnew'] = 'Create a new tag:';
$string['archiveselecttagold'] = 'or use an existing:';
$string['archivetagneeded'] = 'Please specify an archive label.';
$string['archivetagonlyone'] = 'You can specify a new archive label or select an existing, bot not both at the same time.';
$string['archivedone'] = '{$a->count} sessions have been archived with the label "{$a->label}".';
$string['displayarchive'] = 'Display archive';
$string['displayarchive_explain'] = 'for the following tag(s):';
$string['noarchivetagsselected'] = 'No archive tags selected.';
$string['leavearchive'] = 'Leave archive';
$string['leavearchive_explain'] = '';
$string['unarchive'] = 'Unarchive';
$string['unarchive_explain'] = 'the marked sessions';
$string['unarchiveempty'] = 'No session selected for unarchivation.';
$string['unarchiveverify'] = 'Should this {$a} sessions really be unarchived?';
$string['unarchivedone'] = '{$a} sessions have been unarchived.';

// User hist.
$string['nodata'] = 'No data.';
$string['tdelta_in_s'] = 't<sub>delta</sub> (in s)';
$string['action'] = 'Action';
$string['comment'] = 'Comment / data';
$string['exercise'] = 'Exercise';
$string['finalsend'] = 'Final Send';
$string['text'] = 'Text';
$string['solutionDisplayed'] = 'Solution displayed';

// Course list.
$string['points'] = 'points';

// Exercise overview.
$string['exalt'] = 'file no.';
$string['file'] = 'file';
$string['nSelected'] = "#assigned";
$string['nSolved'] = "#attempted";
$string['nCorrectSolved'] = "#solved correctly";
$string['averageSolved'] = "&Oslash;solved correctly";
$string['averagePercent'] = "&Oslash;solution percent";
$string['show_exercise'] = "Display exercise";

// Exercise entries.
$string['exfield'] = 'Input field';
$string['nusers'] = 'Number of users';
$string['exfieldentry'] = 'Entry in input field';
$string['exfieldresult'] = 'Single solution percent';

// Grouping.
$string['grouping'] = 'Grouping';
$string['grouping_no'] = 'no grouping Gruppierung';
$string['grouping_yes'] = 'Gropu by: ';
$string['group'] = 'Group';
$string['groupingText1'] = 'Please select, how the data should be groupped:';

// Logging.
$string['attempt_started'] = 'E-Test started';

