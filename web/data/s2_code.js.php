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
 * The js-code for the E-Test. We have to insert language strings, thatswhy as php file.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../../config.php");
header('Content-Type: text/javascript');
?>
// study2000, 19.08.2002, TU Dresden, R. Krauße, Version 0.61 Code
// http://linus.psych.tu-dresden.de/Stupla/study2000
// modified 12.06.2013 by rk to fit the new moodle needs

// Modi
var State = 0;

var M_TEXT = 100;
var M_MATERIAL = 101;
var M_END = 104;
var M_TOCMEDIA = 105;

var M_MEDIAOPEN = 200;

var Mode = (M_OVERVIEW != -1 && Media[M_OVERVIEW][0]) ? M_OVERVIEW + M_MEDIAOPEN : M_TEXT;
var OldMode = M_TEXT;
var InBaseCat = false;

var MAX_DEEP = 10;

// global data
var curNr = 0;
var lay;
var ie;
var dom;
var op;
var bas;
var LastChosenTopic = 0;

// Inhaltsverzeichnis
var ContWidth = "25%";
var LastDeep = new Array(MAX_DEEP);
var IsOpen = new Array(nTopic);
var MatHeight = 40;
var AllSwitch = 1;
var KeepCurClosed1 = -1;
var KeepCurClosed2 = -1;

// Notizen
var Notes = new Array(100);
var nNotes = 0;
var NoteAt = 0;
var NoteNr = 0;

// Markieren
var curMarkColor = 0;
var MarkFirst;
var MarkColor = new Array (
  "#00FF00", "#FFFF00", "#FF0000", "#00FFFF",
  "#FF00FF", "#C0C0C0", "#808000", "#0000FF",
  "#FF8000", "#FF8080", "#408080", "#80FF00",
  "#800000", "#8080FF", "#E24F1D", "#FFF9E3",
  "#FFFFFF");
var Marks = new Array(100);
var nMarks = 0;

// Folien
var FolieMatch = 0;
var FolieMode = new Array("(1:1)", "(Breite)", "(Höhe)", "(total)");

// Aufgabe
var mailanswerdo = false;
var mailansweraddress = "";

// allgemeine Merker
var Exploding = 0;
var LoadingData = 0;
var OnLoading = 1;
var OnLoading2 = 1;
var MedTopic = 0;
var MedI = 0;

var blockExLoad = 0;
var blockExUnload = 0;

// momentane Medienliste (wg. transdown)
var MedListMode = -1;
var MedListTopic = -1;
var MedListPos = -1;
var MedList;

// Plane
var Flight = 0;
var PlaneMode = 0;

var PM_MARK1 = 1;
var PM_MARK2 = 2;
var PM_NOTE = 3;

// Protocol
var Protocol = new Array();
var nProtocol = 0;
var LastTime;
var LastAction = "";

var TopicDuration = new Array(nTopic);
var TopicLast = new Array(nTopic);
var LastTopicRead = 0;

// Sheets
var PlayNr = 0;
var PlayButtonMode = 0;

var curSheets = -1;

// Media-Texte
var ModeTitle = new Array('Text', 'Material', 'Sammelmappe', 'Protokoll', 'Ende');

// Buttonstyle: contens, Media on, Media exist, Media not exist, speziell
var ButtonBgColor = new Array('#F5F5DC', '#1E90FF', '#1E90FF', '#808080', '#20B2AA');
var ButtonFontColor = new Array('#003366', '#DDFDF3', '#DDFDF3', '#A9A9A9', '#FFFFFF');

// frequently used strings

var DISPLAY_HEADER = '<BODY text="#333333" bgcolor="#FFFFFF" link="#000055" vlink="#550000" style="font-family:Arial;">\r\n';
var DIALOG_HEADER = '<BODY text="#003366" bgcolor="#F5F5DC" link="#000055" vlink="#550000">\r\n';

var NO_LINKDEKO = '<HEAD>\r\n<style type="text/css">\r\n' +
    '  a:link { text-decoration:none }\r\n' +
    '  a:visited { text-decoration:none }\r\n' +
    '  a:active { text-decoration:none }\r\n' +
    '</style></HEAD>\r\n';

// intern links and overview
var BackAction = "";

function GetMedia(mode, nr)
{
  var m = mode % M_MEDIAOPEN;
  return Media[m][nr];
}

function GetMediaTitle(mode)
{
  var m = mode % M_MEDIAOPEN;
  return MediaTitle[m];
}

function GetMediaTitleShort(type)
{
  if ( type < M_TEXT )
    return MediaTitleShort[type];
  else if ( type < M_TOCMEDIA )
    return ModeTitle[type - M_TEXT];
}

function GetMediaStyle(mode)
{
  var m = mode % M_MEDIAOPEN;
  return (m < M_TEXT) ? MediaStyle[m] : 0;
}

function IsMediaMode(m)
{
  return m < M_TEXT || M_TOCMEDIA <= m;
}

// display functions ------------------------------------------------

function SecondBookEntry(title, mode, comment)
{
  var Entry = '<font face="Arial">'+title+'</font>';
  if ( Mode == mode )
    Entry = '<font face="Arial" color="#228B22"><B>'+title+'</B></font>';
  var s = '<tr><td valign="top"><img src="'+base+'../data/dot2.gif" width="17" height="13" '+(ie||lay?'alt':'title')+'="Globale Funktion">\r\n</td>' +
      '<td colspan="10" valign="bottom"><A href="javascript:top.control.ChangeMode(' + mode + ')" ' +
      ' title="' + comment + '" onMouseOver="status=\'' + title + '\'; return true;" ' +
      'onMouseOut="status=\'\'; return true;" target="control">' + Entry + '</a></td></tr>\r\n';
  return s;
}

function TitleText()
{
  if ( M_TEXT < Mode && Mode < M_MEDIAOPEN || M_TOCMEDIA + M_MEDIAOPEN <= Mode)
    return ModeTitle[Mode - M_TEXT];
  else
    if ( curNr == -1 )
      return "Studierplatz 2000";
    else
      return Name[curNr];
}

function MediaLink(mode, nr, i)
{
  var s;
  var mData = GetMedia(mode, nr);
  if ( mData[i][0] != "" )
    s = '  <A href="javascript:top.control.StartMedia(' + mode + ', ' + nr + ', ' + i + ')"'+ (lay ? "" : ' target="control"');
  else
    s = '  <FONT color="#000055"';
  s += MouseTip(mData[i][2]) + '>' +
       mData[i][1];
  if ( mData[i][0] != "" )
    s += '</A>\r\n';
  else
    s += '</FONT>\r\n';
  return s;
}

function MediaButton(text, value, width)
{
  var style = 2;
  if ( Mode % M_MEDIAOPEN == value || MediaCatBase[Mode % M_MEDIAOPEN] == value )
    style = 1;
  var Exist = value == M_TEXT || TopicAndCapsHasMedia(value, curNr);
  var v1 = value+1;
  while ( !Exist && v1 < MediaTitle.length && MediaCatBase[v1] != -1 )
    Exist = TopicAndCapsHasMedia(v1++, curNr)
  if ( !Exist )
    style = 3;
  return Button(text, 'ChangeMode(' + value + ')', width, style,
    'Zeigt zum Thema zugeordnete' +  ((value == M_TEXT) ? 'n ' : ' ') + text +' an');
}

function ReqSwitchContMode()
{
  setTimeout("SwitchContMode()", 30);
}

