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
 * Language strings for english.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General strings.
$string['etest'] = 'E-Test';

$string['modulenameplural'] = 'E-Tests';
$string['modulename'] = 'E-Test';

$string['modulename_help'] = 'Mit dem E-Test-Modul können Folgen von mit dem EF-Editor erzeugten Aufgaben unter kontrollierten Bedingungen abgearbeitet werden (Einstufungstests).
Für die einzelnen Aufgaben können auch kleine Aufgabenpools angegeben werden, aus denen die tatsächlichen Aufgaben zufällig ausgewählt werden.

Information zum E-Test gibt es unter  '.'
[http://studierplatz2000.tu-dresden.de](http://studierplatz2000.tu-dresden.de "Studierplatz Homepage")';
$string['modulename_link'] = 'mod/etest/view';

$string['pluginadministration'] = 'E-Test Administration';
$string['pluginname'] = 'E-Test';

// Access rights.
$string['etest:addinstance'] = 'Neuen E-Test hinzufügen';
$string['etest:attempt'] = 'E-Test abarbeiten';
$string['etest:viewmyattempts'] = 'Eigene Sessions im Protokoll sehen';
$string['etest:viewallattempts'] = 'Alle Sessions im Protokoll sehen';
$string['etest:deleteattempts'] = 'Sessions löschen';
$string['etest:addpoints'] = 'Extrapunkte zuweisen';
$string['etest:recalc'] = 'Recalc-en eines E-Tests';
$string['etest:archiveattempt'] = 'Sessions archivieren';
$string['etest:viewarchive'] = 'Archivierte Sessions ansehen';

// Mod form.
$string['introref'] = 'Datei für Intro-Seite';
$string['introref_help'] = 'Hier können sie eine HTML-Datei hochladen, die später unmittelbar beim Öffnen des E-Testes angezeigt wird.
Auf ihr sollten die Modalitäten des Test u.ä. für die Nutzer erläutert werden, sie kann erläuternde Kommentare, Links zu Beispielaufgaben etc. enthalten.  '.'
Zusätzliche Dateien (Grafiken, Beispielaufgaben) bitte auch hier hochladen und in der HTML-Seite relativ verlinken.
Wenn sie mehrere Dateien hochladen markieren sie bitte die HTML-Datei als Hauptdatei.';

$string['sessionhandling'] = 'Sessionhandling / Unternutzer';

// Subuser.
$string['subuser'] = 'Unternutzer';
$string['useSubuser'] = 'Unternutzer verwenden';
$string['useSubuser_help'] = 'Wenn mehrere Person einen Studierplatz über ein und dasselbe Login verwenden sollen
(z.B. auch beim Gastlogin), ist es sinnvoll zusätzliche Daten der Person zu erfassen.  '.'
Hier kann festgelegt werden, welche Daten dies sein sollen.
Wenn ein Nutzer dann den Studierplatz startet, wird er aufgefordert, diese Daten einzugeben.';
$string['subuserLogData'] = 'Daten die beim einloggen erfasst werden sollen';
$string['subuserFieldname'] = 'Bezeichnung';
$string['subuserFieldmust'] = 'Pflicht';
$string['subuserFieldtype'] = 'Typ';
$string['subuserFieldcomment'] = 'Kommentar';
$string['firstname'] = 'Vorname';
$string['lastname'] = 'Familienname';
$string['birthday'] = 'Geburtsdatum';
$string['dd.mm.yyyy'] = '(tt.mm.jjjj)';
$string['subuser_new'] = 'Hier können Sie ein neues Datenfeld anlegen.';
$string['textfield'] = 'Textfeld';
$string['datefield'] = 'Datum';
$string['combofield'] = 'Combobox';
$string['addsufield'] = 'Neues Datenfeld hinzufügen';
$string['delsufield'] = 'Datenfeld löschen';
$string['su_combodata'] = 'Einträge der Combobox';
$string['subuserdata'] = 'Daten der Unternutzer';
$string['subuserdata_help'] = 'Für diese Daten könne sie definieren:

* die Bezeichnung (Kurzname)
* ob die Angabe erforderlich oder optional sein soll
* den Typ (Freitext, Datumsfeld, Combobox)
* ein erläuternder Text, der den Nutzern angezeigt wird

Vorname, Nachname und Geburtsdatum werden standardmässig angeboten
(wenn sie diese Werte nicht wollen, können sie sie einfach löschen (mittels des "Del"-Knopfes am Zeilenanfang)).  '.'
Um neue Werte anzugeben tragen sie die gewünschten Werte in die untere Zeile ein und fügen diese mit dem "Add"-Knopf hinzu.';
$string['combodef'] = 'Comboboxen definieren';
$string['combodef_help'] = 'Um Comboboxen zu definieren wählen sie einfach als Typ "Combobox".  '.'
Jetzt können Sie im Textbereich, der sich darunter neu öffnet, die Einträge angeben, die die Nutzer später angezeigt bekommen.  '.'
Jede Zeile repräsentiert einen Eintrag.';
$string['subuserprotname'] = 'Anzeige im Protokoll';
$string['subuserprotname_help'] = 'Hier können sie definieren, aus welchen Unternutzerdaten im Protokoll der Name des Nutzers zusammengesetzt wird.
Geben sie dazu die Bezeichnung der Felder eingeschlossen in \'$\' an.  '.'
Z.B. wenn sie die Datenfelder \'Vorname\', \'Nachname\' and \'Klasse\' haben und hier angeben
> *$Vorname$, $Nachname$, ($Klasse$)*

dann wird der Nutzer beispielsweise als
> Mueller, Nina (4b)

angezeigt .  '.'
Wenn sie *$USER$* verwenden, wird der Name des Moodle-Nutzers verwendet und *$SESSION$* liefert die Session-Nummer/Id.';

$string['resultpage'] = 'Auswertungsseite';
$string['noresultdiagram'] = '<b>Kein</b> Säulendiagramm';
$string['noresultdiagram_explain'] = ' <b>keine</b> Darstellung der Ergebnis-Punkte am Ende als Säulendiagramm';
$string['noprintbutton'] = '<b>Kein</b> Drucken-Knopf';
$string['noprintbutton_explain'] = ' <b>kein</b> Drucken-Knopf auf der Auswertungsseite anzeigen';

$string['timelimit'] = 'Zeitlimit';
$string['timelimit_help'] = 'Zeitlimit für den Test (in Minuten). Keine Angabe oder 0 heißt kein Zeitlimit.';

$string['maxsession'] = 'Maximale Versuche';
$string['maxsession_help'] = 'Wie oft ein Nutzer bzw. Unternutzer den Test machen darf; keine Angabe = beliebig oft.';

$string['nocontinuesession'] = 'Kein fortsetzen';
$string['nocontinuesession_explain'] = ' Abgebrochene Sessions dürfen <u>nicht</u> fortgesetzt werden.';

$string['alwaysopen'] = 'Jederzeit';
$string['neverclosed'] = 'Nie';
$string['specifictime'] = 'Spezifische Zeit';

$string['exercises'] = 'Aufgaben';
$string['exblock'] = 'Aufgabenblock';
$string['exblocks'] = 'Aufgabenblöcke';
$string['exblocks_help'] = 'Mehrere inhaltlich zusammengehörige Aufgaben werden in Aufgabenblöcke zusammengefasst,
z.B. "Grammatik", "Übersetzung".
Sie werden im Menü später als Hauptpunkt mit Unterpunkten (den Aufgaben) dargestellt.  '.'
Für solche Blöcke können auch Mindestpunktzahlen vergeben werden.
Schafft der Nutzer später in einem Blöcke die Mindestpunktzahl nicht,
wird er automatisch einen Grad schlechter bewertet.';
$string['upexblock'] = 'Aufgabenblock nach oben verschieben';
$string['downexblock'] = 'Aufgabenblock nach unten verschieben';
$string['delexblock'] = 'Aufgabenblock löschen';
$string['delexblock_confirm'] = 'Soll dieser Aufgabenblock wirklich gelöscht werden?';
$string['exblockname'] = 'Name';
$string['exblockminpoints'] = 'Mindestpunktzahl';
$string['maxpoints'] = 'Maximalpunktzahl';
$string['addexblock'] = 'Neuen Aufgabenblock hinzufügen';
$string['expointscontinous'] = 'relative Punktvergabe';
$string['new_block'] = 'Neuer Block';

$string['ex'] = 'Aufgabe';
$string['upex'] = 'Aufgabe nach oben verschieben';
$string['downex'] = 'Aufgabe nach unten verschieben';
$string['delex'] = 'Aufgabe löschen';
$string['delex_confirm'] = 'Soll diese Aufgabe wirklich gelöscht werden?';
$string['exname'] = 'Name';
$string['expoints'] = 'Punktzahl';
$string['addex'] = 'Neue Aufgabe hinzufügen';

$string['exalt_file'] = 'Aufgabendateien';
$string['exalt_file_help'] = 'Hier können Sie aufgabendateien hochladen, und zwar mehrere, die dann bei verschiedenen Nutzern jeweils als alterativen verwendet werdemn ...ODO';

$string['useexnames'] = 'Aufgabennamen verwenden';
$string['useexnames_explain'] = ' die oben angegebenen Aufgabennamen im Test anzeigen, ansonsten werden generische Namen "Aufgabe <i>x</i>" verwendet';

$string['grades'] = 'Bewertung';
$string['points'] = 'Punkte';
$string['grade_shortname'] = 'Kurzname';
$string['grade_longname'] = 'Langer Name';
$string['grade_addtext'] = 'Zusatztext';
$string['delgrade'] = 'Wertung löschen';
$string['delgrade_confirm'] = 'Soll diese Wertung wirklich gelöscht werden?';
$string['grade_empty'] = 'Keine Wertung angegeben.';
$string['grade_new'] = 'Hier können Sie eine neue Wertung angegeben:';
$string['addgrade'] = 'Neue Wertung hinzufügen';

$string['finalpages'] = "Ergebnisseiten";
$string['printform'] = 'Alternative Druck-Seite';
$string['printform_help'] = 'Hier haben Sie die Möglichkeit das Layout für die am Ende zu druckende Seite festzulegen.  '.'
Es können auch externe Links auf Bilder etc. eingefügt werden.
Die tatsächlichen Daten des Nutzers werden über die Platzhalter eingefügt.  '.
'Wenn dieses Feld leer bleibt, wird eine Standardseite mit den Daten angezeigt.';
$string['printformplaceholders'] = 'Platzhalter';
$string['printform_explain'] =
    'Folgende Platzhalter können verwendet werden:<br>'.
    '<table border="0" cellpadding="2">'.
    '<tr><td>$FIRSTNAME$ ... Vorname</td><td>&nbsp;</td><td>$COURSENAME$ ... Langer Name des erreichten Kurses</td></tr>'.
    '<tr><td>$LASTNAME$ ... Nachname</td><td>&nbsp;</td><td>$ADDTEXT$ ... Zusatztext des erreichten Kurses</td></tr>'.
    '<tr><td>$BIRTHDAY$ ... Geburtstag</td><td>&nbsp;</td><td>$RESULTLIST$ ... Liste der erreichten Punkte in den Blöcken</td></tr>'.
    '<tr><td>$TITLE$ ... Titel des Tests</td><td>&nbsp;</td><td>$USERDATA$ ... Tabelle der Nutzerdaten</td></tr>'.
    '<tr><td>$DATE$ ... Aktuelles Datum</td><td>&nbsp;</td><td>$bezeichnung$ or $USER0$, $USER1$ usw. ... eingebene Werte der hinzugefügten Unternutzerfelder</td></tr>'.
    '</table>';

// Settings page.
$string['usedesignsfortest'] = 'Moodeldesings für eingebettete Seiten im E-Test benutzen';
$string['usedesignsfortest_explain'] = 'Die eingebetteten Seiten verschiedener Designs enthalten unerwünschte Rahmen etc. Mit dieser Option können Sie entscheiden, ob die Designs für diese Seiten angewendet werden sollen.';
$string['usedesignsforprot'] = 'Moodeldesings für eingebettete Seiten im Protokoll benutzen';
$string['usedesignsforprot_explain'] = 'Die eingebetteten Seiten verschiedener Designs enthalten unerwünschte Rahmen etc. Mit dieser Option können Sie entscheiden, ob die Designs für diese Seiten angewendet werden sollen.';

// Index page.
$string['subusers'] = 'Unternutzer/innen';
$string['sessions'] = 'Sessions';
$string['archivedusers'] = 'Archivierte Nutzer';

$string['protocol'] = 'Protokoll';
// View page.
$string['startbutton'] = 'E-Test starten';
$string['START'] = 'START';

// On attempt.
$string['BACK'] = 'ZURÜCK';
$string['FINISH_TEST'] = 'TEST BEENDEN';
$string['NEXT'] = 'WEITER';
$string['remaining_info1'] = 'Sie haben noch {$a} Minute.';
$string['remaining_info2'] = 'Sie haben noch {$a} Minuten.';
$string['ptsOf'] = 'Pkt. von ';

$string['intro'] = 'Einleitung';
$string['exercises'] = 'Aufgaben';

$string['finish_test'] = 'Test beenden';
$string['result'] = 'Ergebnis';

// Subuser login.
$string['fillFields'] = 'Bitte füllen Sie die Felder aus.';
$string['dateComment'] = '(tt.mm.jjjj)';
$string['loginButton'] = 'Weiter';
$string['combo_please_select'] = 'Bitte auswählen!';
$string['missingField'] = 'Bitte füllen Sie das Feld \'{$a}\' aus.';
$string['formatField'] = 'Bitte prüfen Sie die Eingabe im Feld \'{$a}\'. Sie entspricht nicht dem erwartetem Format.';
$string['missingSelect'] = 'Bitte wählen Sie im Feld \'{$a}\' eine gültige Option aus.';

// Login page.
$string['ses_exists1'] = 'Bearbeitung am {$a}';
$string['ses_exists0'] = 'Folgende Bearbeitungen/Sitzungen könnten fortgesetzt werden:';
$string['ses_exists3'] = 'Sie können aber auch einen neuen Test starten.';
$string['ses_exist'] = 'Für den Benutzer <b>{$a}</b> existieren bereits Bearbeitungen/Sitzungen.';
$string['ses_continue_button'] = 'Diese Sitzung fortsetzen';
$string['ses_new_button'] = 'Neuen Test starten';
$string['ses_cancel_button'] = 'Abbrechen, zurück zum Login';
$string['maxSessionReached'] = 'Hinweis: Sie können keine weiteren Bearbeitungsversuch für diesen E-Test starten. Dieser E-Test kann pro Person nur {$a} x bearbeitet werden.';

$string['testOver'] = 'Test bereits beendet';
$string['testOver_explain'] = 'Für diese Nutzerdaten wurde der Test bereits abgeschlossen. Daher kann er nicht fortgesetzt werden.';

// Cancel page.
$string['cancel_question'] = 'Möchten Sie den Test jetzt wirklich beenden?';
$string['cancel_state'] = '(Sie haben #SOLVED# von #TOTAL# Aufgaben bearbeitet.)';
$string['cancel_button_yes'] = 'Ja, ich möchte den Test beenden.';
$string['cancel_button_no'] = 'Nein, ich möchte zurück zu den Aufgaben.';

// Final page.
$string['print_button'] = 'Resultat drucken';
$string['points_of'] = '#POINTS# von #TOTALPOINTS# Punkten';
$string['total'] = 'Gesamt';
$string['correctly_solved'] = 'richtig gelöst';
$string['partially_solved'] = 'zum Teil richtig gelöst';
$string['wrong_solved'] = 'falsch gelöst';
$string['not_solved'] = 'nicht bearbeitet';

// Print page.
$string['print1'] = 'Sie haben den Eingangstest erfolgreich beendet und haben folgendes Niveau erreicht:';

// Report.

// Header.
$string['userlist'] = 'User-Liste';
$string['courselist'] = 'Kurs-Liste';
$string['leveling'] = 'Leveling';
$string['userhist'] = 'Ablauf';
$string['recalc'] = 'Recalc';
$string['exoverview'] = 'Aufgabenübersicht';
$string['exentries'] = 'Aufgabeneinträge';
$string['usermatrix'] = 'User-Matrix';

$string['displayonpage'] = 'Anzeigen (HTML)';
$string['downloadtext'] = 'Text-Export (CSV)';
$string['downloadexcel'] = 'Excel-Export';
$string['refresh'] = 'Aktualisieren';

// User list.
$string['duration'] = 'Dauer';
$string['worse'] = 'vers.';
$string['origPts'] = 'orig. Pt.';
$string['origGrade'] = 'orig. Kurs';
$string['recalcPts'] = 'recalc Pt.';
$string['recalcGrade'] = 'recalc Kurs';
$string['oldPts'] = 'altes Lev Pt.';
$string['oldGrade'] = 'altes Lev. Kurs';

$string['orig-data'] = 'Orig.-Data';
$string['recalc-data'] = 'Recalc.-Data';

$string['use_as_startdate'] = 'Dieses Datum als \'ab:\'-Datum zur Einschränkung der Nutzer verwenden.';
$string['use_as_enddate'] = 'Dieses Datum als \'bis:\'-Datum zur Einschränkung der Nutzer verwenden.';

$string['delete'] = 'Löschen';
$string['delete_explain'] = 'der markierten Sessions';
$string['deleteempty'] = 'Keine Session zum Löschen ausgewählt.';
$string['deleteverify'] = 'Soll(en) diese {$a} Session(s) wirklich (unwiederbringlich) gelöscht werden?';
$string['deletedone'] = '{$a} Session(s) wurden (unwiederbringlich) gelöscht.';
$string['cancel'] = 'Abbrechen';

$string['archivetag'] = 'Archivlabel';
$string['archive'] = 'Archivieren';
$string['archive_explain'] = 'der markierten Sessions';
$string['archiveempty'] = 'Keine Session zum Archivieren ausgewählt.';
$string['archiveverify'] = 'Soll(en) diese {$a} Session(s) wirklich archiviert werden (und von der aktuellen Liste entfernt)?';
$string['archiveselecttag'] = 'Bitte geben sie ein Label an, damit diese Session(s) später im Archiv bequem wiedergefunden werden können.';
$string['archiveselecttagnew'] = 'Geben Sie ein neues Label an:';
$string['archiveselecttagold'] = 'oder benutzen sie ein vorhandenes:';
$string['archivetagneeded'] = 'Bitte geben sie ein Archiv-Label an.';
$string['archivetagonlyone'] = 'Bitte geben sie ein neues Archiv-Label an oder wählen sie ein altes. Beides gleichzeitig geht nicht.';
$string['archivedone'] = '{$a->count} Session(s) wurden mit dem Label "{$a->label}" archiviert.';
$string['displayarchive'] = 'Archiv anzeigen';
$string['displayarchive_explain'] = 'für die folgenden Label:';
$string['noarchivetagsselected'] = 'Keine Archiv-Labels ausgewäht.';
$string['leavearchive'] = 'Archiv verlassen';
$string['leavearchive_explain'] = '';
$string['unarchive'] = 'Entarchivieren';
$string['unarchive_explain'] = 'der markierten Sessions';
$string['unarchiveempty'] = 'Keine Session zum Entarchivieren ausgewählt.';
$string['unarchiveverify'] = 'Soll(en) diese {$a} Session(s) wirklich entarchiviert werden?';
$string['unarchivedone'] = '{$a} Sessions wurden entarchiviert.';

// User hist.
$string['nodata'] = 'No data.';
$string['tdelta_in_s'] = 't<sub>delta</sub> (in s)';
$string['action'] = 'Aktion';
$string['comment'] = 'Bemerkung';
$string['exercise'] = 'Aufgabe';
$string['finalsend'] = 'Final Send';
$string['text'] = 'Text';
$string['solutionDisplayed'] = 'Lösung angezeigt';

// Course list.
$string['points'] = 'Punkte';

// Exercise overview.
$string['exalt'] = 'Datei-Nr.';
$string['file'] = 'Datei';
$string['nSelected'] = "#zugeordnet";
$string['nSolved'] = "#bearbeitet";
$string['nCorrectSolved'] = "#richtig gelöst";
$string['averageSolved'] = "Ø richtig gelöst";
$string['averagePercent'] = "Ø Lösungsprozente";
$string['show_exercise'] = "Aufgabe anzeigen";

// Exercise entries.
$string['exfield'] = 'Eingabefeld';
$string['nusers'] = 'Anzahl Benutzer/Innen';
$string['exfieldentry'] = 'Eintrag im Eingabefeld';
$string['exfieldresult'] = 'Einzelne Lösungsprozente';

// Grouping.
$string['grouping'] = 'Gruppieren';
$string['grouping_no'] = 'keine Gruppierung';
$string['grouping_yes'] = 'Gruppierung nach: ';
$string['group'] = 'Gruppe';
$string['groupingText1'] = 'Wählen Sie aus, wonach die Daten gruppiert werden sollen:';

// Logging.
$string['attempt_started'] = 'E-Test gestartet';

