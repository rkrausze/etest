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
 * This is the protocol header frame.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$eventdata = array();
$eventdata['objectid'] = $etest->id;
$eventdata['context'] = $context;
$eventdata['courseid'] = $course->id;

$event = \mod_etest\event\protocol_viewed::create($eventdata);
$event->trigger();

$PAGE->set_url('/mod/etest/prot/prot.php', array('id' => $cm->id));
$PAGE->set_title(format_string($etest->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('protocol', 'etest'));
$PAGE->set_pagelayout('standard');
$PAGE->blocks->show_only_fake_blocks();
$PAGE->requires->js('/mod/etest/prot/ef_restore.js');

$sOut = $OUTPUT->header();
$i1 = strpos($sOut, '<div id="page"');
if ( $i1 !== FALSE )
{
  $i2 = strpos($sOut, '>', $i1);
  $sOut = substr($sOut, 0, $i2+1);
}
$sOut = str_replace('</head>',
'<style type="text/css">
	td, p { font-size:10pt;font-family:Arial;font-weight:bold }
	body, .path-mod-stupla-prot, #page-header, #page-content { background-color:#FFFF99; }
	#page-content .region-content { overflow: hidden; padding: 0; }
	body.drawer-open-left { margin-left: 0; }
	footer { display: none; }
	#page-mod-etest-prot-prot .drawer { display: none; }
	#page-mod-etest-prot-prot { margin-left: auto; }
    #page { margin-left: 0 !important; margin-right: 0 !important; padding-left: 16px !important; padding-right: 16px !important; margin-top: 69px !important;}
</style>'.
'</head>', $sOut);
echo str_replace('<a ', '<a target="_top" ', $sOut);

$sessions = etest_prot_get_sessions($etest);

?>
<style type="text/css">
    td, p { font-size:10pt;font-family:Arial;font-weight:bold }
    body, .path-mod-etest-prot, #page-header, #page-content { background-color:#FFFF99; }
    #page-content .region-content { overflow: hidden; padding: 0; }
    body.drawer-open-left { margin-left: 0; }
    footer { display: none; }
    #page-mod-etest-prot-prot .drawer { display: none; }
    #page-mod-etest-prot-prot { margin-left: auto; }
</style>
<form name="f" target="data" action="prot_userlist.php">
<input type="hidden" name="a" value="<?php echo $etest->id ?>">
<input type="hidden" name="sortinfo" value="<?php echo $sortinfo ?>">
<input type="hidden" name="exaltid" value="<?php echo $exaltid ?>">
<input type="hidden" name="groupSql" value="<?php echo $groupsql ?>">
<input type="hidden" name="groupText" value="<?php echo $grouptext ?>">
<input type="hidden" name="groupData" value="<?php echo $groupdata ?>">
<input type="hidden" name="inArchive" value="">
<table border="0" width="100%">
    <tr>
        <td>
            <select name="protaction" onchange="Action(this.value)">
                <option value="UserList"><?php print_string('userlist', 'etest'); ?></option>
                <option value="CourseList"><?php print_string('courselist', 'etest'); ?></option>
                <option value="Leveling"><?php print_string('leveling', 'etest'); ?></option>
                <option value="UserHist"><?php print_string('userhist', 'etest'); ?></option>
                <option value="Recalc"><?php print_string('recalc', 'etest'); ?></option>
                <option value="ExercisesOverview"><?php print_string('exoverview', 'etest'); ?></option>
                <option value="ExercisesEntries"><?php print_string('exentries', 'etest'); ?></option>
                <!--option value="ExCont">< ?php print_string('exercisesII', 'etest'); ?></option>
                <option value="ExOverview">< ?php print_string('exercises overview', 'etest'); ?></option-->
            </select>
            ab: <?php etest_report_date_selector("from", $from != 0 ? $from : "0", $sessions) ?>
            bis: <?php etest_report_date_selector("till", $till != 0 ? $till : 'today', $sessions) ?>
            <select name="format" onchange="Action(Mode)">
                <option value="showashtml"><?php print_string('displayonpage', 'etest'); ?></option>
                <option value="downloadascsv"><?php print_string('downloadtext', 'etest'); ?></option>
                <option value="downloadasexcel"><?php print_string('downloadexcel', 'etest'); ?></option>
            </select>
            <input type="button" value="<?php print_string('refresh', 'etest')?>" onclick="Action(f.protaction.value)">
            <input type="button" value="<?php print_string('grouping', 'etest')?>" onclick="Grouping()"
                title="<?php echo $grouptext == "" ? get_string("grouping_no", "etest") :
                    get_string("grouping_yes", "etest").$grouptext ?>"
            <?php echo ($grouptext != "" ) ? ' style="background-color:#DDFF55"' : '' ?> id="groupingButton">
        </td>
        <td align="right" valign="bottom">
            <?php echo get_string("users")?>
            : <select size="1" name="user" onChange="Action(Mode)">
<?php
foreach ($sessions as $session) {
    echo "   <option value=\"$session->id\" ".($session->id == $sessionid ? " selected" : "").">$session->displayname</option>";
}
?>
            </select>
        </td>
    </tr>
</table>
</form>
<script type="text/javascript">
//<![CDATA[

var f = document.forms['f'];
var PHP = "";

var Mode = "UserList";
var ListHeight = "66%";

function Action(mode)
{
  SaveListHeight();
  Mode = mode;
  eval(Mode+"()");
}

function SaveListHeight()
{
  if ( top.data.list )
    ListHeight = document.all ? top.data.list.document.body.offsetHeight : top.data.list.innerHeight;
}

function TableSort(info)
{
    var f1 = (Mode == "UserList") ? top.data.document.forms['fm'] : f;
    f1.sortinfo.value = info;
    f1.target = "data";
    f1.submit();
}

var User = new Array(
<?php
$first = true;
foreach ($sessions as $session) {
    echo $first ? "" : ",", "new Array($session->id, '", addslashes($session->displayname), "')";
    $first = false;
}
?>
);

// Login -----------------------

function UserList()
{
   // window.open(PHP+"prot_userlist.php?a=<?php echo $etest->id ?>", "data");
   f.action = "prot_userlist.php";
   f.target = "data";
   f.sortinfo.value = "";
   f.submit();
}

// Kurs-Listen ------------------

function CourseList()
{
  //window.open(UserPath+f.s2.options[f.s2.selectedIndex].text+'/login.txt', "data");
   f.action = "prot_courselist.php";
   f.target = "data";
   f.sortinfo.value = "";
   f.submit();
}

// Leveling -----------------------

function Leveling()
{
   //window.open(PHP+"leveling.php?a=<?php echo $etest->id ?>", "data");
   f.action = "leveling.php";
   f.target = "data";
   f.sortinfo.value = "";
   f.submit();
}

// UserHist -----------------------

function UserHist()
{
//  window.open(PHP+"prot_hist.php?User="+escape(f.user.options[f.user.selectedIndex].text)+"&AppName="+AppName, "data");
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.writeln('<frameset rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
    '<frame src="'+PHP+"prot_hist.php?sessionid="+f.user.options[f.user.selectedIndex].value+'&a=<?php echo $etest->id ?>"'+
    ' name="list" frameborder="1" framespacing="2">',
    '<frame src="about:blank" name="display">',
    '</frameset>');
  doc.close();
  f.action = "prot_hist.php";
  f.target = "data";
}

// SwitchAll

function SwitchAll(chk) {
    var inp = top.data.document.getElementsByTagName("input");
    for (var i = 0; i < inp.length; i++)
        if ( (""+inp[i].name).substr(0, 3) == 'cb_' )
            inp[i].checked = chk;
}

// Start/Enddate

function StartDate(date) {
  for (var i = 0; i < f.from.options.length; i++)
    if ( date == f.from.options[i].value )
    {
      f.from.selectedIndex = i;
      if ( f.action )
      {
        f.target = "data";
        f.submit();
      }
      break;
    }
}

function EndDate(date) {
  for (var i = 0; i < f.till.options.length; i++)
    if ( date == f.till.options[i].value )
    {
      f.till.selectedIndex = i;
      if ( f.action )
      {
        f.target = "data";
        f.submit();
      }
      break;
    }
}
// Recalc -----------------------

var onRecalc = false;
var nrRecalc = 0;
function Recalc()
{
  if ( onRecalc == false )
  {
    if ( window.confirm("Die Neubewertung der Einträge der Nutzer in die Aufgaben wird jetzt gestartet.") == false )
      return;
    nrRecalc = 0;
    onRecalc = true;
  }
  if ( nrRecalc >= f.user.length )
  {
    onRecalc = false;
    alert("Fertig.");
  }
  else
  {
    var doc = top.data.document;
    doc.close();
    doc.open();
    doc.writeln('<frameset rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
      '<frame src="'+PHP+"prot_recalc1.php?sessionid="+f.user[nrRecalc].value+"&a=<?php echo $etest->id ?>"+
          (nrRecalc == 0 ? "&first=1": "")+'" name="list" frameborder="1" framespacing="2">',
      '<frame src="about:blank" name="display">',
      '</frameset>');
    doc.close();
    nrRecalc++;
  }
}

// ExercisesOverview -----------------------

function ExercisesOverview()
{
   f.action = "prot_exoverview.php";
   f.target = "data";
   f.sortinfo.value = "";
   f.submit();
}

// ExercisesEntries -----------------------

function ExercisesEntries()
{
   f.action = "prot_exlist.php";
   f.target = "data";
   f.sortinfo.value = "";
   f.exaltid.value = "";
   f.submit();
}

// Exercise ---------------------------------

function ExerciseEntriesSingle(exaltid)
{
//  window.open(PHP+"prot_hist.php?User="+escape(f.user.options[f.user.selectedIndex].text)+"&AppName="+AppName, "data");
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.writeln('<frameset rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
    '<frame src="about:blank" name="list" frameborder="1" framespacing="2">',
    '<frame src="about:blank" name="display">',
    '</frameset>');
  doc.close();
  f.target = "list";
  f.action = "prot_exlist.php";
  f.sortinfo.value = "";
  f.exaltid.value = exaltid;
  f.submit();
}

// Exercises Content -----------------------

var ExNr = 0;
function ExCont()
{
  SaveListHeight();
/*  alert(Name);
  var src = "";
  var count = ExNr;
  for (var topic = 0; topic < Name.length; topic++)
    if ( Media[M_AUFGABE][topic] )
      for (var i = 0; i < Media[M_AUFGABE][topic].length; i++)
        if ( count-- == 0 )
          src = Media[M_AUFGABE][topic][i][0];*/
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.writeln('<frameset rows="', ListHeight, ',*" border="2" frameborder="1" framespacing="2">',
    '<frame src="'+PHP+'prot_excont.php?a=<?php echo $etest->id ?>&ExNr='+ExNr+'" name="list" frameborder="1" framespacing="2">',
    '<frame src="about:blank" name="display">',
    '</frameset>');
  doc.close();
}

// ExOverview -----------------------

var GroupNr = 0;
function ExOverview()
{
  SaveListHeight();
  window.open(PHP+'prot_exoverview.php?a=<?php echo $etest->id ?>&GroupNr='+GroupNr+'&Width='+
    (document.layer ? self.innerWidth : self.document.body.offsetWidth), "data");
}

// aus den Tabellen

function SelectHist(user)
{
    SelectOption(f.user, user);
    SelectOption(f.protaction, "UserHist");
    Action("UserHist");
}

function SelectOption(obj, value)
{
  for (var i = 0; i < obj.options.length; i++)
    if ( value == obj.options[i].value )
    {
      obj.selectedIndex = i;
      break;
    }
}

function empty()
{
  var doc = top.data.document;
  doc.close();
  doc.open();
  doc.write("under construction");
  doc.close();
}

// grouping

function Grouping() {
    var win = window.open("prot_grouping.php?a="+f.a.value+"&groupData="+f.groupData.value, "grouping",
        "width=400,height=300,top=100,left=100");
    win.opener = this;
}

function GroupingReturn(sql, text, data) {
    f.groupSql.value = sql;
    f.groupText.value = text;
    f.groupData.value = data;
    document.getElementById('groupingButton').title =
        text == "" ? "<?php print_string("grouping_no", "etest")?>"
            : "<?php print_string("grouping_yes", "etest")?>"+text;
    document.getElementById('groupingButton').style.backgroundColor =
        text != "" ? "#DDFF55" : "";
    f.target = 'data';
    f.submit();
}

// EF-Zeug f�r Vorschau Aufgabe II

// aufgabe ---------------------------------------------------------------------------------

/*function HintWindow(title, text)
{
  NewDia(title,
    '<FONT color="#CC0000"><B>' + title + ':</B></FONT><P>' +
    text +
    '<P align=right>\r\n' +
    '<INPUT type="BUTTON" value="Schlie�en" onClick="self.close()"></P>',
    " ",
    420, 250);
}*/

var DIALOG_HEADER = '<BODY text="#003366" bgcolor="#F5F5DC" link="#000055" vlink="#550000">\r\n';

var fromHint = 0;

function HintWindow(title, text, but)
{
  fromHint = 1;
  NewDia(title,
    '<FONT color="#CC0000"><B>' + title + ':</B></FONT><P>' +
    text +
    '<P align=right>\r\n' + (but ? but : "") +
    '<INPUT type="BUTTON" value="Schließen" onClick="self.close()"></P>',
    "",
    420, 250, 100, 100, 0);
}

function EFSolvedOutfit(nr)
{
  top.data.display.document.fm.EndButton.value = "Beenden";
//  if ( top.data.display.CrashDown && Flight != 0 )
//    top.data.display.CrashDown();
}

function EFStartOutfit()
{
  var fm = top.data.display.document.fm;
  if ( navigator.userAgent.indexOf("MSIE 5.22; Mac") != -1 ) // Mac mit Hacke
  {
    var inp = top.data.display.document.all.tags("input");
    for (var i = 0; i < inp.length; i++)
      if ( inp[i].type == "button" )
        inp[i].style.width="";
  }
  if ( fm.ConfirmButton )
      if ( fm.ConfirmButton.length )
        for (var i = 0; i < fm.ConfirmButton.length; i++)
          fm.ConfirmButton[i].value = "Eingabe bestätigen";
      else
        fm.ConfirmButton.value = "Eingabe bestätigen";
  if ( fm.HintButton )
    if ( fm.HintButton.length )
      for (var i = 0; i < fm.HintButton.length; i++)
        fm.HintButton[i].value = "Hinweis";
    else if ( fm.HintButton )
      fm.HintButton.value = "Hinweis";
  if ( fm.SolveButton )
    if ( fm.SolveButton.length )
      for (var i = 0; i < fm.SolveButton.length; i++)
        fm.SolveButton[i].value = "Lösung";
    else
      fm.SolveButton.value = "Lösung";
  if ( fm.NextButton )
    fm.NextButton.value = "Weiter";
  if ( fm.EndButton )
    fm.EndButton.value = "Abbrechen";
  if ( fm.JumpOverButton )
    fm.JumpOverButton.value = "Überspringen";
  if ( top.data.display.iFHint )
    for (var i = 0; i < top.data.display.FHint.length; i++)
      top.data.display.iFHint[i] = 0;
  if ( fm.mailanswerdo )
  {
    fm.appname.value = f.s2.options[f.s2.selectedIndex].text;
    fm.mailanswerdo.checked = mailanswerdo;
    fm.mailansweraddress.value = mailansweraddress;
  }
  // ETest
  if ( fm.BackButton )
    fm.BackButton.value = " ZURÜCK ";
  if ( fm.NextButton ) {
    fm.NextButton.value = "Eingabe bestätigen";
    fm.NextButton.onclick = function() { top.data.display.nSolve = 1; alert("Result: "+top.data.display.GetResult()+"%"); };
  }
  // showHist
  if ( showHistFlag == 1 )
  {
    showHistFlag = 0;
    setTimeout("FillHistExercise()", 50);
  }
}

function EFHTML2Text(s)
{
  var res = "";
  s = s + "";
  while ( true )
  {
    var i = s.search(/</);
    if  ( i == -1 )
    {
      res += s;
      break;
    }
    res += s.slice(0, i);
    s = s.slice(i+1, s.length);
    i = s.search(/>/);
    if  ( i == -1 )
      break;
    s = s.slice(i+1, s.length);
  }
  res = res.replace(/&auml;/g, "ä");
  res = res.replace(/&ouml;/g, "ö");
  res = res.replace(/&uuml;/g, "ü");
  res = res.replace(/&Auml;/g, "Ä");
  res = res.replace(/&Ouml;/g, "Ö");
  res = res.replace(/&Uuml;/g, "Ü");
  res = res.replace(/&szlig;/g, "ß");
  return res;
}

function AddHist(s) // Abw�rtskompatibilitaet f�r Aufgabenschablonen
{
  if ( onRecalc == true )
    setTimeout("top.data.list.LoadNext("+s+")", 5);
}

//------------
var NoDiaFlag = 0;
var DiaWin;

var DoFocus = -1;

function NewDia(title, body, script, width, height, x, y, doFocus)
{
  if ( NoDiaFlag == 1 )
  {
    NoDiaFlag = 0;
    return;
  }
  DiaWin = window.open("", "DiaWin", (fromHint == 1 ? "scrollbars=yes," : "")+"resizable=yes,width=" + width + ",height=" + height);
  var doc = DiaWin.document;
  doc.close();
  doc.open();
  doc.writeln(
    '<head><title>' + title + '</title></head>\r\n',
    DIALOG_HEADER,
    '<div id=dia><font face="Arial">\r\n',
    '<form name="frm" onsubmit="return false;">' +  body, '\r\n</form>',
    '</font></DIV>',
    '</body>\r\n',
    '<sc' + 'ript type="text/javascript"><!-- \r\n',
    '  setTimeout("window.resizeTo(dia.offsetWidth < 410 && dia.offsetHeight > 410 ? 430 : dia.offsetWidth+32, dia.offsetHeight+58)", 200);\r\n',
    '  setTimeout("moveTo(', x, ', ', y, ')", 400);\r\n',
    '  setTimeout("window.resizeTo(dia.offsetWidth < 410 && dia.offsetHeight > 410 ? 430 : dia.offsetWidth+32, dia.offsetHeight+58)", 500);\r\n',
    '  setTimeout("if ( dia.offsetHeight+'+y+' > screen.availHeight ) window.resizeTo(screen.availWidth-'+(x+30)+', screen.availHeight-'+(y+30)+')", 600);\r\n',
    '  var fm = document.frm;\r\n',
    '  var ReturnWin = "1";\r\n',
    script,
    '\r\n',
    '--></sc', 'ript>\r\n',
    '</html>');
  doc.close();
  DiaWin.focus();
  DoFocus = doFocus;
  if ( script != "" && fromHint == 0 )
    self.setTimeout('SetDiaReturnWin()', 500);
  fromHint = 0;
}

function SetDiaReturnWin()
{
  if ( !DiaWin.closed )
    if ( DiaWin.ReturnWin == "1")
    {
      DiaWin.ReturnWin = top.control;
      if ( DoFocus != -1 )
        DiaWin.document.frm.elements[DoFocus].focus();
    }
    else
      setTimeout('SetDiaReturnWin()', 200);
}

// ShowHistExercise ----------------------------------------------------------------
var showHistFlag = 0;

function ShowHistExercise(ex, m1)
{
  showHistFlag = 1;
  mStore = unescape(m1.replace(/###/g, "%"));
  window.open(ex, "display");
}

function FillHistExercise()
{
    if ( mStore != "" ) {
        if ( top.data.display.document.defaultCharset != "UTF-8")
            mStore = decode_utf8(mStore);
        Restore(top.data.display);
        if ( onRecalc ) {
            setTimeout("top.data.list.LoadNext(top.data.display.GetResult())", 5);
        }
    }
}

setTimeout("correctSize()", 100);

function correctSize() {
    var h;
    var h1 = document.body.scrollHeight;
    var h2 = document.body.offsetHeight;
    if (h1 > h2) // all but Explorer Mac
        h = h1;
    else // Exlorer Mac; would also work in Explorer 6 Strict, Mozilla and Safari
        h = h2;
    //top.control.resizeTo('100%', h);
    top.control.height = h;
    return true;
}

function decode_utf8(utftext) {
    var plaintext = ""; var i=0; var c=c1=c2=0;
    // while-Schleife, weil einige Zeichen uebersprungen werden
    while(i<utftext.length)
    {
        c = utftext.charCodeAt(i);
        if (c<128) {
             plaintext += String.fromCharCode(c);
            i++;}
        else if((c>191) && (c<224)) {
            c2 = utftext.charCodeAt(i+1);
            plaintext += String.fromCharCode(((c&31)<<6) | (c2&63));
            i+=2;}
        else {
            c2 = utftext.charCodeAt(i+1); c3 = utftext.charCodeAt(i+2);
            plaintext += String.fromCharCode(((c&15)<<12) | ((c2&63)<<6) | (c3&63));
            i+=3;}
    }
    return plaintext;
}
//]]>
</script>
<?php
$sOut = $OUTPUT->footer();
$i = strpos($sOut, '<footer');
$sOut = substr($sOut, $i);
echo $sOut;
