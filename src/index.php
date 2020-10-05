<?php
  ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

class folderCompare {

    /**
     * constructor
     * @param none
     * @return none
     * @brief initialized the folder comparisson.
     **/
    function __construct() {
      $this->templateFolder = '/template';
      $this->projectFolder = '/foldercheck';
      $this->ignoreCasing = true;

      if($this->doesNotExistOrEmpty($this->templateFolder)) $this->error("Template folder is empty");
      if($this->doesNotExistOrEmpty($this->projectFolder)) $this->error("Project folder is empty");

      $this->hideFilesFolders = array("..", ".DS_Store","Project IOs online.url");
    }

    /**
     * @brief function check if a directory is empty or does not exist
     * @param string full path of directory to check
     * @retval false: if files are in folder
     * @retval true: if no files in folder or folder does not exist
     **/
    private function doesNotExistOrEmpty($dirname)
    {
      if (!is_dir($dirname)) return true;
      foreach (scandir($dirname) as $file) {
        if (!in_array($file, array('.','..','.DS_Store'))) return false;
      }
      return true;
    }

    /**
     * @brief formats error output, so it becomes readable on the screen. Halts program after output.
     * @param string error message to display
     * @return none
     **/
    private function error($msg) {
      echo '<font color="red"><h1>Error</h1>';
      echo "$msg";
      echo "</font>";
      die();
    }

