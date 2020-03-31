<?php

function listDirContent($dir, $level = 1) {
  $hideFiles = array(".", "..", ".DS_Store","Project IOs online.url");
  $dirEntry = scandir($dir, SCANDIR_SORT_ASCENDING);

  // counting files in dir
  $fileCount = 0;
  if (!is_array($dirEntry)) return false;
  foreach ($dirEntry as $key => $value) {
    $valueWithDir = $dir. "/" . $value;

    // remove hidding files
    if (!in_array($value,$hideFiles)) {
      if (is_dir($valueWithDir)) {
        $filearray[$value] = listDirContent($valueWithDir, $level+1);
      }
      if (is_file($valueWithDir)) {
        $filearray[$value] = $fileCount;
      }

    }

    $fileCount++;
  }
  return $filearray;
}

// output
function outputResults ($res,$level=0) {
  $loopCounter=0;
  if (!is_array($res)) return false;
  foreach ($res as $key => $value) {

    // magic to remove unneeded lines
    if($loopCounter>0) echo "<tr>";
    if ($loopCounter>0 && $level> 0)
      echo str_repeat("<td></td>",$level);

    // setting font color to highlight probable errors
    $fontColor = "orange";
    if($value!=0) $fontColor="green";

    // providing link to top url
    $link = "";
    if ($level == 0) $link = '<a href="https://nxp1.sharepoint.com/:f:/r/sites/cipadmin/Shared%20Documents/Projects%20Administration/40_projects/42_Running_Projects/' . $key . '"> [Go]</a>';

    // generating output
    echo "<td><font color=\"$fontColor\">$key $link</font></td>";
    outputResults($value, $level+1);
    if ($loopCounter > 0) {
      echo "</tr>";
    }
    $loopCounter++;

  }
}

$res = listDirContent("/foldercheck/");
?>

<html>
<head>
  <style>
/* DEMO STYLES */
.table-show {
  empty-cells: show;
}

.table-hide {
  empty-cells: hide;
}

/* PRESENTATONAL STYLES */
body {
  background: #D3D3D3;
  color: #2F4F4F;
  font-family: Helvetica;
}

table {
  margin: 25px auto;
}

td {
  background: #fff;
  border: 1px solid #999;
  padding: 10px 15px;
  color: green;
  cursor: pointer;
}

td:hover {
  background: #eaeaea;
}


</style>
</head>
<body>
<h1>Folder status of CIP Admin (<?php echo date("Y-m-d"); ?>)</h1>
<p>Sanity check of our sharepoint:
<ul>
  <li>Please check the yellow highlighted areas, because they seem to be missing documents. This may be due to the status of the project (e.g. still running - thus no final report).
<li>Rather than deleting folders (like FPP, OPP) please put a text file in them named "not applicable.txt".
</ul>
To get directly to the corresponding sharepoint - click on the GO link behind the project name.
</p>

<table class="table-show">
<?php
  outputResults($res);
?>
</table>
</body>
</html>
