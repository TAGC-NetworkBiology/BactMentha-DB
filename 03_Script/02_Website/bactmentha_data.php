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
    if (isset($_SESSION['search_value'])) {           // from the home page
      unset($_SESSION['search_value']);
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
    if (isset($_SESSION['filterValues'])) {
      unset($_GET["filterValues"]);
      unset($_SESSION["filterValues"]);
      unset($filterValues);
    }
    if (isset($_SESSION['search_value'])) {
      unset($_SESSION['search_value']);
    }

    // ____________________________________________
    // CALLS THE REQUIRED SCRIPTS FOR THE FUNCTIONS
    include '../static/php/bactmenthaDB_library.php' ;
    include 'static/php/bactmenthaDB_library.php' ;
?>
<!DOCTYPE html>
<html>
<!--=============================================================================================================================================-->
<!--=================================================================== HEAD ====================================================================-->
<!--=============================================================================================================================================-->
  <head>
    <meta charset="utf-8">                                     <!-- Encoding -->
    <title>Browse-BactMentha</title>                               <!-- Title that will appear in the window tab -->
    <script type="text/javascript" src="/static/js/bactmenthaDB_library.js"></script>
    <!-- include min JQuery to use datatable -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- Include datatable file -->
    <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
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
    <li><a class='current_page' href="/Browse?refresh_all=Reset">Browse</a></li>
    <li><a href="/Download">Download</a></li>
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
<!---------------------------------------------------------------- VIEW SELECTION ----------------------------------------------------------------->
    <!-- title -->
    <h2 class='home_news_title1'> Display specific BactMentha datasets </h2>
    <div class='home_title_bar'></div><br><br>
    <!-- presentation text -->
    
    <!-- buttons for view/table seletcion -->
    <form name="view_choice" id="view_choice_form">
        <!-- First subdiv: filter by host taxa -->
        <div class='browse_div_titles'>BactMentha dataset filtered on host taxa:</div><br><br>
        <div class='browse_text_div'>
            <label class='default_text'>
                Click on a taxon button to display the BactMentha database pathogen-host protein-protein interactions for
                the corresponding host taxon.<br>
                <i>/!\ Loading the Human and the All Data tables can take a few seconds. Thank you for your patience.</i>
            </label>
        </div><br>
        <div class='home_buttons_space'>
            <!-- All -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=all' title='All taxa'>
                    All Data
                </a>
            </div>
            <!-- Human -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens)'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'><br>
                    Human
                </a>
            </div>
            <!-- Mouse -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=mus_musculus' title='Mouse (Mus musculus)'>
                    <img src='/static/img/buttons/mouse.png' class='home_button_png'><br>
                    Mouse
                </a>
            </div>
            <!-- Rat -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=rattus_norvegicus' title='Rat (Rattus norvegicus)'>
                    <img src='/static/img/buttons/rat.png' class='home_button_png'><br>
                    Rat
                </a>
            </div>
        </div><br><br><br>

        <!-- Second subdiv: filter by pathogen categories -->
        <div class='browse_div_titles'>BactMentha dataset filtered on pathogen categories:</div><br><br>
        <div class='browse_text_div'>
            <label class='default_text'>
                The <a class='table_link' href='https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed' 
                target='_blank'>World Health Organization (WHO) Priority Pathogens</a> are infectious microorganisms considered a significant threat 
                to global public health, often due to their potential for causing severe outbreaks or limited treatment options. 
                According to the European Union classification system of biological agents, hazard groups (from 1 to 4) categorize, based 
                on their risk, those which may affect the human health (<a class='table_link' href='https://www.hse.gov.uk/pubns/misc208.pdf' 
                target='_blank'>https://www.hse.gov.uk/pubns/misc208.pdf</a>).
                Click on the following buttons to filter the BactMentha Dataset and display only the interactions involving
                pathogens in either WHO priority list or in an Hazard group. (Filter on Hazard groups 1 and 4 have not been added
                as no such pathogens are found in BactMentha).
            </label>
        </div><br>
        <div class='home_buttons_space'>
            <!-- Who priority OR hazard group -->
            <div class='data_button pathogen_button'>
                <a href='/Browse?data_view_type=who_priority&data_taxon=bact_who_hazard' title='Who priority or hazard groups pathogens'>
                    Who priority<br>or<br>Hazard groups
                </a>
            </div>
            <!-- Who priority -->
            <div class='data_button pathogen_button'>
                <a href='/Browse?data_view_type=who_priority&data_taxon=bact_who' title='Who priority pathogens'>
                    Who priority
                </a>
            </div>
            <!-- Hazard group 2 -->
            <div class='data_button pathogen_button'>
                <a href='/Browse?data_view_type=who_priority&data_taxon=bact_hazard2' title='Hazard group 2 pathogens'>
                    Hazard group 2
                </a>
            </div>
            <!-- Hazard group 3 -->
            <div class='data_button pathogen_button'>
                <a href='/Browse?data_view_type=who_priority&data_taxon=bact_hazard3' title='Hazard group 3 pathogens'>
                    Hazard group 3
                </a>
            </div>
        </div><br><br><br>

        <div class='browse_div_titles'>BactMentha dataset filtered on annotated protein interactions:</div><br><br>
        <div class='browse_text_div'>
            <label class='default_text'>
                Available data on experimentally detected binding regions have been gathered from IMEx databases, thus providing 
                the details on the sequence regions of either bacteria or host proteins (or both) involved in the interactions. 
                The <a class='table_link' href='https://www.ebi.ac.uk/ols4/' target="_blank">Ontology Search (OS)</a> definition 
                describing these binding regions can be found <a class='table_link' 
                href='https://www.ebi.ac.uk/ols4/ontologies/mi/classes/http%253A%252F%252Fpurl.obolibrary.org%252Fobo%252FMI_0117?lang=en' 
                target="_blank">here</a>. Moreover, the <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
                target="_blank">mimicINT</a> workflow has been used to predict the interaction interfaces 
                between Human proteins and bacterial proteins based on known domain-domain or domain-motif interaction templates. 
                We also provide AlphaFold-predicted complex structure for a subset of interactions between annotated bacterial and human proteins.  
                Click on the following buttons to display only the Human-Bacteria protein-protein interactions for which there 
                are known experimental binding regions (BR) on at least one interacting protein or mimicINT predicted interaction 
                interfaces (MI) of interaction (predicted regions are for both proteins) or AlphaFold-predicted complex structure (AF).
            </label>
        </div><br>
        <div class='home_buttons_space'>
            <!-- Human Binding Regions -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=br_interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens) interactions annotated with binding regions'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'><br>Binding regions
                </a>
            </div>
            <!-- Human Mimicint annotations -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=mi_interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens) interactions annotated with mimicint interfaces'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'><br>mimicINT interfaces
                </a>
            </div>
            <!-- Human AF3 annotations -->
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=af_interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens) interactions annotated with AlphaFold complexes'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'><br>AlphaFold complexes
                </a>
            </div>
          <!--  Human WHO priority
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=who_interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens) WHO priority concerned interactions'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'> &nbsp WHO priority
                </a>
            </div>
            Human Hazard group 3 
            <div class='data_button host_button'>
                <a href='/Browse?data_view_type=hg3_interaction_data&data_taxon=homo_sapiens' title='Human (Homo sapiens) interactions with Hazard group 3 pathogens'>
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'> &nbsp HG3
                </a>
            </div> -->
        </div><br>
      </form>