    /**
     * reads and sorts a directory into an array and puts number of files etc. in it as well.
     * Adds attributed to the array about the number of files in subfolder as well as the full path.
     * @brief reads a directory into an array and generates information
     * @param string full path to $folder which is read into an array.
     * @return array with all folder entries
     **/
    private function folderToArray($folder="") {
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
          $folderInfo['numberOfSubfolders'] = count( glob("$fullPath/*", GLOB_ONLYDIR) );
          $folderInfo['fullPath'] = $fullPath;
          // putting payload into archive
          $folderArray[$shortPath] = $folderInfo;
        }
      }
      // sort based on array
      ksort($folderArray, SORT_REGULAR);
      return $folderArray;
    }

    /**
     * prints list in folder
     * @param array containing the folder array to process.
     * @return string containing output html of all files in directory
     **/
    private function returnListOfFiles($array) {
      foreach($array as $key => $value) {
        $res = preg_match('/^(due )([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt/', basename($value), $match);
        if ($res == 1) {
          $dueDays = floor((strtotime($match[2]) - time())/86400);
          if ($dueDays<0) $dueStatus = "❌ Over ";
          if ($dueDays>0) $dueStatus = "🟡 Soon";
          if ($dueDays>30) $dueStatus = "✅";

          $returnString = "<li>📅 $dueStatus due in <b>$dueDays days</b> on " . $match[2];
        }
        else
          $returnString .= "<li>" . basename($value);
      }
      return $returnString;
    }

    /**
     * checks if a haystack string ends with a needle string. This is used to to identify work and archive folders.
     * @brief checks if a string ends with a keyword like archive or work
     * @param $string haystack containing  the haystack string
     * @param $string needle containing the needle string
     * @return boolean containing the result.
     * @retval false: if not found
     * @retval true: if found
     **/
    private function endsWith($haystack, $needle)
    {
        $needle = $needle ."/.";
        $length = strlen($needle);
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * This function determins the folder status. E.g. a green check mark if everything is ok or a yellow warning if more than one file has been found.
     * @brief evaluates and outputs the status of a folder (green, orange, red)
     * @param $string full string of path to check
     * @return string html string containing the folder status.
     **/
    private function folderStatus($folder) {
      if ($folder['numberOfFiles'] == 1) return '<span title="ok">✅</span>';
      if ($folder['numberOfSubfolders']>0) return '<span title="has subdirectories - check those">ℹ️</span>';
      if ($folder['numberOfFiles'] == 0) return '<span title="no file found - please put not applicable.txt in the directory in case not needed">❌</span>';
      if ($this->endsWith($folder['fullPath'],"work")) return '<span title="archive folder not checked for sanity">🗄</span>️';
      if ($this->endsWith($folder['fullPath'],"archive")) return '<span title="archive folder not checked for sanity">🗄</span>️';

      return '<span title="too many files - please move irrelevant files to archive subdirectory">🟡</span>';
    }

    /**
     * compares the provided template folder with the actual project folder(s) and outputs html
     * @param string $template containing the full url of the template folder
     * @param string $project contains the corresponding project folder
     * @return none
     **/
    private function compareTwoFolders($template, $project) {
      // reading template folder
      $template = $this->folderToArray($template);
      if ($this->ignoreCasing) $template = array_change_key_case($template);

      // reading project folder
      $folder = $this->folderToArray($project);
      if ($this->ignoreCasing) $folder = array_change_key_case($folder);

      // finding additional folders in project folder
      $additional = array_diff_key($folder, $template);

      // finding missing folders in project folder
      $missing =  array_diff_key($template, $folder);
      ksort($missing);

      echo '<table data-name="mytable" class="table-show">';
      echo '<thead><tr><td><span title="Sharepoint Folder">Folder</span></td><td><span title="Comparing the folder to our template structure">Tmplt</span></td><td><span title="Number of files ok in folder?">#</span></td><td><span title="List of files in directory">files</span></td></tr></thead>';
      foreach ($folder as $entry => $files) {
          // does the folder match a folder within the template directory
          if (array_key_exists($entry,$template)) $tmpFolder = '<span title="required folder exists">✅</span>';

          // is this folder an additional folder
          if (array_key_exists($entry, $additional)) $tmpFolder = '<span title="additional folder not required by template directory">➕</span>';

          // number of file
          $folderStatus = $this->folderStatus( $files);

          // file
          $fileList = $this->returnListOfFiles($files['files']);

          // output
          echo "<tr><td>$entry</td><td>$tmpFolder</td><td>$folderStatus</td><td>$fileList</td></tr>";

      }

      foreach ($missing as $entry => $files) {
        echo "<tr><td>$entry</td>" .'<td><span title="required folder missing">❌</span></td><td>-</td><td>--</td></tr>';
        }
    echo "</table>";
    }


    /**
     * public function kicking of the folder comparisson.
     * @brief starting the comparisson procedure
     * @param string if a string is provided, just that folder is compared. If no string is provided all folders will be compared.
     * @return none.
     **/
    function run ($folder="") {
        if (empty($folder)) {
          $projects = scandir ($this->projectFolder);
          foreach ($projects as $project) {
            if (!in_array(basename($project), $this->hideFilesFolders) && $project != ".") {
              echo "<h1>" . $project . '</h1> <li><a href="/?folder=' . htmlentities($project) . '">show only this project</a>';

              $this->compareTwoFolders($this->templateFolder, $this->projectFolder . "/$project");
            }
          }
        } else {
          echo "<h1>" . $folder . "</h1>";
          $this->compareTwoFolders($this->templateFolder, $this->projectFolder . "/$folder");
        }
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
  color: #000;
  font-family: Helvetica;
}

table {
  margin: 25px auto;
}

td {
  background: #fff;
  border: 1px solid #999;
  padding: 10px 15px;
  color: black;
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
<p> <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for ..." title="Type in a name"></p>

<?php
  $fci = new folderCompare();
  $fci->run($_GET['folder']);
?>

<script>
function myFunction() {
  var input, filter, table, tr, td, i,alltables;
    alltables = document.querySelectorAll("table[data-name=mytable]");
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  alltables.forEach(function(table){
      tr = table.getElementsByTagName("tr");
      for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
        if (td) {
          if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
          } else {
            tr[i].style.display = "none";
          }
        }
      }
  });
}
</script>
</body>
</html>
