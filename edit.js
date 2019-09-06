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
 * JavaScript library for the etest module editing interface.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// sudata as JS
// name, must, type, comment, data
var suData = new Array();
// grdata as JS
// id, minpoints, shortname, longname, addtext
var grData = new Array();
var maxPoints = 0;
var str = new Array();

// Initialise everything on the etest settings form.
function etest_settings_init(Y, str1, suData1, grData1, maxPoints1, initFocus) {
    str = str1;
    // subuser
    // Fix für Moodle2.0
    if ( !Y.one('#id_useSubusers') ) {
        Y.one(document.forms[0].useSubusers).set('id', 'id_useSubusers');
    }
    Y.on('change', function(e) {
        Y.one('#id_subuserPanel').setStyle('display', Y.one('#id_useSubusers').get('checked') ? '' : 'none');
    }, '#id_useSubusers');
    Y.one('#id_subuserPanel').setStyle('display', Y.one('#id_useSubusers').get('checked') ? '' : 'none');
    suData = suData1;
    // grade
    grData = grData1;
    if ( !grData ) {
        grData = new Array();
    }
    maxPoints = maxPoints1;
    // printform
    Y.on('change', function(e) {
        Y.one('#id_printformArea').setStyle('display', Y.one('#id_noPrintButton').get('checked') ? 'none' : '');
    }, '#id_noPrintButton');
    Y.one('#id_printformArea').setStyle('display', Y.one('#id_noPrintButton').get('checked') ? 'none' : '');

    // show
    show_suarea();
    etest_grArea_show();
    // add onSubmit
    Y.on('submit', function(e) {
        var x,y;
        if (self.pageYOffset) // all except Explorer
        {
            x = self.pageXOffset;
            y = self.pageYOffset;
        }
        else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
        {
            x = document.documentElement.scrollLeft;
            y = document.documentElement.scrollTop;
        }
        else if (document.body) // all other Explorers
        {
            x = document.body.scrollLeft;
            y = document.body.scrollTop;
        }
        var fm = document.forms[0];
        fm.redirectAdd.value = "xCursor="+x+"&yCursor="+y;
        return true;
    }, '#mform1');
    // scroll to initFocus
    if ( initFocus ) {
        etest_show_exblock(initFocus[0]);
        etest_show_ex(initFocus[0], initFocus[1]);
        window.scrollTo(initFocus[2], initFocus[3]);
    }
}

// Subuser area

function etest_suArea() {
    var s = "";
    for (var i = 0; i < suData.length; i++) {
        s += "<tr><td>";
        s += '<input type="button" name="su_del_'+i+'" value="Del" onclick="su_del('+i+')" title="'+str['delsufield']+'"></td><td>'+
             '<input type="text" name="su_name_'+i+'" value="'+suData[i]['name']+'"></td><td>'+
             '<input type="checkbox" name="su_must_'+i+'"'+(suData[i]['must'] == true ? ' checked' : '')+'></td><td>'+
             '<select name="su_type_'+i+'" onchange="su_switch(this.value, '+i+')">'+
             '  <option value="text"'+(suData[i]['type'] == "text" ? ' selected' : '')+'>'+str['textfield']+'</option>'+
             '  <option value="date"'+(suData[i]['type'] == "date" ? ' selected' : '')+'>'+str['datefield']+'</option>'+
             '  <option value="combo"'+(suData[i]['type'] == "combo" ? ' selected' : '')+'>'+str['combofield']+'</option>'+
             '</select></td><td>'+
             '<input type="text" name="su_comment_'+i+'" size="50" value="'+suData[i]['comment']+'" />'+
             '</td></tr><tr id="su_combotr_'+i+'" style="display:'+((suData[i]['type'] == "combo") ? "table-row" : "none")+'">'+
             '<td colspan="4" valign="top" align="right">'+str['su_combodata']+':</td>'+
             '<td><textarea name="su_data_'+i+'" cols="40" rows="5"  class="form=textarea">'+(""+suData[i]['data']).replace(/<br>/g, "\r\n")+'</textarea>';
        s += '</td></tr>';
    }
    return s;
}

function su_switch(value, id) {
    // doppelt wg. IE vs. FF
    document.getElementById('su_combotr_'+id).style.display = (value == "combo") ? "block" : "none";
    document.getElementById('su_combotr_'+id).style.display = (value == "combo") ? "table-row" : "none";
}