function ShowButtons()
{
  ButtonCount = 0; // für <Button>-Breite
  var sControl = "";
  if ( finalTime == "" || State != 1 )
    sContens = "";
  else
  {
    sTimeText = (dCountdown >= 120 ? '<?php echo addslashes_js(get_string('remaining_info2', 'etest')); ?>' : '<?php echo addslashes_js(get_string('remaining_info1', 'etest')); ?>');
    sTimeText = sTimeText.replace(/\{\$a\}/, '<b><span id="countdown" style="padding:3px;background-color:#000040;border:2px solid #CCCCCC;">'+CountDownString()+'</span></b>');
    sContens = '<font face="arial">'+sTimeText+ '</font>';
  }
  if ( ie )
  {
    if ( ContMode >= 1 )
      document.all.Buttons.innerHTML =
        '<TABLE border=0 cellpadding=0 cellspacing=0 width="100%"><TR><TD align="left">' +
        '<FONT face="Arial"><B><SPAN id=efString>'+sControl +'</SPAN></B></FONT>'+
        '</TD><TD align=right>' +
        sContens +
        '</TD></TR></TABLE>';
    else
      document.all.Buttons.innerHTML = sContens + '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ' + sControl;
    for (var i = 0; i < document.all.tags("button").length; i++)
      if ( document.all.tags("button")[i].id )
        document.all.tags("button")[i].style.width = eval("document.all.a"+document.all.tags("button")[i].id+".offsetWidth")+12;
  }
  else if ( dom ) // DOM
  {
    if ( ContMode >= 1 )
      document.getElementById("Buttons").innerHTML =
        '<TABLE border=0 cellpadding=0 cellspacing=0 width="100%"><TR><TD>' +
        '<FONT face="Arial"><B><SPAN id=efString>'+sControl +'</SPAN></B></FONT>'+
        '</TD><TD align=right>' +
        sContens +
        '</TD></TR></TABLE>';
    else
      document.getElementById("Buttons").innerHTML = sContens + '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ' + sControl;
  }
  else // LAY, OP and BASE
  {
    var sBut;
    if ( ContMode >= 1 )
    {
      sBut =
        '<TABLE border=0 cellpadding=0 cellspacing=0 width="100%">' +
        '<TR><TD valign="top">' +
          '<TABLE border=0 cellpadding=0 cellspacing=0><TR>' +
        '<FONT face="Arial"><B><SPAN id=efString>'+sControl +'</SPAN></B></FONT>'+
          '</TR></TABLE>' +
        '</TD><TD>&nbsp;</TD><TD align="right" valign="top">' +
          '<TABLE border=0 cellpadding=0 cellspacing=0><TR>' +
            sContens +
          '</TR></TABLE>' +
        '</TD></TR></TABLE>';
    }
    else
      sBut = '<TABLE border=0 cellpadding=0 cellspacing=0><TR><TD>&nbsp;</TD>' + sContens + '<TD> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </TD>' + sControl + '</TR></TABLE>';
    var doc2 = lay ? document.Buttons.document : top.buttons.document;
    if ( lay )
      sBut = '<FORM>' + sBut + '</FORM>';
    else
      sBut = '<BODY bgcolor="#336699" style="margin-top:0px; margin-bottom:0px;">'+sBut+'</BODY>';
    doc2.close();
    doc2.clear();
    doc2.open();
    doc2.write(sBut);
    doc2.close();
  }
}

function CatLink(i)
{
  var s = '<P><A href="javascript:top.control.ChangeMode('+ i + ')"><B>'+GetMediaTitle(i)+'</B></A></P>';
  return s;
}

function ShowDisplay()
{
  // alles da?
  if ( !top.data.display )
  {
    setTimeout("ShowDisplay()", 500);
    return;
  }
  // close small windows
  if ( DiaWin && !DiaWin.closed )
    DiaWin.close();
  if ( Flight )
    CrashDown();
  PlaneMode = 0;
  NoteAt = 0;
  NoteNr = 0;
  AddProt("");
  if ( M_TEXT <= Mode && Mode < M_TOCMEDIA )
    if ( Nr2Ex[curNr] >=0 )
      InitProt(602, curNr, Nr2Ex[curNr]);
    else
      InitProt(Mode, curNr, 0);
  if ( Mode == M_TEXT || OnLoading2 == 1 )
  {
    if ( curNr == -1 )
      window.open(base + '../data/s2.htm', "display");
    else
    {
      if ( State != 0 && curNr == PosLogin )
      {
        var doc = top.data.display.document;
        doc.close();
        doc.clear();
        doc.open();
        doc.writeln(DISPLAY_HEADER,
          '<FONT face="Arial">Sie sind eingelogget als <B>' +
          (UserNameN != '' ? unescape(UserNameV)+' '+unescape(UserNameN) : unescape(UserDisplayName)) +
          (UserGebDat != '' ? ' ('+unescape(UserGebDat)+')' : '') +
          '</B>.</font>');
        doc.close()
      }
      else if ( OnLoading2 == 1 && M_OVERVIEW != -1 && Media[M_OVERVIEW][0] )
        window.open(base + Media[0][0][0][0], "display");
      else {
        if ( M_VIEWALWAYS != -1 )
        {
           MakeMedList(M_VIEWALWAYS, curNr);
           var ViewAlways1 = (MedList.length > 0 ? true : false);
           if ( ViewAlways != ViewAlways1 )
           {
             ViewAlways = ViewAlways1;
             if ( ViewAlways == true )
               ViewMedia = GetMedia(M_VIEWALWAYS, MedList[0][0])[MedList[0][1]][0];
             setTimeout("SwitchContMode(1)", 50);
			 		   blockExUnload = 0;
             return;
           }
        }
        if ( File[curNr] == "" ) {
		 		  blockExUnload = 0;
          SetTopic(curNr+1);
        }
        else {
        	if ( Nr2Ex[curNr] >= 0 )
        	  blockExLoad = 1;
          window.open(File[curNr], "display");
        }
      }
      OnLoading2 = 0;
    }
  }
  blockExUnload = 0;
}

function SaveShowDisplay()
{
  // alles da?
  if ( !top.data.display )
  {
    setTimeout("SaveShowDisplay()", 500);
    return;
  }
  ShowDisplay();
}

// control functions -----------------------------------------------

function ChangeMode(mode)
{
  CheckStoreExercise();
  if ( mode == M_END ) // LEVELING abbrechen
  {
    if ( State == 1 )
    {
      window.open(base + '../data/cancel.php', "display");
      ShowTitle("<?php print_string('finish_test', 'etest'); ?>");
    }
    else if ( State == 2 )
    {
      window.open(base + '../data/final.php', "display");
      ShowTitle("<?php print_string('result', 'etest'); ?>");
    }
    return;
  }
  InBaseCat = (mode == Mode) && (Mode % M_MEDIAOPEN <= M_TEXT) && MediaCatBase[Mode % M_MEDIAOPEN] == -1 && !InBaseCat;
  if ( Mode % M_MEDIAOPEN <= M_TEXT )
    OldMode = Mode % M_MEDIAOPEN;
  var DoSaveShow = ((Mode >= M_MEDIAOPEN) && (mode != M_TEXT)) ? 1 : 0;
  if ( (GetMediaStyle(Mode) & 2) || (GetMediaStyle(mode) & 2) )
    DoSaveShow = 0;
  if ( Mode > M_TEXT || mode > M_TEXT )
  {
    Mode = mode;
    if ( mode < M_TOCMEDIA || !(GetMediaStyle(mode) & 2) )
      setTimeout("ShowTitle()", 10);
    setTimeout("ShowMat()", 30);
  }
  else
    Mode = mode;
  if ( DoSaveShow == 1 )
  {
    window.open(base + '../data/dummy.htm', "display");
    setTimeout("SaveShowDisplay()", 500);
  }
  else
    setTimeout("ShowDisplay()", 10);
  if ( mode < M_TOCMEDIA || mode >= M_MEDIAOPEN || !(GetMediaStyle(mode) & 2) )
    setTimeout("ShowButtons()", 10);
}