<!------------------------------------------------------------ VIEW SELECTION PHP PART ------------------------------------------------------------>
        <?php
        // save the values into the session variables (values are passed directly in the link when
        // the user clicks on one of the buttons)

        // for the view type
        if (isset ($_GET["data_view_type"])){
            $_SESSION["data_view_type"] = $_GET["data_view_type"];
        }
        // for the taxon
        if (isset ($_GET["data_taxon"])){
            $_SESSION["data_taxon"] = $_GET["data_taxon"];
        }
        // Save the selected table with both view type and taxon
        if (!empty ($_GET["data_view_type"]) and !empty($_GET['data_taxon'])){
            // // save the values into the session variables
            $_GET['data_selected_view'] = findViewWithTypeAndTaxon($_GET["data_view_type"], $_GET["data_taxon"]);
        }
        if (isset($_GET['data_selected_view'])) {
            $_SESSION['data_selected_view'] = $_GET['data_selected_view'] ;
        }

        if (isset($_SESSION['data_selected_view'])) {
            $addExportAllBtn = getAddExportAllBtn($_SESSION['data_selected_view']);
            if ($_SESSION["data_view_type"] == "who_priority") {
                echo "<script>tableID = '#who_prio_table' ;</script>";
            } else {
                echo "<script>tableID = '#query_table' ;</script>";
            }
            echo "<script>addExportAllBtn = ".$addExportAllBtn.";</script>";
        }

        if (isset($_GET["unsetBmIdsToExport"])) {
            unset($_SESSION["BmIdsToExport_browse"]);
        }
        elseif(isset($_SESSION["BmIdsToExport_browse"])) {
          $files = downloadSearchCsvFiles($_SESSION["BmIdsToExport_browse"]);
          echo '<script>downloadFiles(' . $files . ');</script>';
        };
  
        if (isset($_POST["BmIdsToExport"])) {
            $_SESSION["BmIdsToExport_browse"] = $_POST["BmIdsToExport"];
        }

      ?>