function show_suarea() {
    document.getElementById('subuserFields').innerHTML =
        '<table style="border:solid 1px lightgray;border-collapse:collapse" rules="all">'+
        '    <tr style="background-color:lightgray">'+
        '        <th>&nbsp;</th>'+
        '        <th>'+str['subuserFieldname']+'</th>'+
        '        <th>'+str['subuserFieldmust']+'</th>'+
        '        <th>'+str['subuserFieldtype']+'</th>'+
        '        <th>'+str['subuserFieldcomment']+'</th>'+
        '    </tr>'+
        etest_suArea()+
        ' <tr><td colspan="5"><i>'+str['subuser_new']+':</i></td></tr>'+
        '  <tr>'+
        '    <td>&nbsp;</td>'+
        '  <td><input type="text" name="newsu_name" size="14" value="" /></td>'+
        '  <td><input type="checkbox" name="newsu_must" /></td>'+
        '  <td>'+
        '    <select name="newsu_type"  onchange="su_switch(this.value, \'new\')">'+
        '        <option value="text">'+str['textfield']+'</option>'+
        '        <option value="date">'+str['datefield']+'</option>'+
        '        <option value="combo">'+str['combofield']+'</option>'+
        '    </select>'+
        '     </td>'+
        '  <td>'+
        '    <nobr><input type="text" name="newsu_comment" size="45" value=""/>'+
        '    <input type="button" name="newsu_add" value="Add" onclick="su_addnew()" title="'+str['addsufield']+'"></nobr>'+
        '  </td>'+
        ' </tr>'+
        ' <tr id="su_combotr_new" style="display:none">'+
        '  <td colspan="4" valign="top" align="right">'+str['su_combodata']+':<br></td>'+
        '  <td><textarea name="newsu_data" cols="40" rows="5"  class="form=textarea"></textarea></td>'+
        ' </tr>'+
        '</table>';
}

function su_del(id)
{
    su_makesuData();
    suData.splice(id, 1);
    show_suarea();
}

function su_addnew()
{
    su_makesuData();
    var newSu = new Array();
    newSu['name'] = document.forms.mform1['newsu_name'].value;
    newSu['must'] = document.forms.mform1['newsu_must'].checked;
    newSu['type'] = document.forms.mform1['newsu_type'].value;
    newSu['comment'] = document.forms.mform1['newsu_comment'].value;
    newSu['data'] = document.forms.mform1['newsu_data'].value;
    suData[suData.length] = newSu;
    show_suarea();
}

function su_makesuData()
{
    for (var i = 0; i < suData.length; i++)
    {
        suData[i]['name'] = document.forms.mform1['su_name_'+i].value;
        suData[i]['must'] = document.forms.mform1['su_must_'+i].checked;
        suData[i]['type'] = document.forms.mform1['su_type_'+i].value;
        suData[i]['comment'] = document.forms.mform1['su_comment_'+i].value;
        suData[i]['data'] = document.forms.mform1['su_data_'+i].value;
    }
}

// grade area

function etest_grArea() {
    var s = "";
    var lastPoints = maxPoints;
    for (var i = 0; i < grData.length; i++) {
        s += '<tr>'+
             '  <td>'+lastPoints+'</td>'+
             '  <td>&nbsp;-&nbsp;</td>'+
             '  <td><input type="hidden" name="grade_id_'+i+'" value="'+grData[i]['id']+'"/><input type="text" name="grade_minpoints_'+i+'" size=4 value="'+grData[i]['minpoints']+'"></td>'+
             '  <td><input type="text" name="grade_shortname_'+i+'" size=20 value="'+grData[i]['shortname']+'"></td>'+
             '  <td><input type="text" name="grade_longname_'+i+'" size=50 value="'+grData[i]['longname']+'"></td>'+
             '  <td><input type="text" name="grade_addtext_'+i+'" size=40 value="'+grData[i]['addtext']+'"></td>'+
             '  <td><input type="button" name="delgrade_'+i+'" value="Del" onclick="etest_grArea_del('+i+')" title="'+str['delex']+'" onclick=")"></td>'+
             '</tr>';
        lastPoints = grData[i]['minpoints']-1;
    }
    if ( lastPoints >= 0 )
    {
        s += '<tr>'+
             '  <td>'+lastPoints+'</td>'+
             '  <td>&nbsp;-&nbsp;</td>'+
             '  <td>0</td>'+
             '  <td colspan="4"><i>'+str['grade_empty']+'</i></td>'+
             '</tr>';
    }
    return s;
}