function SetTopic(nr, mode)
{
  if ( AvailableNr(nr) == false )
    return;
  if ( curNr == nr && (!mode || Mode == mode) ) // doppelklick
    return;
  if ( blockExLoad == 1 || blockExUnload == 1 )
    return;
  blockExUnload = 1;
  var dummy = CheckStoreExercise();
  curNr = nr;
  if ( mode )
    Mode = mode;
  SetTopicText(nr);
}

function SetTopicText(nr)  // returns from material
{
  var DoSaveShow = (Mode >= M_MEDIAOPEN);
  Mode %= M_MEDIAOPEN;
  LastChosenTopic = nr;
  if ( Mode > M_TEXT )
  {
    Mode = OldMode;
    setTimeout("ShowMat()", 30);
  }
  if ( Mode < M_TEXT && !TopicAndCapsHasMedia(Mode, nr) )  // rem this for to goto text in every case
    if ( MediaCatBase[Mode] != -1 && TopicAndCapsHasMedia(MediaCatBase[Mode], nr) )
      Mode = MediaCatBase[Mode]
    else
      Mode = M_TEXT;
  MedListMode = -1;
  setTimeout("ShowCont()", 50);
  if ( DoSaveShow == 1 )
  {
    window.open(base + '../data/dummy.htm', "display");
    setTimeout("SaveShowDisplay()", 500);
  }
  else
    setTimeout("ShowDisplay()", 60);
  setTimeout("ShowButtons()", 70);
  setTimeout("ShowTitle()", 80);
//  ShowDisplay();  // org pos
  //setTimeout("ShowCont()", 40);
}

function UpDateCont(nr)
{
  curNr = nr;
  setTimeout("ShowCont()", 10);
}

function SwitchOpen(nr)
{
  if ( nr == KeepCurClosed2 )
    ShowCont();
  else if ( LastDeep[Deep[nr]] == nr ) // zuklappen eines automatisch geöffnenet Kapitels
  {
    KeepCurClosed1 = nr;
    SetTopic(nr);
  }
  else
  {
    IsOpen[nr] = 1 - IsOpen[nr];
    ShowCont();
  }
  AllSwitch = 1;
}

function SwitchAllOpen()
{
  for (var i = 0; i < nTopic; i++)
    IsOpen[i] = AllSwitch;
  AllSwitch = 1-AllSwitch;
  ShowCont();
}

var MedI;

function FolieSwitch()
{
  FolieMatch = (FolieMatch+1) % 4;
  if ( Mode == M_FOLIE + M_MEDIAOPEN )
    StartMedia(M_FOLIE, curNr, MedI);
  else
    ShowButtons();
}

function IsGraphic(s)
{
  var ext = s.substring(s.length-4).toUpperCase();
  return ( ext == ".GIF" || ext == ".JPG" || ext == ".PNG" );
}

function IsHtml(s)
{
  var ext = s.substring(s.length-5).toUpperCase();
  return ( ext.search(/\.HTM/) != -1 );
}

// move in contens ------------------------------------

function PagePrev()
{
  var nr1 = curNr;
  while ( nr1 > 1 )
  {
    nr1--;
    if ( Nr2Ex[nr1] >= 0 )
    {
      SetTopic(nr1);
      return;
    }
  }
}

function PageUp()
{
  if ( curNr == -1 || Deep[curNr] == 0 )
  {
    SetTopic(0);
    return;
  }
  for (var i=curNr; i >= 0; i--)
    if ( Deep[i] < Deep[curNr] )
    {
      SetTopic(i);
      break;
    }
}

function PageNext()
{
  if ( Nr2Ex[curNr] == ExUser.length-1)
  {
    CheckStoreExercise();
    setTimeout("ShowCont()", 50);
    window.open(base + '../data/cancel.php', "display");
    ShowTitle("<?php print_string('finish_test', 'etest'); ?>");
    return;
  }
  var nr1 = curNr;
  while ( nr1 < nTopic-1 )
  {
    nr1++;
    if ( Nr2Ex[nr1] >= 0 )
    {
      SetTopic(nr1);
      return;
    }
  }
}

function MoveMedia(d)
{
  MedListPos += d;
  StartMedia(Mode % M_MEDIAOPEN, MedList[MedListPos][0], MedList[MedListPos][1]);
}

function Prev()
{
  if ( posHistory > 0 )
    SetHistory(--posHistory);
}

function Next()
{
  if ( posHistory +1 < nHistory )
    SetHistory(++posHistory);
}

function BackToStuff(oldMode)
{
  if ( curNr != LastChosenTopic )
    UpDateCont(LastChosenTopic);
  ChangeMode(oldMode)
}

// service function ----------------------------------------------------------

function HTML2Text(s)
{
  var res = "";
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
  res = res.replace(/&auml;/, "ä");
  res = res.replace(/&ouml;/, "ö");
  res = res.replace(/&uuml;/, "ü");
  res = res.replace(/&Auml;/, "Ä");
  res = res.replace(/&Ouml;/, "Ö");
  res = res.replace(/&Ouml;/, "Ü");
  res = res.replace(/&szlig;/, "ß");
  return res;
}

function TN(s) // two numbers
{
  if ( s < 10 )
    return "0" + s;
  else
    return s;
}

function InsertBR(s)
{
  return s.replace(/\n/g, "<BR>");
}

function CivilizeBlanks(s)
{
  while ( s.length > 0 && s.charAt(0) == " " )
    s = s.slice(1, s.length);
  var i = 1;
  while ( i < s.length )
  {
    if ( s.charAt(i) == " " )
      if ( s.charAt(i-1) == " " )
      {
        var s1 = s;
        s = s1.slice(0, i-1) + s1.slice(i, s1.length);
        continue;
      }
    i++;
  }
  while ( s.length > 0 && s.charAt(s.length-1) == ' ' )
    s = s.slice(0, s.length-1);
  return s;
}

