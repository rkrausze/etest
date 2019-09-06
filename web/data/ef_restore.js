// ef_restore for ETest  ------------------------------------------------
// restores from reloaded Value 

var mStore;
var SolvedStore;
var iHintStore;
var TargetTextStore;
var win;

function SGetElAI(nr, i)
{
  return eval("win.document.forms[0].a"+nr+"["+i+"]");
}

function SGetElA(nr)
{
  return eval("win.document.forms[0].a"+nr);
}

function RestoreReload(TheWin)
{
  win = TheWin;
  RestoreReloadWin();
}

function RestoreReloadWin()
{
  if ( !win ||
       !win.document ||
       !win.document.forms[0] ||
       !win.document.forms[0].elements )
  {
    setTimeout("RestoreWin()", 500);
    return;
  }
  if ( !win.Type )
    return;
  m = mStore.split("|");
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 0 ) // MCR
    {
      m[nr] = Number(m[nr].replace(/\[mc\]/g, ""))-1;
      if ( m[nr] != -1 && SGetElAI(nr, m[nr]) != 0 )
        SGetElAI(nr, m[nr]).checked = true;
    }
    if ( win.Type[nr] == 1 ) // MCP
      SGetElA(nr).options.selectedIndex = Number(m[nr].replace(/\[mc\]/g, ""));
    else if ( win.Type[nr] == 2 ) // MCX
    {
      m[nr] = m[nr].split(",");
      for (i=0; i < m[nr].length; i++)
        SGetElAI(nr, i).checked = (m[nr][i] == 1);
    }
    else if ( win.Type[nr] <= 7 ) // VR - TF
      SGetElA(nr).value = (typeof m[nr] == "string") ? m[nr].replace(/_/g, " ").replace(/\/\/\/\//g, "\r\n") : m[nr];
    else if ( win.Type[nr] == 8 ) // TFM
    {
      if ( m[nr].indexOf(";") == -1 ) {
        /(\s*)(.*)(\s*),*(.*),\2([^;]*)$/.exec(m[nr]);
        m[nr] = RegExp.$2+(RegExp.$4 != '' ? ','+RegExp.$4 : '');
      }
      else
      {
        /(\s*)(.*)(\s*);(.*),\2([^;]*)$/.exec(m[nr]);
        m[nr] = RegExp.$2+';'+RegExp.$4;
      }
      SGetElA(nr).value = m[nr].replace(/_/g, " ");
    }
    else if ( win.Type[nr] == 9 ) // TLS
      win.SetTLSEntry(nr, m[nr]);
    else if ( win.Type[nr] == 10 ) // TLM
    {
      win.m[nr] = m[nr].split(",");
      win.RefreshTLM(nr);
    }
  }
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 11 ) // SLS
      win.RefreshSLS(nr);
  }
}