function etest_grArea_show() {
    document.getElementById('gradeFields').innerHTML =
        '<table style="border:solid 1px lightgray;border-collapse:collapse" rules="all">'+
        '    <tr style="background-color:lightgray">'+
        '        <th colspan="3">'+str['points']+'</th>'+
        '        <th>'+str['grade_shortname']+'</th>'+
        '        <th>'+str['grade_longname']+'</th>'+
        '        <th>'+str['grade_addtext']+'</th>'+
        '        <th>&nbsp;</th>'+
        '    </tr>'+
        etest_grArea()+
        ' <tr><td colspan="7"><i>'+str['grade_new']+'</i></td></tr>'+
        '  <tr>'+
        '    <td>?</td>'+
        '    <td>-</td>'+
        '    <td><input type="hidden" name="grade_id_new" value=""/><input type="text" name="grade_minpoints_new" size="4" value="" /></td>'+
        '    <td><input type="text" name="grade_shortname_new" size="20" value=""/></td>'+
        '    <td><input type="text" name="grade_longname_new" size="50" value=""/></td>'+
        '    <td><input type="text" name="grade_addtext_new" size="40" value=""/></td>'+
        '    <td><input type="button" name="newgr_add" value="Add" onclick="etest_grArea_addnew()" title="'+str['addgrade']+'"></td>'+
        '  </tr>'+
        '</table>';
}

function etest_grArea_del(id)
{
    if ( confirm(str['delgrade_confirm']) )
    {
        etest_grArea_makeData();
        grData.splice(id, 1);
        etest_grArea_show();
    }
}

function etest_read_form_grade(suffix) {
    var grade = new Array();
    grade['id'] = document.forms.mform1['grade_id_'+suffix].value;
    grade['minpoints'] = document.forms.mform1['grade_minpoints_'+suffix].value;
    grade['shortname'] = document.forms.mform1['grade_shortname_'+suffix].value;
    grade['longname'] = document.forms.mform1['grade_longname_'+suffix].value;
    grade['addtext'] = document.forms.mform1['grade_addtext_'+suffix].value;
    return grade;;
}

function etest_grArea_addnew()
{
    etest_grArea_makeData();
    grData[grData.length] = etest_read_form_grade('new');
    grData.sort(etest_grSort);
    etest_grArea_show();
}

function etest_grArea_makeData()
{
    for (var i = 0; i < grData.length; i++)
    {
        grData[i] = etest_read_form_grade(i);
    }
    grData.sort(etest_grSort);
}

function etest_grSort(a, b)
{
    return b['minpoints']-a['minpoints'];
};

// exercises

function etest_show_exblock(i) {
    if ( i == -1 ) {
        return;
    }
    for (var a = 0; true; a++) {
        var node = document.getElementById("exblock_array_"+a);
        if ( !node ) {
            break;
        }
        node.style.backgroundColor = "#DDDDDD";
        document.getElementById("ex_add_"+a).style.display = "none";
    }
    var el = document.getElementById("ex_add_"+i);
    try
    {
        el.style.display = "table-row";
    }
    catch(err)
    {
        el.style.display = "block";
    }
    document.getElementById("exblock_array_"+i).style.backgroundColor = "#FFFFFF";
    document.forms[0].curExBlock.value = i;
    document.forms[0].curEx.value = -1;
    return true;
}

function etest_show_ex(i, j) {
    if ( i == -1 ) {
        return;
    }
    if ( j == -1 ) {
        return;
    }
/*    for (var a = 0; ; a++) {
        if ( !document.getElementById("exalt_file_"+a+"_0") )
            break;
        for (var b = 0; ; b++) {
            var node = document.getElementById("exalt_file_"+a+"_"+b);
            if ( !node )
                break;
            node.style.display = "none";
            document.getElementById("ex_array_"+a+"_"+b).style.backgroundColor = "#DDDDDD";
        }
    }
    var el = document.getElementById("exalt_file_"+i+"_"+j);
    if ( el ) {
        try {
            el.style.display = "table-row";
        }
        catch(err) {
            el.style.display = "block";
        }
    }*/
    document.getElementById("ex_array_"+i+"_"+j).style.backgroundColor = "#FFFFFF";
    document.forms[0].curExBlock.value = i;
    document.forms[0].curEx.value = j;
    return true;
}
