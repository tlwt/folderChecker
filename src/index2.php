<?php

ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

class folderCompare {

    function folderCompare() {
      $this->templateFolder = '/template';
      $this->projectFolder = '/foldercheck';
      $this->ignoreCasing = true;

      $this->hideFilesFolders = array("..", ".DS_Store","Project IOs online.url");
    }


    function folderToArray($folder="") {
      $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
      $folderArray = array();
      foreach ($rii as $entry) {
        // check if entries need to be ignored
        if (!in_array(basename($entry), $this->hideFilesFolders) &&

        is_dir($entry)) {
          $fullPath = strval($entry);
          $shortPath = str_replace($folder, "", $entry);
          $folderInfo['files'] = glob("$fullPath/*.*");
          $folderInfo['numberOfFiles'] = count($folderInfo['files']);
          $folderInfo['fullPath'] = $fullPath;
          // putting payload into archive
          $folderArray[$shortPath] = $folderInfo;
        }
      }

      ksort($folderArray, SORT_REGULAR);

      return $folderArray;
    }

    // checks if a folder is in the array
    function isFolderInArray($folder, $array) {
      return array_key_exists($folder,$array);
    }

    // compares two arrays (optionally ignoreing casing)
    function array_diff2($array1,$array2) {
      return array_diff_key($array1,$array2);
    }

    function compareTwoFolders($template, $project) {
      // reading template folder
      $template = $this->folderToArray($template);
      if ($this->ignoreCasing) {
        $template = array_change_key_case($template);
      }

      // reading project folder
      $folder = $this->folderToArray($project);
      if ($this->ignoreCasing) {
        $folder = array_change_key_case($folder);
      }
      // finding additional folders in project folder
      $additional = $this->array_diff2($folder, $template);

      // finding missing folders in project folder
      $missing =  $this->array_diff2($template, $folder);

      echo "<table>";
      echo "<thead><tr><td>Folder</td><td>vs. template</td><td>#</td><td>files</td></tr></thead>";
      foreach ($folder as $entry => $files) {
          // does the folder match a folder within the template directory
          if ($this->isFolderInArray($entry,$template)) $folderStatus = '✅';

          // is this folder an additional folder
          if ($this->isFolderInArray($entry, $additional)) $folderStatus = '🟡';

          // output
          echo "<tr><td>$entry</td><td>$folderStatus</td><td>" . $files['numberOfFiles'] ."</td><td>" . join ("<li>",$files['files']) ."</td></tr>";

      }

      foreach ($missing as $entry => $files) {
        echo "<tr><td>$entry</td>" .'<td>❌</td><td>-</td></tr>';
        }
    echo "</table>";
    }

    function run () {
        echo "<ol>";
        $projects = scandir ($this->projectFolder);
        foreach ($projects as $project) {
          if (!in_array(basename($project), $this->hideFilesFolders) && $project != ".") {
            echo "<h1>" . $project . "</h1>";
            $this->compareTwoFolders($this->templateFolder, $this->projectFolder . "/$project");
          }
        }
    }

    function run1() {
      $this->compareTwoFolders($this->templateFolder, $this->projectFolder . "/FabOS");

    }


}
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
  $fci = new folderCompare();
  $fci->run();
?>
</table>
</body>
</html>