function HTML2StatusText(s)
{
  var res = HTML2Text(s);
  res = res.replace(/'/g, "&rsquo;").replace(/&nbsp;/g, " ");
  return CivilizeBlanks(res);
}

function MouseTip(text, zusatz)
{
  var s1 = HTML2StatusText(text);
  return (s1 != '' ? ' title="' + s1 + '"' : '') + ' onMouseOver="status=\'' + s1 + '\';' +
         (zusatz ? zusatz : '') +
         ' return true;" onMouseOut="status=\'\'; return true;" ';
}

// aufgabe ---------------------------------------------------------------------------------

function HintWindow(title, text)
{
  NewDia(title,
    '<font color="#CC0000"><b>' + title + ':</b></font><p>' +
    text +
    '</p><p align=right>\r\n' +
    '<input type="BUTTON" value="Schließen" onClick="self.close()"/></p>',
    " ",
    420, 250);
}

function EFSolvedOutfit(nr)
{
  if ( top.data.display.CrashDown && Flight != 0 )
    top.data.display.CrashDown();
}

function EFStartOutfit()
{
  var fm = top.data.display.document.fm;
  if ( fm.BackButton )
    fm.BackButton.value = " <?php print_string('BACK', 'etest'); ?> ";
  if ( fm.NextButton )
    fm.NextButton.value = Nr2Ex[curNr] == ExUser.length-1 ? "<?php print_string('FINISH_TEST', 'etest'); ?>" : " <?php print_string('NEXT', 'etest'); ?> ";
  if ( ""+top.data.display.location != File[curNr] ) {
    var newCurNr = -1;
    for (var i = 0; i < File.length; i++)
      if ( ""+top.data.display.location == File[i] ) {
        newCurNr = i;
        break;
      }
    if ( newCurNr != -1 ) {
      curNr = newCurNr;
      setTimeout("ShowCont()", 50);
      setTimeout("ShowButtons()", 70);
      setTimeout("ShowTitle()", 80);
      alert('Bitte verwenden Sie zum Navigieren nicht die "Vor"- und "Zurück"-Knöpfe des Browsers.');
      AddProt("NavigationError");
      InitProt(602, curNr, Nr2Ex[curNr]);
    }
  }
  if ( Nr2Ex[curNr] >= 0 )
  {
    mStore = ExStore[Nr2Ex[curNr]];
    if ( (''+mStore).substr(0, 10) == '##reload##') {
    	mStore = mStore.substr(10);
    	top.data.display.nSolve = 0;
			RestoreReload(top.data.display);
    }
    else if ( mStore != '##empty##') {
    	top.data.display.nSolve = 0;
      Restore(top.data.display);
    }
  }
  blockExLoad = 0;
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

function AddHist(s) // Abwärtskompatibilitaet für Aufgabenschablonen
{
//  if ( LastAction == "" ) // Mehrfach lösen
//    InitProt(LastMed, LastTopic, LastI);
  AddProt(s);
  //InitProt(LastMed, LastTopic, LastI);
}

// save and load ----------------------------------------------------------------------

function MakeString()
{
  var res = "";
  // notes
  for (var i = 0; i < nNotes; i++)
  {
    if ( i != 0 )
      res += "$";
    res += Notes[i][0] + "$" + Notes[i][1] + "$" + Notes[i][2];
  }
  // marks
  res += "|";
  for (var i = 0; i < nMarks; i++)
  {
    if ( i != 0 )
      res += "$";
    res += Marks[i][0] + "$" + Marks[i][1] + "$" + Marks[i][2] + "$" + Marks[i][3];
  }
  // protocol
  res += "|";
  for (var i = 0; i < nProtocol; i++)
  {
    if ( i != 0 )
      res += "$";
    res += Protocol[i];
  }
  // sheets
  res += "|";
  for (var i = 0; i < nSheets; i++)
  {
    if ( i != 0 )
      res += "$";
    res += Sheets[i][1];
    for (var j = 0; j < Sheets[i][0]; j++)
      res += "&" + Sheets[i][j+2];
  }
  return res;
}

function ParseString(s)
{
  var Data = s.split("|");
  // notes
  nNotes = 0;
  var Data1 = Data[0].split("$");
  for (var i = 0; i < Data1.length-2; i += 3)
  {
    Notes[nNotes] = new Array(Data1[i], Data1[i+1], Data1[i+2]);
    nNotes++;
  }
  // marks
  nMarks = 0;
  Data1 = Data[1].split("$");
  for (var i = 0; i < Data1.length-3; i += 4)
  {
    Marks[nMarks] = new Array(Data1[i], Data1[i+1], Data1[i+2], Data1[i+3]);
    nMarks++;
  }
  // protocol
  nProtocol = 0;
  if ( Data[2] != "" )
  {
    Data1 = Data[2].split("$");
    for (var i = 0; i < Data1.length; i++)
    {
      Protocol[nProtocol++] = Data1[i];
    }
  }
  // sheets
  nSheets = 0;
  Data1 = Data[3].split("$");
  for (var i = 0; i < Data1.length; i++)
  {
    Sheets[i] = new Array();
    nSheets++;
    var Data2 = Data1[i].split("&");
    Sheets[i][0] = 0;
    Sheets[i][1] = Data2[0];
    for (var j = 1; 2*j < Data2.length; j++)
    {
      Sheets[i][j+1] = '' + Data2[2*j-1] + '&' + Data2[2*j];
      Sheets[i][0]++;
    }
  }
  ShowDisplay();
  ShowCont();
}

// Protocol ---------------------------------------------------------

var LastMed = -1;
var LastTopic = -1;
var LastI = -1;

function InitProt(med, topic, nr)
{
  AddHistory(med, topic, nr, "");
  if ( LastAction != "" )
    AddProt("");
//  if ( M_TEXT < med && med < M_TOCMEDIA )
//    return;
  LastMed = med;
  LastTopic = topic;
  LastI = nr;
  LastAction = "" + LastMed + "." + topic + "." + nr;
  var Now = new Date();
  LastTime =  Math.floor(Now.getTime() / 1000);
}

function AddProt(s)
{
  if ( LastAction == "" )
    return;
  var Now = new Date();
  var NowTime =  Math.floor(Now.getTime() / 1000);
  //if ( NowTime - LastTime > 10 || s != "" ) // page was watched more than 10 seconds
  {
    LastAction += "&" + LastTime + "&" + (NowTime - LastTime) + "&" + s;
    Protocol[nProtocol++] = LastAction;
//    SendProt(LastAction);
    if ( LastMed == 600 ) // Login
    	SendProt(LastTime, 600, -1, NowTime - LastTime, 0, s);
    else if ( LastMed == 601 ) // Final send
    	SendProt(LastTime, 601, -1, NowTime - LastTime, Points, s);
    else if ( LastMed == 602 ) { // Aufgabe
    	var i = s.indexOf(' ');
    	SendProt(LastTime, 602, ExAlt[LastTopic], NowTime - LastTime, s.substr(0, i), s.substr(i+1));
    }
    else if ( LastMed == 603 ) // Master time correct
    	SendProt(LastTime, 603, -1, NowTime - LastTime, 0, s);
  }
  LastAction = "";
  if ( LastMed == M_AUFGABE )
  {
    ShowCont();
    ShowButtons();
    var Now = new Date();
    LastTime =  Math.floor(Now.getTime() / 1000); // für Mehrfachlösen
  }
}

function Dauer(t)
{
  if ( t < 60 )
    return "" + t + " sec";
  if ( t < 3600 )
    return "" + Math.floor(t/60) + '.' + TN(t % 60) + " min";
  return "" + Math.floor(t/3600) + '.' + TN(Math.floor((t % 3600) / 60)) + " h";
}

function GetAddText(sCode)
{
/*  for (var i = nProtocol-1; i >= 0; i--)
    if ( sCode == Protocol[i].substring(0, sCode.length) )
    {
      var Task = Protocol[i].split("&");
      return Task[3];
    }*/
  return "";
}

function GetExerciseResult(name)
{
  if ( M_AUFGABE == -1 )
    return 0;
  var addText = "";
  for (var i = 0; i < Name.length; i++)
    if ( Media[M_AUFGABE][i] )
      for (var j=0; j < Media[M_AUFGABE][i].length; j++)
        if ( Media[M_AUFGABE][i][j][1] == name )
          addText = GetAddText("" + M_AUFGABE+ "." + i + "." + j);
  return addText;
}

// ProtocolExercise -------------------------------------------------

function sColBlock(col, h, text)
{
  return '<img src="' + base + '../data/pc_' + col + '.gif" width="40" height="' + h + '" '+(ie||lay?'alt':'title')+'="'+text+'">';
}

function ProtPillar(doc, vals, l)
{
  var first = 1;
  var sumAll = 0;
  for (var i = 0; i < vals.length; i++)
    sumAll += vals[i];
  var cName = new Array("gruen", "gelb", "blau",  "rot");
  var cComment = new Array("richtig gelöst", "zum Teil richtig", "unbearbeitet", "falsch gelöst");
  var pix = 0;
  var sum = 0;
  doc.writeln('<TABLE border="0" cellspacing="0" width="50"><TR><TD align="left" valign="top" width="40">');
  for (var i = vals.length-1; i >= 0; i--)
    if ( vals[i] != 0)
    {
      sum += vals[i];
      var sumPix = Math.floor(sum*l/sumAll+0.5);
      first = 0;
      pix = sumPix;
    }
  doc.writeln('</TD></TR></TABLE>');
}

function sProtPillar(vals, l)
{
  var first = 1;
  var sumAll = 0;
  for (var i = 0; i < vals.length; i++)
    sumAll += vals[i];
  var cName = new Array("gruen", "gelb", "blau",  "rot");
  var cComment = new Array("richtig gel&ouml;st", "zum Teil richtig gel&ouml;st", "nicht bearbeitet", "falsch gel&ouml;st");
  var pix = 0;
  var sum = 0;
  var sRes = '<table class="pillar">';
  for (var i = vals.length-1; i >= 0; i--)
    if ( vals[i] != 0)
    {
      sum += vals[i];
      var sumPix = Math.floor(sum*l/sumAll+0.5);
      sRes += '<tr><td>'+
              sColBlock(cName[i], sumPix-pix, vals[i]+((vals[i] == 1) ? ' Frage ' : ' Fragen ')+cComment[i])+
              '</td></tr>';
      first = 0;
      pix = sumPix;
    }
  return sRes + '</table>';
}

function sDiagram()
{
  if ( State == 0 )
    return 'Noch keine Aufgaben geladen.';
  var s = '<table border="0" align="center"><tr><td><table border="0" cellpadding="5" width="100%" class="graphic"><tr>';
  var l = 200;
  // Skale
  s += '<td align="right" valign="bottom" style="white-space:nowrap">'+
    '<table class="pillarRight" >'+
    '<tr><td><img src="' + base + '../data/ps_100.gif" height="11" width="30"><img src="' + base + '../data/ps_t.gif" height="11" width="5"></td></tr>'+
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="'+ (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_75.gif" height="11" width="26"><img src="' + base + '../data/ps_m.gif" height="11" width="5"></td></tr>'+
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_50.gif" height="11" width="26"><img src="' + base + '../data/ps_m.gif" height="11" width="5"></td></tr>'+
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_25.gif" height="11" width="26"><img src="' + base + '../data/ps_m.gif" height="11" width="5"></td></tr>'+
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>'+
    '<tr><td><img src="' + base + '../data/ps_b.gif" width="5" height="6"></td></tr>'+
    '</table>'+
    '</td>';
  var BlockVals = new Array();
  var BlockNr = new Array();
  var j = ExN.length; // Anzahl der Balken
  var iEx = 0;
  for (var i = 0; i < j; i++)
    BlockVals[i] = new Array(0, 0, 0, 0);
  for (var i = 0; i < nTopic; i++)
    if ( Nr2Ex[i] >= 0 )
    {
      var j1 = Nr2Ex[i];
      var j2 = Nr2Block[i];
      if ( ExUserState[j1] == -2 )
        BlockVals[j2][2]++;
      else if ( ExUserState[j1] >= 98 )
        BlockVals[j2][0]++;
      else if ( ExUserState[j1] > 0 )
        BlockVals[j2][1]++;
      else
        BlockVals[j2][3]++;
    }
  var SummVals = new Array(0, 0, 0, 0);
  // Blöcke drucken
  for (var i = 0; i < j; i++)
  {
    s += '<td width="'+100/(j+1)+'%" valign="bottom">';
    s += sProtPillar(BlockVals[i], l);
    s += '</td>';
    SummVals[0] += BlockVals[i][0];
    SummVals[1] += BlockVals[i][1];
    SummVals[2] += BlockVals[i][2];
    SummVals[3] += BlockVals[i][3];
  }
  s += '<td width="'+100/(j+1)+'%" valign="bottom">';
  s += sProtPillar(SummVals, l);
  // Skale
  s += '</td>' +
    '<td align="left" valign="bottom" style="white-space:nowrap">' +
    '<table class="pillarLeft" >'+
    '<tr><td><img src="' + base + '../data/ps_t.gif" height="11" width="5"><img src="' + base + '../data/ps_100.gif" height="11" width="30"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_m.gif" height="11" width="5"><img src="' + base + '../data/ps_75.gif" height="11" width="26"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11)  + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_m.gif" height="11" width="5"><img src="' + base + '../data/ps_50.gif" height="11" width="26"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_m.gif" height="11" width="5"><img src="' + base + '../data/ps_25.gif" height="11" width="26"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_line.gif" width="5" height="' + (l/4-11) + '"></td></tr>' +
    '<tr><td><img src="' + base + '../data/ps_b.gif" width="5" height="6"></td></tr>' +
    '</table>'+
    '</td>' +
    '</tr><tr><td>&nbsp;</td>';
  for (var i = 0; i < j; i++)
    s += '<td width="'+(100/(j+1))+'%" valign="top" bgcolor="#FFFF80'+
      '"><font face="Arial"><nobr><strong>' + Name[Block2Nr[i]] + '</font></nobr></td>';
  s += '<td valign="top" bgcolor="#FFFF80"><font face="Arial"><strong><?php print_string('total', 'etest') ?></strong></font></td><td>&nbsp;</td></tr></table></td></tr></table>\r\n' +
    '<font face="Arial">'+sColBlock("gruen", 10, "")+'\r\n' +
    '<?php print_string('correctly_solved', 'etest') ?> &nbsp; &nbsp; \r\n' +
    sColBlock("gelb", 10, "")+'\r\n' +
    '<?php print_string('partially_solved', 'etest') ?> &nbsp; &nbsp; \r\n' +
    sColBlock("rot", 10, "")+'\r\n' +
    '<?php print_string('wrong_solved', 'etest') ?> &nbsp; &nbsp; \r\n' +
    sColBlock("blau", 10, "")+'\r\n' +
    '<?php print_string('not_solved', 'etest') ?></font>';
  return s;
}

// Links from text --------------------------------------------------

function VisitMedia(topic, media, i)
{
  if ( Mode == M_TEXT )
    BackAction = "curNr = " + (1*curNr) +"; ChangeMode(" + M_TEXT +");ShowTitle();ShowCont();";
  else if ( Mode == M_OVERVIEW + M_MEDIAOPEN )
    BackAction = 'StartMedia(' + M_OVERVIEW + ', ' + curNr + ', ' + MedI + ');ShowTitle();ShowCont();';
  if ( media == -1 ) // nur Text
  {
    Mode = M_TEXT;
    SetTopic(topic);
    return;
  }
  else if ( i == -1 )
  {
    curNr = topic;
    ChangeMode(media);
  }
  else
  {
    StartMedia(media, topic, i);
  }
  setTimeout("ShowTitle()", 30);
  setTimeout("ShowCont()", 40);
}

function StartEmptyMedia(mode, nr, i)
{
  var doc = top.data.display.document;
  doc.close();
  doc.clear();
  doc.open();
  doc.writeln(DISPLAY_HEADER,
    '<FONT face="Arial"><FONT size="4" color="#CC0000"><B>',
    GetMediaTitle(mode),
    '</B></FONT><P><B>',
    MediaLink(mode, nr, i), '</B><P>',
    GetMedia(mode, nr)[i][2],
    '<P align="right"><FONT size="2">(Kein Link zugewiesen.)</FONT>');
  doc.close();
}

// Init -------------------------------

var MatBG = new Array();
var CapTopic = new Array();
var aLastTopic = new Array();

function Init()
{
  lay = top.document.layers;
  ie = document.all && document.all.tags("title")[0].style.setAttribute;
  dom = document.getElementById && document.getElementById("Buttons") && document.getElementById("Buttons").innerHTML;
  op = document.getElementById && navigator.userAgent.indexOf('Opera') != -1;
  bas = !lay && ! ie && !dom && !op;
  // background graphics
  for (var i = 0; i < MediaTitle.length; i++)
  {
    var s = MediaTitleShort[i];
    s = s.replace(/\./g, "");
    s = s.replace(/ä/g, "ae");
    s = s.replace(/Ä/g, "Ae");
    s = s.replace(/ö/g, "oe");
    s = s.replace(/Ö/g, "Oe");
    s = s.replace(/ü/g, "ue");
    s = s.replace(/Ü/g, "Ue");
    s = s.replace(/ß/g, "sz");
    MatBG[i] = s;
  }

  var LastCap = new Array();
  for (var i = 0; i < nTopic; i++)
  {
    TopicDuration[i] = 0;
    IsOpen[i] = 0;
    LastCap[Deep[i]] = i;
    CapTopic[i] = ( Deep[i] == 0 ) ? -1 : LastCap[Deep[i]-1];
    aLastTopic[i] = 0;
  }

  for (var i = nTopic-1; i >= 0; i--)
    if ( CapTopic[i] != -1 )
      if ( aLastTopic[CapTopic[i]] == 1 )
        aLastTopic[i] = 0;
      else
      {
        aLastTopic[i] = 1;
        aLastTopic[CapTopic[i]] = 1
      }

  for (var i = 0; i < MediaTitle.length; i++)
    for (var j = 0; j < nTopic; j++)
      if ( Media[i][j] )
        for (var k = 0; k < Media[i][j].length; k++)
        {
          Media[i][j][k][4] = 0;
          Media[i][j][k][5] = 0;
          Media[i][j][k][6] = -1;
        }

  // StartParameter lesen
  var s = top.location.href;
  var p1 = s.indexOf("?")
  if ( p1 != -1 ) {
    var arr = s.substr(p1+1).split("&");
    for (var j = 0; j < arr.length; j++) {
      if ( arr[j].toLowerCase().indexOf("topic=") == 0 )
      {
        var s1 = unescape(arr[j].substr(6));
        var l1 = s1.length;
        for (var i = 0; i < nTopic; i++)
          if ( s1 == Name[i].substr(0, l1) ) {
            curNr = i;
            break;
          }
      }
      //ETEST
      else if ( arr[j].toLowerCase().indexOf("course=") == 0 )
        etest_course= unescape(arr[j].substr(7))
      else if ( arr[j].toLowerCase().indexOf("etest=") == 0 )
        etest_etest= unescape(arr[j].substr(6))
      else if ( arr[j].toLowerCase().indexOf("user=") == 0 )
        etest_user= unescape(arr[j].substr(5))
    }
  }

  // LEVELING
  for (var i = 0; i < MediaTitle.length; i++)
  {
    if ( MediaTitle[i].toLowerCase() == "leveling" || MediaTitle[i].toLowerCase() == "etest" )
      M_LEVELING = i;
    if ( MediaTitle[i].toLowerCase() == "viewalways" )
      M_VIEWALWAYS = i;
  }

  for (var i = 0; i < nTopic; i++)
    if ( File[i] == LOGIN )
    {
      File[i] = "../../etest_sulogin.php?etest="+etest_etest+"&user="+etest_user+"&course="+etest_course;
      PosLogin = i;
      topNr = i;
    }


  SwitchContMode();
}

function GetTopicHaken(nr)
{
  if ( M_AUFGABE == -1 )
    return false;
  var val = false;
  for (var i = nr; i < nTopic; i++)
    if ( i != nr && Deep[nr] == Deep[i] )
      break;
    else
      if ( Media[M_AUFGABE][i] )
        for (var j = 0; j < Media[M_AUFGABE][i].length; j++)
          if ( Media[M_AUFGABE][i][j][6] == -1 )
            return false;
          else
            val = true;
  return val;
}

// Lernpfad

function SLP(mode, nr, i)
{
  if ( top.control.SLPText )
  {
    if ( mode == M_TEXT )
      return SLPText[nr];
    else if ( SLPMedia[mode] && SLPMedia[mode][nr] && SLPMedia[mode][nr][i] )
      return SLPMedia[mode][nr][i];
  }
  return 0;
}

function SLP_L(mode, nr, i)
{
  return (SLP(mode, nr, i) == 1) ? "l" : "";
}

// Topic bzw. transdown hat Medien (nicht auf Cat's weitergesplittet)
function TopicAndCapsHasMedia(mode, nr)
{
  if ( GetMedia(mode, nr) )
    return true;
  // mit TRANSDOWNS versuchen
  var nr1 = CapTopic[nr];
  while ( nr1 != -1 )
  {
    var med = GetMedia(mode, nr1);
    if ( med )
      for (var i = 0; i < med.length; i++)
         if ( med[i][3] & 1 != 0 )
           return true;
    nr1 = CapTopic[nr1];
  }
  return false;
}

// Topic bzw. transdown hat Medien (nicht auf Cat's weitergesplittet)
function TopicAndCapsHasGraphicMedia(mode, nr)
{
  var nr1 = nr;
  while ( nr1 != -1 )
  {
    var med = GetMedia(mode, nr1);
    if ( med )
      for (var i = 0; i < med.length; i++)
         if ( nr1 == nr || med[i][3] & 1 != 0 )
           if ( IsGraphic(med[i][0]) )
             return true;
    nr1 = CapTopic[nr1];
  }
  return false;
}

// Aktuelle Medienliste generieren, (rekursiv, da von allgemein zu speziell)

function MakeMedList(mode, nr)
{
  MedListMode = mode;
  MedListTopic = nr;
  MedList = new Array();
  MakeMedListRek(nr, nr);
  MedListPos = -1;
}

function MakeMedListRek(nr1, nr)
{
  if ( CapTopic[nr1] != -1 )
    MakeMedListRek(CapTopic[nr1], nr);
  var med = GetMedia(MedListMode, nr1);
  if ( med )
    for (var i = 0; i < med.length; i++)
      if ( nr1 == nr || med[i][3] & 1 != 0 )
        MedList[MedList.length] = new Array(nr1, i);
}

// Achtung! nr ist Nr des (transdowned) Mediums, kann von curNr abweichen
function CheckUpdateMedListPos(mode, nr, i)
{
  if ( MedListMode != mode )
    MakeMedList(mode, curNr);
  MedListPos = 0;
  for (var j = 0; j < MedList.length; j++)
    if ( MedList[j][0] == nr && MedList[j][1] == i )
    {
      MedListPos = j;
      break;
    }
}

function CheckUpdateMedList(mode, nr, i)
{
  if ( MedListMode != mode || MedListTopic != curNr )
    MakeMedList(mode, curNr);
  CheckUpdateMedListPos(mode, nr, i);
}

// History ----------------------------------------------------------

var History = new Array();
var nHistory = 0;
var posHistory = -1;

function AddHistory(mode, nr, i, add)
{
  var s = ""+mode+"."+nr+"."+i+"."+add;
  if ( posHistory < 0 || History[posHistory] != s )
  {
    History[++posHistory] = ""+mode+"."+nr+"."+i+"."+add;
    nHistory = posHistory+1;
  }
}

function SetHistory(pos)
{
  var w = History[pos].split(".");
  Mode = 1*w[0];
  curNr = 1*w[1];
  if ( IsMediaMode(1*w[0]) && w[0] >= M_MEDIAOPEN )
  {
    StartMedia(1*w[0] % M_MEDIAOPEN, 1*w[1], 1*w[2]);
/*    if ( w[3] != "" && GetMediaStyle(1*w[0], 1*w[1]) & 8 != 0)
      top.data.display.glossdata.GotoGloss(w[3]);*/
  }
  else
  {
    ShowDisplay();
    ShowButtons();
    ShowTitle();
    ShowCont();
  }
}

// keep exercise-Entries

function CheckStoreExercise()
{
//  alert("reqstore"+State+" "+Nr2Ex[curNr]+" "+(!top.data.display.m));
  if ( State != 1 || Nr2Ex[curNr] < 0 || !top.data.display.m )
    return false;
  Store(top.data.display);
//  alert("store"+mStore);
  ExStore[Nr2Ex[curNr]] = (new Array()).concat(mStore);
  top.data.display.nSolve = -10;
  EFResult(top.data.display.GetResult());
  return true;
}

// Leveling ---------------------------------------------------------

var M_LEVELING = -1;
var M_VIEWALWAYS = -1;
var ViewAlways = false;

var LOGIN = "START";
var PosLogin = 0;

var UserNameN = "";
var UserNameV = "";
var UserDisplayName = "";
var UserGebDat = "";
var User = "";
var usePrintButton = 1;
var useResultDiagram = 1;
var UserData = "";
var ExUser;
var ExUserState;
var ExN;
var Block2Pos = new Array();
var Nr2Block = new Array();
var Block2Nr = new Array();
var Nr2Ex = new Array();

var ExStore = new Array();

var topNr = 99999;

var CourseName;

var finalTime = -1;

function Insert(arr, elem, pos)
{
  for (var i = arr.length-1; i >= pos; i--)
    arr[i+1] = arr[i];
  arr[pos] = elem;
}

function ApplyData()
{
  State = 1;
  var dataSrc = top.data.display;
  UserNameN = dataSrc.UserNameN;
  UserNameV = dataSrc.UserNameV;
  UserDisplayName = dataSrc.UserDisplayName;
  UserGebDat = dataSrc.UserGebDat;
  User = dataSrc.User;
  usePrintButton = dataSrc.usePrintButton;
  useResultDiagram = dataSrc.useResultDiagram;
  UserData = dataSrc.UserData;
  ExUser = dataSrc.ExUser;
  var ExName = dataSrc.ExName;
  ExUserState = (new Array()).concat(dataSrc.ExUserState); // damit es auch wirklich ein Array ist (das z.B. join() versteht)
  ExN = dataSrc.ExN;
  ExAlt = new Array();

//  AddHistory(02);
//  AddHistoryInfo(GetHistoryDateTime());
  // Aufgaben einfügen
  var iExBlock = 0;
  var iEx = 0;
  for (var i = 0; i < Name.length; i++)
    if ( Media[M_LEVELING][i] )
    {
      // Überschrift-Zusatzdaten
      Nr2Block[i] = iExBlock;
      Block2Nr[iExBlock] = i;
      Nr2Ex[i] = -1;
      var exDeep = Deep[i]+1;
      // KL-Aufgaben einfügen
      for (var j = 0; j < ExN[iExBlock]*1; j++)
      {
        i++;
        Insert(Name, ExName[iEx], i);
        Insert(File, ExUser[iEx], i);
        Insert(Deep, exDeep, i);
        Insert(IsOpen, false, i);
        ExAlt[i] = dataSrc.ExAlt[iEx];
        Nr2Block[i] = iExBlock;
        Nr2Ex[i] = iEx;
        if ( dataSrc.ExValue )
	        ExStore[iEx] = dataSrc.ExValue[iEx];
    		else
  	      ExStore[iEx] = '##empty##';
        iEx++
        for (var k = 0; k < MediaTitle.length; k++)
          Insert(Media[k], "", i);
      }
      iExBlock++;
    }
    else
      Nr2Ex[i] = -2;
  nTopic = Name.length;
  UpdateTopicStruct();
  // alles anzeigen
  //CookieCheck();
/*  // nochmal die korrekten Zahlen ermitteln (falls Änderung durch Cookie)
  nExSolved = 0;
  nExToSolve = 0;
  for (var i = 0; i < ExBlockState.length; i++)
    for (var j = 0; j < ExBlockState[i].length; j++)
      if ( ExBlockState[i][j] >= 0 )
        nExSolved++;
      else if ( ExBlockState[i][j] >= -2 )
        nExToSolve++;
  CheckExBlockColor();
  // PosEvaluation ermitteln
  for (var i = 0; i < nTopic; i++)
  {
    if ( Deep[i] == 0 )
      PosEvaluation = i;
    if ( Nr2Block[i] == -2 )
      break;
  }
  for (var i = PosEvaluation+1; i < nTopic; i++)
    if ( Deep[i] == 0 )
    {
      PosThanks = i;
      break;
    }
  // Evaluation ausblenden
  for (var i = PosEvaluation; i < nTopic; i++)
  {
    if ( Deep[i] == 0 )
      PosEvaluation = i;
    if ( Nr2Block[i] == -2 )
      break;
  }
  Modified = false;*/
  if ( dataSrc.RemainingTime == "no" )
    finalTime = "";
  else
    finalTime = (new Date()).getTime()+dataSrc.RemainingTime*1000;
  InitProt(600, 0, 0);
  AddProt(""+(new Date())+" ["+navigator.appName+" "+navigator.appVersion+"]");
  topNr = nTopic+1;
  ShowMat();
//  CheckSolveState();
  PageNext();
  if ( finalTime != "" )
    setTimeout("CountDown()", 1000);
}

/*function CheckSolveState()
{
  if ( State < 1 || State > 2 )
    return;
  // noch zu lösende Aufgaben übrig
  var nUnSolved = 0;
  for (var i = 0; i < Name.length; i++)
    if ( Nr2Ex[i] >= 0 )
      if ( ExUserState[Nr2Ex[i]] <= -1 )
      {
        //topNr = i;
        return;
      }
  // abschicken der Ergebnisse
  FinalSend("complete")
}*/

function nSolved()
{
  if ( State < 1 || State > 2 )
    return;
  // noch zu lösende Aufgaben übrig
  var n = 0;
  for (var i = 0; i < Name.length; i++)
    if ( Nr2Ex[i] >= 0 )
      if ( ExUserState[Nr2Ex[i]] != -2 )
        n++;
  return n;
}

function GetStateNr(nr)
{
  if ( nr > topNr )
    return 3;
  if ( State == 0 )
    return 2;
  if ( Nr2Ex[nr] >= 0 )
  {
    var j =  ExUserState[Nr2Ex[nr]];
    if ( j == -2 )
      return 4;
/*    else if ( j == -1 )
      return 4;*/
    else
      return 5;
  }
  else
    return 2;
}

function AvailableNr(nr)
{
  if ( nr > topNr )
    return false;
  if ( State == 0 )
    return true;
  /*if ( Nr2Ex[nr] >= 0 )
  {
    var j =  ExUserState[Nr2Ex[nr]];
    if ( j < 0 )
      return true;
    else
      return false;
  }
  else*/
    return true;
}

function SendableString(s)
{
  var sSendable = s;
  sSendable = sSendable.replace(/ /g, "_");
  sSendable = sSendable.replace(/\t/g, "_");
  sSendable = sSendable.replace(/\n/g, "//");
  sSendable = sSendable.replace(/\r/g, "//");
  return sSendable;
}

function AddEFText()
{
  var ef = top.data.display;
  Store(ef);
  var sEF = "";
  for (var i = 0; i < ef.m.length; i++)
  {
    if ( i != 0 )
      sEF += '|';
    if ( ef.Type[i] == 0 )
      sEF += '[mc]'+(ef.m[i]+1);
    else if ( ef.Type[i] == 1 )
      sEF += '[mc]'+ef.m[i];
    else
      sEF += ef.m[i];
  }
  sEF = SendableString(sEF);
  return sEF;
}

/*function EFResult(percent)
{
  var j = Nr2Ex[curNr];
  if ( ExUserState[j] >= 0 )
  {
    SetEFString("Die Aufgabe wurde bereits bearbeitet.");
    AddHist("-2 "+AddEFText());
  }
  else if ( percent >= 98 )
  {
    SetEFString("Bearbeitet.");
    ExUserState[j] = percent;
    AddHist(percent+" "+AddEFText());
  }
  else if ( ExUserState[j] == -2 )
  {
    SetEFString("Noch nicht richtig. Versuchen Sie es noch einmal.");
    ExUserState[j] = -1;
    AddHist("-1 "+AddEFText());
  }
  else
  {
    SetEFString("Bearbeitet.");
    ExUserState[j] = percent;
    AddHist(percent+" "+AddEFText());
  }
  CheckSolveState();
  ShowCont();
}*/

function EFResult(percent)
{
  //alert(percent);
  if ( State == 2 )
  {
    SetEFString("Der Test wurde bereits beendet.");
    return;
  }
  var j = Nr2Ex[curNr];
  //SetEFString("Aufgabe bearbeitet.");
  var s = AddEFText();
  var s1 = s.replace(/\|/g , "").replace(/\[mc\]0/g, "");
  if ( s1 != "" )
    ExUserState[j] = percent;
  AddHist(percent+" "+AddEFText()+(top.data.display.PR ? ' '+top.data.display.PR.join(',') : ''));
  ShowCont();
}


var dCountdown;
var sCountdown = "??:??";

function CountDownString()
{
  dCountdown = Math.round(((finalTime-(new Date()).getTime()))/1000);
  if ( dCountdown < 0 )
    return "";
  var sec = dCountdown % 60;
  return (sCountdown = ""+((dCountdown-sec)/60)+":"+(sec < 10 ? '0' : '')+sec);
}

function CountDown()
{
  setTimeout("ShowCountDown('"+CountDownString()+"')", 1);
  if ( State == 1 )
    if ( dCountdown <= 0 ) // Zeit abgelaufen
    {
      FinalSend("timeout");
      alert("Die Zeit ist abgelaufen.");
    }
    else
      setTimeout("CountDown()", 1000);
}

function UpdateTopicStruct()
{
  var LastCap = new Array();
  for (var i = 0; i < nTopic; i++)
  {
    TopicDuration[i] = 0;
    IsOpen[i] = 0;
    LastCap[Deep[i]] = i;
    CapTopic[i] = ( Deep[i] == 0 ) ? -1 : LastCap[Deep[i]-1];
    aLastTopic[i] = 0;
  }

  for (var i = nTopic-1; i >= 0; i--)
    if ( CapTopic[i] != -1 )
      if ( aLastTopic[CapTopic[i]] == 1 )
        aLastTopic[i] = 0;
      else
      {
        aLastTopic[i] = 1;
        aLastTopic[CapTopic[i]] = 1
      }
}

function StopTest()
{
  if ( !window.confirm("Klicken Sie auf OK um den Test zu beenden.") )
  {
    ContinueTest();
    return;
  }
  // Test abbrechen
  setTimeout("FinalSend('user')", 500);
}

function ContinueTest()
{
  SetTopicText(curNr);
}

function Hidden(name, value)
{
  return '<input type="hidden" name="'+name+'" value="'+value+'">';
}

var Cause;
function FinalSend(cause)
{
  Cause = cause;
  InitProt(601, 0, 0);
  State = 2;
  var doc = top.data.display.document;
  doc.close();
  doc.clear();
  doc.open();
  doc.writeln(DISPLAY_HEADER, '<FORM method="POST" action="../../etest_final.php">',
    Hidden("AppName", AppName),
    Hidden("ExUserState", ExUserState.join(",")),
    Hidden("Cause", cause),
    Hidden("course", etest_course),
    Hidden("etest", etest_etest),
    Hidden("user", etest_user),
    Hidden("ETestSessionId", etest_session),
    '</FORM></BODY>');
  doc.close();
  setTimeout("top.data.display.document.forms[0].submit()", 20);
}

var SumPoints;
var PointArray;
var Points = 0;
var GradeAddText = ""
var PrintForm = "";

function CourseAsigned(course, sumPoints, pointArray, addtext, printform)
{
  CourseName = course;
  SumPoints = sumPoints;
  PointArray = pointArray;
  GradeAddText = addtext;
  PrintForm = printform;
  Points = 0;
  for (var i = 0; i < PointArray.length; i++)
    Points += 1*PointArray[i];
  AddProt(Cause+" => "+course);
  window.open(base + '../data/final.php', "display");
  ShowTitle("Ergebnis");
}

// Ablaufprotokoll

function Hidden(name, s)
{
  var s1 = '<input type="hidden" name="' + name + '" value="' + s + '">\n';
  return s1;
}

function SendProt(starttime, action, exid, duration, result, data)
{
  if ( State < 1 )
    return;
  var doc = top.send.document;
  doc.close()
  doc.open();
  //doc.defaultCharset = "UTF-8";
  doc.writeln('<head><title>Leveling Return</title>',
  	'<meta http-equiv="content-type" content="text/html; charset=UTF-8" />',
  	'</head>\n<body bgcolor="#800000">\n',
    '<form method="POST" action="', base, '../../etest_addprot.php">\n',
    Hidden("AppName", AppName),
    Hidden("course", etest_course),
    Hidden("etest", etest_etest),
    Hidden("user", etest_user),
    Hidden("session", etest_session),
    Hidden("starttime", starttime),
    Hidden("action", action),
    Hidden("exid", exid),
    Hidden("duration", duration),
    Hidden("result", result),
    Hidden("data", data),
    '</form></body>');
  doc.close();
  setTimeout("top.send.document.forms[0].submit()", 20);
}

function sResultList()
{
  if ( State == 0 )
    return 'Noch keine Aufgaben geladen.';
  var s = '<table border="0" align="center">';
  var BlockVals = new Array();
  var BlockNr = new Array();
  var j = ExN.length; // Anzahl der Balken
  var iEx = 0;
  var Sum = 0;
  // Blöcke drucken
  for (var i = 0; i < j; i++)
  {
    s += '<tr><td><B>'+Name[Block2Nr[i]]+'</B>&nbsp; &nbsp;</td><td align="right">'+PointArray[i]+
         '</td><td align="left">Pkt.</td></tr>'
    Sum += 1*PointArray[i];
  }
  s += '<tr><td height="2" colspan="3"><hr></td></tr>'+
       '<tr><td><B><?php print_string('total', 'etest')?></B>&nbsp; &nbsp;</td><td align="right"><B>'+Sum+
       '</B></td><td align="left"><b><?php print_string('ptsOf', 'etest')?> '+SumPoints+'</b></td></tr>';
  return s + '</table>';
}

function Master()
{
  if ( window.prompt("Master-Password: ", "") != "Selkirk" )
    return;
  InitProt(603, 0, 0);
  var RemainingTime1 = Math.round(((finalTime-(new Date()).getTime()))/1000);
  var RemainingTime2 = 1*window.prompt("Remaining Time in min:", RemainingTime1/60)*60;
  AddProt("RemainingTime: "+RemainingTime1 + " sec => " + RemainingTime2+ " sec");
  finalTime = (new Date()).getTime()+RemainingTime2*1000;
}

// adds moodle etest

var etest_course = "";
var etest_etest = "";
var etest_user = "";
var etest_session = "";