<!----------------------------------------------------------------- RESET BUTTON ------------------------------------------------------------------>
      <?php 
          // button to reset all session variables (selected view, columns...) -> corresponding code if isset at the beginning of the page
        //   echo "<form> <input type='submit' name='refresh_all' value='Reset' class='bm_button'> </form><br>";
      ?>
<!---------------------------------------------------------------- VIEW PRINTING ------------------------------------------------------------------>
      <?php
        //___________________________________________________________________________________________________________
        // CONNECTION TO THE DATABASE

        $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());

        //___________________________________________________________________________________________________________
        // CHECK IF A TABLE HAS BEEN SELECTED (IF TRUE: DISPLAY THE TITLE)

        $table_name = isViewSelected($_SESSION["data_selected_view"]);            // view name or empty string saved in session parameters)
        if ($table_name != "") {                                                  // if a table is selected
            if ($_SESSION["data_view_type"] == "who_interaction_data") {
                $view_label_to_display = databaseToOfficialViewName($table_name) . " concerning WHO priority pathogens";
            } else if ($_SESSION["data_view_type"] == "hg3_interaction_data") {
                $view_label_to_display = databaseToOfficialViewName($table_name) . " concerning pathogens in hazard group 3";
            } else if ($_SESSION["data_view_type"] == "af_interaction_data") {
              $view_label_to_display = databaseToOfficialViewName($table_name) . " with AlphaFold3 complexes";
            } else if ($_SESSION["data_view_type"] == "mi_interaction_data") {
              $view_label_to_display = databaseToOfficialViewName($table_name) . " with predicted mimicint interfaces";
            } else if ($_SESSION["data_view_type"] == "br_interaction_data") {
              $view_label_to_display = databaseToOfficialViewName($table_name) . " with experimentally annotated binding regions";
            } else {
              $view_label_to_display = databaseToOfficialViewName($table_name) ;
            }
               // find the official name to display (more readable, no underscores)
            echo "<script>$('#view_choice_form').hide();</script>";
            echo "<br><label class='home_news_title2'>".$view_label_to_display."</label>" ;  // and display it.
        }

        //___________________________________________________________________________________________________________
        // TAKES THE COLUMNS NAMES FROM THE TABLE (if a table is selected)

        if (!empty($table_name) && $_SESSION["data_view_type"] != "who_priority") {
            if ($_SESSION["data_view_type"] == "who_interaction_data") {
                $psql_query = browsePageCreationQuery($table_name) . " WHERE IF.taxon_interactor_ida IN (SELECT taxon_id FROM view_who_priority)";
            } else if ($_SESSION["data_view_type"] == "hg3_interaction_data") {
                $psql_query = browsePageCreationQuery($table_name) . " WHERE IF.taxon_interactor_ida IN (SELECT taxon_id FROM view_hazard_group3)";
            } else if ($_SESSION["data_view_type"] == "af_interaction_data") {
                $psql_query = browsePageCreationQuery($table_name) . " WHERE IF.annotations LIKE '____1'";
            } else if ($_SESSION["data_view_type"] == "mi_interaction_data") {
                $psql_query = browsePageCreationQuery($table_name) . " WHERE IF.annotations LIKE '___1_'";
            } else if ($_SESSION["data_view_type"] == "br_interaction_data") {
                $psql_query = browsePageCreationQuery($table_name) . " WHERE IF.annotations LIKE '__1__'";
            } else {
                $psql_query = browsePageCreationQuery($table_name);
            }

            $find_col_query = $psql_query.' LIMIT 0;';              // Taking the table header without the data
            $find_col_result = pg_query($find_col_query) or die('Error in the query execution : ' . pg_last_error());
            $col_nb = pg_num_fields($find_col_result);              // take the number of columns in the table
            $col_list = array();                                    // empty list that will contain the column names

            for ($i = 0; $i < $col_nb; $i++) {                      // for each columns until the last one
                $colname = pg_field_name($find_col_result, $i);     // take the column name at the position
                $col_list[] = $colname;                             // add the name of the current column to the list
            }


            //___________________________________________________________________________________________________________
            // DATATABLE TOGGLE COLUMN DIV  (-> always in the 'if a table is selected')

            $toggleColumnDiv = createToggleColDiv($col_list, $col_nb, 12); // 12 is the index of the bm_id from the resulting table of psql_query
            echo $toggleColumnDiv;

            //___________________________________________________________________________________________________________
            // DISPLAY THE TABLE WITH THE SELECTED COLUMN ONLY (-> always in the 'if a table is selected')
    
            // execution of the psql query (set previously right after the if != empty table_name statement)
            $query_result = pg_query($psql_query) or die('Error in the query execution : ' . pg_last_error());
            
            $AllBmInteractionIds = [];
            // HEADER                                                                   // Displaying the resulting table in HTML
            echo("<table id='query_table' class='display stripe table_php'>\n<thead><tr>"); // Creation of the table and its header ()
            
            $col_number = pg_num_fields($query_result);                  // i = number of columns
            $dispColList = [];                                           // list of columns to display

            for ($i = 0; $i < $col_number; $i++) {                       // iterate through the col names
                $col_name = pg_field_name($query_result, $i);            // takes the new col name
                $disp_colname = MapColnameDispname($col_name);           // replace underscores by spaces in headers names
                // display the col name in a new cell on same line and a select filter
                if ($col_name != 'annotations') {                        
                    echo "\n<th col-id='${col_name}' col-index='".($i+1).
                    "' class='exportable'>${disp_colname}</th>" ;        // avoid exporting annotations column
                }
                else {                                                   // display the col name in a new cell on same line and a select filter
                    echo "\n<th col-id='${col_name}' col-index='".($i+1)."'>${disp_colname}</th>" ; 
                }
                $dispColList[] = $col_name ;                             // add the column to display to be used as key
            }
          
            echo("</tr></thead><tbody>");                                                                                 
            // DATA
            $row_index = 0 ;                                             // to put a specific index in rows

            while($row_elem = pg_fetch_row($query_result)) {             // for all the rows in the resulting table
                echo("\n<tr row-index=".$row_index.">");                 // creates a new line in the html table
                $count_cells = count($row_elem);                         // number of elements in the row
                for ($j = 0; $j < $count_cells; $j++) {                  // for all the elements in the line
                    $current_cell = current($row_elem);                  // current line cell element
                    if ($j == 2 or $j == 5){                             // for the identifiers (interactor_id links to uniprot)
                        echo("<td><a class='table_link' href='https://www.uniprot.org/uniprotkb/".$current_cell."/entry' target='_blank'>".$current_cell."</a></td>");
                    }
                    else if ($j == 8) {
                        echo("<td><a class='table_link' href='https://pubmed.ncbi.nlm.nih.gov/".$current_cell."/' target='_blank'>".$current_cell."</a></td>");
                    }
                    else{
                        if ($j == 12) { // store all the mnt_interaction_id values in a list
                            $AllBmInteractionIds[] = $current_cell;
                        }
                        echo("<td>".$current_cell."</td>");               // display the current element in a new cell
                    }
                    next($row_elem);                                      // taking the next element of the row
                }
                $row_index++ ;                                            // next row index                                         
                echo("</tr>");
            }

            echo("</tbody></table>");                                     // Closing the line, the body and the table
            $close=pg_close($dbconn);                                     // Closing postgresql connexion
            echo("<br><br>");                                             // </div> (end of the table display div)

            // save the viewtype and the bmId index into JS variables to be used in JS functions
            echo("<script>var ViewTypeAnnotIndex = 13</script>");
            echo("<script>var bmIdIndex = 12</script>");
            echo("<script> var AllBmInteractionIds = ". json_encode($AllBmInteractionIds) ."; </script>");

        }                                                                 // end of the 'if there is a selected table' not in 'who_priority'
        else if (!empty($table_name) && $_SESSION["data_view_type"] == "who_priority") {
            $psql_query = "SELECT * FROM ".$table_name;
            $find_col_query = $psql_query.' LIMIT 0;';
            $find_col_result = pg_query($find_col_query) or die('Error in the query execution : ' . pg_last_error());
            $col_nb = pg_num_fields($find_col_result);
            $col_list = array();

            for ($i = 0; $i < $col_nb; $i++) {
                $colname = pg_field_name($find_col_result, $i);
                $col_list[] = $colname;
            }

            //___________________________________________________________________________________________________________
            // DATATABLE TOGGLE COLUMN DIV  (-> always in the 'if a table is selected')

            $toggleColumnDiv = createToggleColDiv($col_list, $col_nb, 10); // 10 is the index of the bm_id from the resulting table of psql_query
            echo $toggleColumnDiv;

            //___________________________________________________________________________________________________________
            // DISPLAY THE TABLE WITH THE SELECTED COLUMN ONLY (-> always in the 'if a table is selected')
    
            $query_result = pg_query($psql_query) or die('Error in the query execution : ' . pg_last_error());

            // HEADER
            echo("<table id='who_prio_table' class='display stripe table_php'>\n<thead><tr>");
            
            $col_number = pg_num_fields($query_result);
            $dispColList = [];

            for ($i = 0; $i < $col_number; $i++) {
                $col_name = pg_field_name($query_result, $i);
                $disp_colname = MapColnameDispname($col_name);
                echo "\n<th col-id='${col_name}' col-index='".($i+1).
                    "' class='exportable'>${disp_colname}</th>" ;        
                $dispColList[] = $col_name ;
            }
            echo("</tr></thead><tbody>");   

            // DATA
            $row_index = 0 ;
            while($row_elem = pg_fetch_row($query_result)) {
                echo("\n<tr row-index=".$row_index.">");
                $count_cells = count($row_elem);
                for ($j = 0; $j < $count_cells; $j++) {
                    $current_cell = current($row_elem);
                    if ($j == 0) {
                        echo("<td><a class='table_link' href='/Home?search_value=".$current_cell."&searchbtn=Search' target='_blank'>".$current_cell."</a></td>");
                    }
                    else {
                        echo("<td>".$current_cell."</td>");
                    }
                    next($row_elem);
                }
                $row_index++ ;                                        
                echo("</tr>");
            }

            echo("</tbody></table>");
            $close=pg_close($dbconn);
            echo("<br><br>");
          }
      ?>

      <!-- Scroll-to-top button -->
      <div class="scroll-to-top">
          <a href="#top">
              &#9650; <!-- Up arrow symbol ▲ -->
          </a>
      </div>

