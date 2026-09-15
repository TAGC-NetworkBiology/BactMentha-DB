<?php
    // ____________________
    // START OF THE SESSION
    if (empty($_SESSION)) {session_start(); }   // if first page (without refresh)
    error_reporting(E_ERROR | E_PARSE);         // to avoid some warning messages

    if(isset($_GET['refresh_all'])) {           // if the reset button is pressed
      session_destroy();                        // empty the session variables
      session_start();                          // start a new session
    }
    // ______________________________________
    // UNSET PREVIOUS PAGES SESSION VARIABLES
    if (isset($_SESSION['data_view_type']) || isset($_SESSION['data_taxon']) || isset($_SESSION['data_selected_view']) || isset($table_name)) {         // from the data page
      unset($_SESSION['data_view_type']);
      unset($_SESSION['data_taxon']);                 // from the data page
      unset($_SESSION['data_selected_view']);
      unset($table_name);
    }
    if (isset($_SESSION['stats_view_type'])) {        // from the stats page
      unset($_SESSION['stats_view_type']);
    }
    if (isset($_SESSION['stats_taxon'])) {            // from the stats page
      unset($_SESSION['stats_taxon']);
    }
    if (isset($_SESSION['stats_selected_view'])) {    // from the stats page
      unset($_SESSION['stats_selected_view']);
    }
    if (isset($_SESSION['search_value'])) {           // from the home page
      unset($_SESSION['search_value']);
    }
    if (isset($_SESSION['filterValues'])) {
      unset($_GET["filterValues"]);
      unset($_SESSION["filterValues"]);
      unset($filterValues);
    }
    if (isset($_SESSION['search_value'])) {
      unset($_SESSION['search_value']);
    }
?>

<!DOCTYPE html>
<html>
<!--=============================================================================================================================================-->
<!--=================================================================== HEAD ====================================================================-->
<!--=============================================================================================================================================-->
  <head>
    <meta charset="utf-8">       
    <title>Download-BactMentha</title> 
    <script type="text/javascript" src="/static/js/bactmenthaDB_library.js"></script>
    <!-- include min JQuery to use dataTable -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- Include dataTable files -->
    <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css"></script> -->
    <!-- Include JS for dataTable export -->
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js"></script>
    <!-- Include the bactmentha CSS file (in the same folder) -->
    <link rel="stylesheet" href="/static/css/bactmenthaDB_stylesheet.css" /> 
    <!-- Include the CSS file for datatable -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
  </head>
<!--=============================================================================================================================================-->
<!--=================================================================== BODY ====================================================================-->
<!--=============================================================================================================================================-->
  <body>
<!--================================================================== HEADER ===================================================================-->
<div class="header_bar">
  <div class="gradient_background">
    <img src="/static/img/banner/test_banner_20240122.svg" class="header_logo image2">
    <!-- <img src="/static/img/banner/mint_leaves.png" class="header_logo image2">
    <img src="/static/img/banner/test_layer-bugs.png" class="header_logo image1"> -->
    <!-- <img src="/static/img/banner/test_layer-bactmentha.png" class="header_logo image3"> -->
  </div>
</div>

<nav class='navbar'>
  <ul>
    <li><a href="/Home">Home</a></li>
    <li><a href="/Browse">Browse</a></li>
    <li><a class='current_page' href="/Download">Download</a></li>
    <li><a href="/Statistics">Statistics</a></li>
    <li><a href="/About">About &#9660</a>
      <ul>
        <li><a class="no_hover">About &#9660</a></li>
        <li><a href="/Documentation">Documentation</a></li>
        <li><a href="/FAQ">F.A.Q.</a></li>
        <li><a href="/Contact">Contact</a></li>
      </ul>
    </li>     
  </ul>
