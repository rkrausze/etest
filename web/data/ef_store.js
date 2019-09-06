// ef_store for TEE  ------------------------------------------------
// TEE, 03.07.2002, TU Dresden, R. Krauße, EF-Version 0.85

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

function Store(TheWin)
{
  win = TheWin;
  if ( !win.Type )
    return;
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 0 ) // MCR
    {
      win.m[nr] = -1;
      var i = 0;
      while ( SGetElAI(nr, i) )
      {
        if ( SGetElAI(nr, i).checked )
          win.m[nr] = i;
        i++;
      }
    }
    else if ( win.Type[nr] == 1 ) // MCP
      win.m[nr] = SGetElA(nr).options.selectedIndex;
    else if ( win.Type[nr] == 2 ) // MCX
    {
      for (i=0; i < win.d[nr].length; i++)
        win.m[nr][i] = (SGetElAI(nr, i).checked) ? "1" : "0";
    }
    else if ( win.Type[nr] <= 6 ) // VR - LT
      win.m[nr] = SGetElA(nr).value;
  }
  mStore = win.m;
  SolvedStore = win.Solved;
  iHintStore = win.iHint;
  TargetTextStore = win.TargetText;
}

function Restore(TheWin)
{
  win = TheWin;
  RestoreWin();
}

function RestoreWin()
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
  win.m = mStore;
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 0 ) // MCR
    {
      if ( win.m[nr] != -1 && SGetElAI(nr, win.m[nr]) != 0 )
        SGetElAI(nr, win.m[nr]).checked = true;
    }
    if ( win.Type[nr] == 1 ) // MCP
      SGetElA(nr).options.selectedIndex = win.m[nr];
    else if ( win.Type[nr] == 2 ) // MCX
    {
      for (i=0; i < win.d[nr].length; i++)
        SGetElAI(nr, i).checked = (win.m[nr][i] == 1);
    }
    else if ( win.Type[nr] <= 7 ) // VR - TF
      SGetElA(nr).value = win.m[nr];
    else if ( win.Type[nr] == 8 ) // TFM
      SGetElA(nr).value = win.m[nr][0];
    else if ( win.Type[nr] == 9 ) // TLS
      win.SetTLSEntry(nr, win.m[nr]);
    else if ( win.Type[nr] == 10 ) // TLM
      win.RefreshTLM(nr);
  }
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 11 ) // SLS
      win.RefreshSLS(nr);
  }
  win.Solved = SolvedStore;
  win.iHint = iHintStore;
}