<!------------------------------------------------------------------- JS SCRIPT ------------------------------------------------------------------->
<script>

    // Adding the filters in the header for each column
    $(document).ready(function () {

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

        if ($('#query_table').length) {
            // Setup - add a text input to each footer cell
            $('#query_table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#query_table thead');
    
        
            var table = $('#query_table:not(.child_table)').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                initComplete: function () {
                    var api = this.api();
                    // For each column
                    api
                        .columns()
                        .eq(0)
                        .each(function (colIdx) {
                            // Set the header cell to contain the input element
                            var cell = $('.filters th').eq(
                                $(api.column(colIdx).header()).index()
                            );
                            var title = $(cell).text();
                            if (title != 'Annotations'){ // for each title except the annotation one
                                // check if the column with index 'colIdx' has a header.
                                if ($(api.column(colIdx).header()).index() >= 0) {
                                    // If the column has a header, adds an input element with a 'text' type and sets a placeholder to 'title'.
                                    $(cell).html('<input type="text" placeholder="' + title + '"/>');
                                }
                                // Attach event handlers to the input element for filtering purposes.
                                // When a change event is triggered on the input element, the following function is executed.
                                $('input', $('.filters th').eq($(api.column(colIdx).header()).index()))
                                    .off('keyup change')
                                    .on('change', function (e) {

                                        // Set the 'title' attribute of the input element to its current value (the filter value).
                                        $(this).attr('title', $(this).val());

                                        // Define a regular expression pattern for filtering (initially set to '{search}').
                                        var regexr = '({search})';

                                        // Get the current cursor position within the input element.
                                        var cursorPosition = this.selectionStart;

                                        // Search the DataTables column with index 'colIdx' using the filter value ('this.value').
                                        // It applies the regular expression pattern to perform the search.
                                        // The search uses '(((' + this.value + ')))' as the pattern to match the exact value.
                                        // If 'this.value' is empty, it searches for an empty value in the column.
                                        api.column(colIdx)
                                            .search(
                                                this.value != ''
                                                    ? regexr.replace('{search}', '(((' + this.value + ')))')
                                                    : '',
                                                this.value != '',
                                                this.value == ''
                                            )
                                            .draw();
                                    }) // end of the 'change' function
                                    .on('keyup', function (e) {
                                        e.stopPropagation();

                                        // Trigger the 'change' event when a keyup event is detected, so the filtering happens on each keypress.
                                        $(this).trigger('change');

                                        // Set the cursor position to the previous value after triggering the change event.
                                        $(this).focus()[0].setSelectionRange(cursorPosition, cursorPosition);
                                    }); // end of the keyup event
                            }
                            else { // only for the annotation column
                                // Code to display checkboxes for different annotations in the column header.
                                var html_Annotcb_text = `<label class='cb_annot_labels' title='Bacterial Protein Annotations'>&nbspPA&nbsp</label>
                                <label class='cb_annot_labels' title='Cellular Components'>&nbspCC&nbsp</label>
                                    <label class='cb_annot_labels' title='Binding Regions'>&nbspBR&nbsp</label>
                                    <label class='cb_annot_labels' title='Mimicint Interfaces'>&nbspMI&nbsp</label>
                                    <label class='cb_annot_labels' title='AlphaFold Complexes'>&nbspAF&nbsp</label><div class="annotations_cb_disp">
                                    <input class='annotCb Pa_button' type="checkbox" id="Cb_ProtAnnot" name="cb_annotations" title="Bacterial Protein Annotations">
                                    <input class='annotCb Cc_button' type="checkbox" id="Cb_CellComp" name="cb_annotations" title="Cellular Components">
                                    <input class='annotCb Br_button' type="checkbox" id="Cb_Features" name="cb_annotations" title="Binding Regions">
                                    <input class='annotCb Mi_button' type="checkbox" id="Cb_Mimicint" name="cb_annotations" title="Mimicint Interfaces">
                                    <input class='annotCb Af_button' type="checkbox" id="Cb_AlphaFold" name="cb_annotations" title="AlphaFold Complexes"></div>`;
                                $(cell).html(html_Annotcb_text);

                                $('.annotCb').on('change', function (e) {
                                    // Retrieve the states of all checkboxes (checked or not).
                                    var isChecked_pa = $("#Cb_ProtAnnot").is(":checked");
                                    var isChecked_cc = $("#Cb_CellComp").is(":checked");
                                    var isChecked_br = $("#Cb_Features").is(":checked");
                                    var isChecked_mi = $("#Cb_Mimicint").is(":checked");
                                    var isChecked_af = $("#Cb_AlphaFold").is(":checked");

                                    // Check if all checkboxes are unchecked
                                    var allUnchecked = !isChecked_pa && !isChecked_cc && !isChecked_br && !isChecked_mi && !isChecked_af;

                                    // Remove the previous custom filter if present
                                    $.fn.dataTable.ext.search.pop();

                                    // If any of the checkboxes is checked, apply the custom filter function
                                    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                                        var row = $(api.row(dataIndex).node());

                                        var filters = {
                                            MI: isChecked_mi,
                                            BR: isChecked_br,
                                            PA: isChecked_pa,
                                            CC: isChecked_cc,
                                            AF: isChecked_af
                                        };

                                        return Object.keys(filters).every(function(type) {
                                            return !filters[type] || row.find('a.moreInfoBtnLink' + type).length > 0;
                                        });
                                    });
                                    // Redraw the table to apply the custom filter (or to remove the filter if all checkboxes are unchecked).
                                    api.draw();
                                });

                            } // end of the else for search with 'Details' column
                            
                        }) // end of the '.each(colIdx) of the api

                }, // end of the initComplete function
                "columnDefs": [
                    {
                        "className":  "dt-head-center",
                        "targets": "_all"
                    },
                    // hide the needed column
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        // "data":           null,
                        "render": function (data, type, full, meta) {
                        var htmlText = displayAnnotationButtons(data);
                        return htmlText;
                    },
                        "targets": ViewTypeAnnotIndex // take the index of annotation column depending on selected view type
                    },
                    {
                        "targets": [2,5],
                        "width": "150px"
                    }
                ],
                "order": [],
                dom: 'Blfrtip',
                buttons: [
                    {text: 'Show/Hide Columns',
                        className: 'bm_button',
                        attr: {id: 'ToggleColButton'},
                        action: function(){$('#dispColChoiceDiv').toggle();
                        }},
                        // Add button conditionally based on the value of addExportAllData
                        addExportAllBtn 
                            ? {
                                text: 'CSV + annotations to CSV',
                                className: 'bm_button',
                                attr: {id: 'ExportAllBtn'},
                                action: function() {
                                    downloadAllInteractionData(tableID, AllBmInteractionIds);
                                }
                            } 
                            : null,
                    // {extend: 'copy',
                    //     className: "bm_button",
                    //     exportOptions: {columns: ':visible.exportable'}},
                    {extend: 'csv',
                        className: "bm_button",
                        exportOptions: {columns: ':visible.exportable'}},
                    {extend: 'excel',
                        className: "bm_button",
                        exportOptions: {columns: ':visible.exportable'}},
                ], // end of the buttons definition
            }); // end of the datatable initialisation
            
            // Correspondence between button classes, child classes and formatting functions
            const detailTypes = {
                moreInfoBtnLinkPA: { childClass: 'PAchild', formatter: format_PA },
                moreInfoBtnLinkCC: { childClass: 'CCchild', formatter: format_CC },
                moreInfoBtnLinkBR: { childClass: 'BRchild', formatter: format_BR },
                moreInfoBtnLinkMI: { childClass: 'MIchild', formatter: format_MI },
                moreInfoBtnLinkAF: { childClass: 'AFchild', formatter: format_AF }
            };
            // Add event listener for opening and closing row details
            $('#query_table tbody').on('click', '.moreInfoBtnLinkPA, .moreInfoBtnLinkCC, .moreInfoBtnLinkBR, .moreInfoBtnLinkMI, .moreInfoBtnLinkAF', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                // Determine which button was clicked
                var buttonClass = Object.keys(detailTypes).find(cls => $(this).hasClass(cls));
                var detail = detailTypes[buttonClass];
                // If this child type is already open, close it
                if (row.child.isShown() && tr.hasClass(detail.childClass)) {
                    row.child.hide();
                    tr.removeClass('shown PAchild CCchild BRchild MIchild AFchild');
                    return;
                }
                // Close another child type if one is already open
                if (row.child.isShown()) {
                    row.child.hide();
                }
                tr.removeClass('PAchild CCchild BRchild MIchild AFchild');
                // Open requested child
                row.child(detail.formatter(row.data(), 12)).show(); // 12 = mnt_interaction_id index
                tr.addClass('shown');
                tr.addClass(detail.childClass);
            });

            // // hide the mnt_interaction_id column by default
            let columnIdx = 12;                                                // takes the index of the column
            let column = table.column(columnIdx);                              // and retrieve the corresponding column in the table
            column.visible(false);                                             // hide the column
            } 
        
        else {
            $('#who_prio_table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#who_prio_table thead');
    
            var table = $('#who_prio_table').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                initComplete: function () {
                    var api = this.api();
        
                    // For each column
                    api
                        .columns()
                        .eq(0)
                        .each(function (colIdx) {
                            // Set the header cell to contain the input element
                            var cell = $('.filters th').eq(
                                $(api.column(colIdx).header()).index()
                            );
                            var title = $(cell).text();
                            if ($(api.column(colIdx).header()).index() >= 0) {
                                $(cell).html('<input type="text" placeholder="' + title + '"/>');
                            }

                            // On every keypress in this input
                            $(
                                'input',
                                $('.filters th').eq($(api.column(colIdx).header()).index())
                            )
                                .off('keyup change')
                                .on('change', function (e) {
                                    // Get the search value
                                    $(this).attr('title', $(this).val());
                                    var regexr = '({search})'; //$(this).parents('th').find('select').val();
        
                                    var cursorPosition = this.selectionStart;
                                    // Search the column for that value
                                    api
                                        .column(colIdx)
                                        .search(
                                            this.value != ''
                                                ? regexr.replace('{search}', '(((' + this.value + ')))')
                                                : '',
                                            this.value != '',
                                            this.value == ''
                                        )
                                        .draw();
                                }) // end of the 'change' function
                                .on('keyup', function (e) {
                                    e.stopPropagation();
        
                                    $(this).trigger('change');
                                    $(this)
                                        .focus()[0]
                                        .setSelectionRange(cursorPosition, cursorPosition);
                                }); // end of the keyup
                                
                        }); // end of the 'for each column index

                }, // end of the initComplete function
                "order": [],
                dom: 'Blfrtip',
                buttons: [
                    {text: 'Show/Hide Columns',
                    className: 'bm_button',
                    attr: {id: 'ToggleColButton'},
                    action: function(){$('#dispColChoiceDiv').toggle();
                    }},
                    {extend: 'copy',
                    className: "bm_button",
                    exportOptions: {columns: ':visible',
                                    rows: ':visible'}},
                    {extend: 'csv',
                    className: "bm_button",
                    exportOptions: {columns: ':visible',
                                    rows: ':visible'}},
                    {extend: 'excel',
                    className: "bm_button",
                    exportOptions: {columns: ':visible',
                                    rows: ':visible'}}
                ]

            }); // end of the datatable initialisation
            table.buttons().container().appendTo( $('.dataTables_filter'));
            }
        // Make the toggle column div become active
        document.querySelectorAll('a.toggle-vis').forEach((el) => {
            el.addEventListener('click', function (e) {                    // each time the user clicks
                e.preventDefault();

                var a = $(this).closest('a');
                let columnIdx = e.target.getAttribute('data-column');      // takes the index of the column
                let column = table.column(columnIdx);                      // and retrieve the corresponding column in the table
        
                // Toggle the visibility
                column.visible(!column.visible());                         // change the column visibility
                if (a.hasClass('clicked_button')) {                        // change the class to change the text color
                    a.removeClass('clicked_button');
                    a.addClass('unclicked_button');
                }
                else if (a.hasClass('unclicked_button')) {
                    a.removeClass('unclicked_button');
                    a.addClass('clicked_button');
                }
                
            }); // end of the eventListener for 'click'

        }); // end of the part that changes column visibility

    }); // end of the document ready function

</script>


<!------------------------------------------------------------------ SAVE BUTTON ------------------------------------------------------------------>
            <!-- <div> <input type="submit" id="button_save_table" name="button_save_table" value="Save selected table"> </div> -->
            <br>

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