</nav>
<!--=================================================================== MAIN ====================================================================-->
    <main>
       <!-- TITLE -->
        <h2 class='home_news_title1'> Download specific versions of BactMentha Database</h2>
        <div class='home_title_bar'></div><br><br><br>
        <!-- LITTLE TEXT -->
        <div>
          <label class='default_centered_text'>
            Here, you can find the archived versions of the BactMentha database. The first archive in 
            the list corresponds to the current version. You can find more information about the archive 
            file in the <a href="/Documentation" style="color:#429E9D"><u>documentation</u></a> page.
          </label>
        </div><br><br><br>
        <!-- TABLE CONTAINING THE LINKS -->
        <?php

          $archivePath = '/home/bactmentha_archives';
          $archiveDict = [];

          // Iterate over the main folders
          foreach (glob($archivePath . '/bm_archive_*') as $mainFolder) {
              $mainFolderName = basename($mainFolder);
              $date = substr($mainFolderName, strlen('bm_archive_'));

              // Initialize the list of files for this version
              $files = [$date];

              // Initialize arrays for different file types
              $completeArchive = null;
              $dbTablesComplete = null;
              $dbTablesHosts = [];
              $refComplete = null;
              $refWhoCc = null;
              $refAf3 = null;
              $refTables = null;
              $refTablesHosts = [];
              $dumpFile = null;

              // Iterate over the files in the main folder
              foreach (glob($mainFolder . '/*') as $file) {
                $filename = basename($file);
                $lowerFilename = strtolower($filename);

                if (strpos($lowerFilename, 'complete_archive.zip') !== false) {
                    $completeArchive = $filename;

                } elseif (strpos($lowerFilename, 'db_tables_complete.zip') !== false) {
                    $dbTablesComplete = $filename;

                } elseif (preg_match('/db_tables_(\d+)\.zip$/i', $filename, $matches)) {
                    $dbTablesHosts[(int)$matches[1]] = $filename;

                } elseif (strpos($lowerFilename, 'ref_who_and_cc_annot.zip') !== false) {
                    $refWhoCc = $filename;

                } elseif (strpos($lowerFilename, 'ref_af3_predictions.zip') !== false) {
                    $refAf3 = $filename;

                } elseif (strpos($lowerFilename, 'ref_complete.zip') !== false) {
                    $refComplete = $filename;

                } elseif (strpos($lowerFilename, 'ref_tables.zip') !== false) {
                    $refTables = $filename;

                } elseif (preg_match('/ref_tables_(\d+)\.zip$/i', $filename, $matches)) {
                    $refTablesHosts[(int)$matches[1]] = $filename;

                } elseif (substr($lowerFilename, -4) === '.dmp') {
                    $dumpFile = $filename;
                }
              }

              ksort($dbTablesHosts);
              ksort($refTablesHosts);

              // Add the categorized files to the dictionary in the desired order
              $archiveDict[$mainFolderName] = [
                'date' => $date,
                'complete_archive' => $completeArchive,
                'db_tables_complete' => $dbTablesComplete,
                'db_tables_hosts' => $dbTablesHosts,
                'ref_complete' => $refComplete,
                'ref_who_cc' => $refWhoCc,
                'ref_af3' => $refAf3,
                'ref_tables' => $refTables,
                'ref_tables_hosts' => $refTablesHosts,
                'dump' => $dumpFile
              ];
          }

          echo "<table class='archive_table'>";
          echo '<tr>
                  <th><label class="default_centered_text">Date</label></th>
                  <th><label class="default_centered_text">Download</label></th>
                  <th><label class="default_centered_text">Details</label></th>
                </tr>';

          $latest_version = TRUE;

          foreach ($archiveDict as $version => $archive) {
              if ($latest_version == TRUE) {
                  echo '<tr class="main_table_line">
                          <td><label class="default_centered_text">' . $archive['date'] . ' <i>(latest)</i></label></td>
                          <td>';
                  $latest_version = FALSE;
              } else {
                  echo '<tr class="main_table_line">
                          <td><label class="default_centered_text">' . $archive['date'] . '</label></td>
                          <td>';
              }
              echo '<div class="archive_tree">';

              // --------------------------------------------------
              // COMPLETE ARCHIVE
              // --------------------------------------------------
              if ($archive['complete_archive'] !== null) {
                  echo '<div class="archive_item level_0 main_table_line">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['complete_archive'] . '" download="' . $archive['complete_archive'] . '">
                              BactMentha complete archive
                          </a>
                        </div>';
              }
              // --------------------------------------------------
              // DATABASE TABLES
              // --------------------------------------------------
              if ($archive['db_tables_complete'] !== null) {
                  echo '<div class="archive_item level_1 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['db_tables_complete'] . '" download="' . $archive['db_tables_complete'] . '">
                               • Database tables for all host taxa
                          </a>
                        </div>';
              }
              foreach ($archive['db_tables_hosts'] as $taxId => $filename) {
                  echo '<div class="archive_item level_2 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $filename . '" download="' . $filename . '">
                               • BactMentha Database tables for host ' . $taxId . '
                          </a>
                        </div>';
              }
              // --------------------------------------------------
              // REFERENCES
              // --------------------------------------------------
              if ($archive['ref_complete'] !== null) {
                  echo '<div class="archive_item level_1 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['ref_complete'] . '" download="' . $archive['ref_complete'] . '">
                               • BactMentha references complete
                          </a>
                        </div>';
              }
              if ($archive['ref_who_cc'] !== null) {
                  echo '<div class="archive_item level_2 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['ref_who_cc'] . '" download="' . $archive['ref_who_cc'] . '">
                               • References WHO/HSE groups and cellular components
                          </a>
                        </div>';
              }
              if ($archive['ref_af3'] !== null) {
                  echo '<div class="archive_item level_2 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['ref_af3'] . '" download="' . $archive['ref_af3'] . '">
                               • Reference AF3 predictions
                          </a>
                        </div>';
              }
              if ($archive['ref_tables'] !== null) {
                  echo '<div class="archive_item level_2 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['ref_tables'] . '" download="' . $archive['ref_tables'] . '">
                               • Reference tables for all host taxa
                          </a>
                        </div>';
              }
              if ($archive['ref_tables'] !== null) {
                  foreach ($archive['ref_tables_hosts'] as $taxId => $filename) {
                      echo '<div class="archive_item level_3 second_link">
                              <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $filename . '" download="' . $filename . '">
                                   • Reference tables for ' . $taxId . '
                              </a>
                            </div>';
                  }
              }
              // --------------------------------------------------
              // DUMP
              // --------------------------------------------------
              if ($archive['dump'] !== null) {
                  echo '<div class="archive_item level_1 second_link">
                          <a class="table_link" href="/home/bactmentha_archives/' . $version . '/' . $archive['dump'] . '" download="' . $archive['dump'] . '">
                               • backup.dump
                          </a>
                        </div>';
              }
              echo '</div>';
              echo '</td>
                    <td class="details_column">
                        <button class="details_button bm_button">Show</button>
                    </td>
                    </tr>';
          }
          echo '</table>';
          echo '<br><br>';

          ?>

    <!-- Scroll-to-top button -->
    <div class="scroll-to-top">
        <a href="#top">
            &#9650; <!-- Up arrow symbol ▲ -->
        </a>
    </div>


    <script>
      $(document).ready(function() {
          // Hide the scroll-to-top button initially
          $('.scroll-to-top').hide();

          // Show/hide the scroll-to-top button based on the user's scrolling position
          $(window).scroll(function () {
              if ($(this).scrollTop() > 100) { // Adjust the threshold as needed
                  $('.scroll-to-top').fadeIn();
              } else {
                  $('.scroll-to-top').fadeOut();
              }
          });

          // Smooth scroll to the top of the page when the "scroll-to-top" button is clicked
          $('.scroll-to-top').on('click', function (event) {
              event.preventDefault();
              $('html, body').animate({ scrollTop: 0 }, 'slow');
          });

          // Hide all links except the first one initially
          $('.archive_table .second_link').hide();

          // Add click event to the "Details" button
          $('.archive_table .details_button').click(function () {
              // Toggle visibility of links in the same row
              $(this).closest('.main_table_line').find('.second_link').toggle();

              // Change the text of the button based on visibility
              var buttonText = $(this).text() === 'Show' ? 'Hide' : 'Show';
              $(this).text(buttonText);
          });

      });
    </script>

    </main>
<!--=============================================================================================================================================-->
<!--================================================================== FOOTER ===================================================================-->
<!--=============================================================================================================================================-->
    <footer>
      <div class='footer_blank_div'></div>
      <div class='footer_bar'> </div>
      <div class='footer_logos'>
        <img src="/static/img/institutions/TAGC.png" class="footer_logo">
        <img src="/static/img/institutions/inserm.png" class="footer_logo">
        <img src="/static/img/institutions/AMU.png" class="footer_logo">
        <img src="/static/img/institutions/tor_vergata_logo.png" class="footer_logo">
        <img src="/static/img/institutions/DIRCOM-Logo-AMIDEX.png" class="footer_logo">
        <img src="/static/img/institutions/anr.png" class="footer_logo">
        <img src="/static/img/institutions/MESR.png" class="footer_logo">
        <img src="/static/img/institutions/france_2023.png" class="footer_logo">
        <img src="/static/img/institutions/jpi.png" class="footer_logo">
        <img src="/static/img/institutions/IMEx_logo_webmedium.png" class="footer_logo">
      </div>
      <div class='footer_cpright'>
        <p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><span property="dct:title">BactMentha</span> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt=""><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt=""></a></p> 
      </div>
    </footer>
  </body>
</html>