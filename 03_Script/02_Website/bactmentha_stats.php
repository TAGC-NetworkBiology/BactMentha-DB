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
    if (isset($_SESSION['data_view_type'])) {         // from the data page
      unset($_SESSION['data_view_type']);
    }
    if (isset($_SESSION['data_taxon'])) {             // from the data page
      unset($_SESSION['data_taxon']);
    }
    if (isset($_SESSION['data_selected_view'])) {     // from the data page
      unset($_SESSION['data_selected_view']);
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
    <title>Statistics-BactMentha</title>                               <!-- Title that will appear in the window tab -->
    <script src="https://d3js.org/d3.v7.min.js"></script>
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
    <!-- Include JS for chart graphs -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
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
    <li><a href="/Download">Download</a></li>
    <li><a class='current_page' href="/Statistics?refresh_all=Reset">Statistics</a></li>
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
        <!-- LITTLE TEXT -->
        <h2 class='home_news_title1'>BactMentha Database Statistics</h2>
        <div class='home_title_bar'></div><br><br>

<!------------------------------------------------------------ VIEW SELECTION PHP PART ------------------------------------------------------------>
      <?php
      // save the values into the session variables
      // for the view type
      if (isset ($_GET["stats_view_type"])){
        $_SESSION["stats_view_type"] = $_GET["stats_view_type"];
      }
      // for the taxon
      if (isset ($_GET["stats_taxon"])){
        $_SESSION["stats_taxon"] = $_GET["stats_taxon"];
      }
      // Save the selected table with both view type and taxon
      if (!empty ($_GET["stats_view_type"]) and !empty($_GET['stats_taxon'])){
        // // save the values into the session variables
        $_GET['stats_selected_view'] = findViewWithTypeAndTaxon($_GET["stats_view_type"], $_GET["stats_taxon"]);
      }
      if (isset($_GET['stats_selected_view'])) {
        $_SESSION['stats_selected_view'] = $_GET['stats_selected_view'] ;
        // $_SESSION['display_col'] = array() ;    // clear the display_col list of the previous table.
        echo "<script> clearStorage() </script>" ;
      }
      ?>

<!---------------------------------------------------------------- VIEW PRINTING ------------------------------------------------------------------>
      <?php
        //___________________________________________________________________________________________________________
        // CONNECTION TO THE DATABASE
        $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());

        //___________________________________________________________________________________________________________
        // CHECK IF A TABLE HAS BEEN SELECTED

        $table_name = isViewSelected($_SESSION["stats_selected_view"]);         // check if a table has been selected in the form
        if ($table_name != "") {                                                // if a table_name is stored either in the get or session table
          $view_label_to_display = databaseToOfficialViewName($table_name) ;    // find the official name to display (more readable, no underscores)
          echo "<h2 class='home_news_title2'>".$view_label_to_display."</h2>" ; // and display it.
        }

        //___________________________________________________________________________________________________________
        // TAKES THE COLUMNS NAMES FROM THE TABLE (if a table is selected)

        if (!empty($table_name)) {                                              // Only if a table has been selected (in get or session variable)
          $find_col_query = 'SELECT * FROM '.$table_name.' LIMIT 0;';                 // Taking the table without the data
          $find_col_result = pg_query($find_col_query) or die('Error in the query execution : ' . pg_last_error());
          $col_nb = pg_num_fields($find_col_result);                                  // take the number of columns in the table
          $col_list = array();                                                        // empty list that will contain the column names

          for ($i = 0; $i < $col_nb; $i++) {                                          // for each columns until the last one
            $colname = pg_field_name($find_col_result, $i);                           // take the column name at the position
            $col_list[] = $colname;                                                   // add the name of the current column to the list
          }

          //___________________________________________________________________________________________________________
          // DATATABLE TOGGLE COLUMN DIV  (-> always in the 'if a table is selected')

          $toggleColumnDiv = createToggleColDiv($col_list, $col_nb, None);
          echo $toggleColumnDiv;

          $selectColQuery = "*";

          //___________________________________________________________________________________________________________
          // DISPLAY THE TABLE WITH THE SELECTED COLUMN ONLY (-> always in the 'if a table is selected')

          // echo('<br><div class="table_display_div">');
  
            // definition of the sql query
            $sql_query = "SELECT $selectColQuery FROM ".$table_name;                  // Query to execute : select all the data from the table
            // execution of the sql query
            $query_result = pg_query($sql_query) or die('Error in the query execution : ' . pg_last_error());

            // HEADER                                                                 // Displaying the resulting table in HTML
            echo("<table id='stats_table' class='display stripe table_php'>\n<thead><tr>"); // Creation of the table and its header ()
            
            $col_number = pg_num_fields($query_result);                               // i = number of columns
            $dispColList = [];                                                        // list of columns to display
            for ($i = 0; $i < $col_number; $i++) {                                    // iterate through the col names
              $col_name = pg_field_name($query_result, $i);                           // takes the new col name
              $disp_colname = MapColnameDispname($col_name);                          // replace underscores by spaces in headers names
              // display the col name in a new cell on same line and a select filter  //onchange='filterSelectedRows()' name='".$col_name."_items[]'
              echo "\n<th col-id='${col_name}' col-index='".($i+1)."'>${disp_colname}</th>" ;
              
              $dispColList[] = $col_name ;                                            // add the column to display to be used as key
            }
        
            echo("</tr></thead><tbody>");                                                                                 
            // DATA
            $row_index = 0 ;                                                          // to put a specific index in rows
            while($row_elem = pg_fetch_row($query_result)) {                          // for all the rows in the resulting table
              echo("\n<tr row-index=".$row_index.">");                                // creates a new line in the html table
              $count_cells = count($row_elem);                                        // number of elements in the row
              for ($j = 0; $j < $count_cells; $j++) {                                 // for all the elements in the line
                $current_cell = current($row_elem);                                   // current line cell element
                echo("<td key=".$dispColList[$j]." value='".$current_cell."'>".$current_cell."</td>"); // display the current element in a new cell
                next($row_elem);                                                      // taking the next element of the row
              }
              $row_index++ ;                                                          // next row index
              echo("</tr>");     
            }
            echo("</tbody></table>");                                                 // Closing the line, the body and the table
          $close=pg_close($dbconn);                                                   // Closing postgresql connexion
          echo("<br><br>");                                                           // </div> (end of the table display div)

        }                                                                             // end of the 'if there is a selected table'
        else {                                                                        // If no selected table, dislpay stats

          // ________________________________________________
          // First graph for number of interactions per taxon

          $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres') or die('Unable to connect to the database : ' . pg_last_error());    // starting postgres connection

          $interaction_data = getAllInteractionsData($dbconn);
          $proteins_numbers = getAllNumberOfProts($dbconn);
          $number_of_bact_strains_families = getNumberOfBactStrainsAndFamPerTaxon($dbconn);
          
          pg_close($dbconn);

          $nbInts_global = $interaction_data['global'];
          $nbInts_human = $interaction_data['homo_sapiens'];
          $nbInts_mouse = $interaction_data['mus_musculus'];
          $nbInts_rat = $interaction_data['rattus_norvegicus'];

          $nb_host_prots_global = $proteins_numbers['global']['B'];
          $nb_host_prots_human = $proteins_numbers['homo_sapiens']['B'];
          $nb_host_prots_mouse = $proteins_numbers['mus_musculus']['B'];
          $nb_host_prots_rat = $proteins_numbers['rattus_norvegicus']['B'];
          $nb_patho_prots_global = $proteins_numbers['global']['A'];
          $nb_patho_prots_human = $proteins_numbers['homo_sapiens']['A'];
          $nb_patho_prots_mouse = $proteins_numbers['mus_musculus']['A'];
          $nb_patho_prots_rat = $proteins_numbers['rattus_norvegicus']['A'];

          $nb_bact_strains_global = $number_of_bact_strains_families['global']['strains'];
          $nb_bact_strains_human = $number_of_bact_strains_families['homo_sapiens']['strains'];
          $nb_bact_strains_mouse = $number_of_bact_strains_families['mus_musculus']['strains'];
          $nb_bact_strains_rat = $number_of_bact_strains_families['rattus_norvegicus']['strains'];
          $nb_bact_families_global = $number_of_bact_strains_families['global']['families'];
          $nb_bact_families_human = $number_of_bact_strains_families['homo_sapiens']['families'];
          $nb_bact_families_mouse = $number_of_bact_strains_families['mus_musculus']['families'];
          $nb_bact_families_rat = $number_of_bact_strains_families['rattus_norvegicus']['families'];

          echo("<!-- First graph for number of interactions per taxon -->
          <div class='stats_main_part_div'>
              <div class='stats_solo_text_div'>
                  <label class='default_text'>
                  On this page, you can explore <strong>graphical statistical summaries</strong> of the BactMentha database.<br>
                  You can also <strong><a href='#stat_table_selector_form' style='color:#429E9D'><u>display a table</u></a></strong> by 
                  selecting a specific statistics for a taxon (or for all taxa).
                  <br><br>
                  The BactMentha database stores <strong>$nbInts_global bacteria-host protein-protein interactions (PPIs) entries</strong>
                  distributed as follows:<br>
                   • <strong>$nbInts_human</strong> interactions (<strong>human</strong>),<br>
                   • <strong>$nbInts_mouse</strong> interactions (<strong>mouse</strong>),<br>
                   • <strong>$nbInts_rat</strong> interactions (<strong>rat</strong>).
                  <br><br>
                  These interactions involve <strong>$nb_host_prots_global distinct host proteins</strong> (respectively 
                  $nb_host_prots_human for human, $nb_host_prots_mouse for mice and $nb_host_prots_rat for rat) and 
                  <strong>$nb_patho_prots_global distinct bacterial proteins</strong> ($nb_patho_prots_human interacting 
                  with human proteins, $nb_patho_prots_mouse with mouse proteins, and $nb_patho_prots_rat with rat proteins).
                  <br><br>
                  In total, <strong>$nb_bact_strains_global bacterial strains from $nb_bact_families_global distinct families</strong>
                  are represented in BactMentha:<br> 
                   • <strong>$nb_bact_strains_human strains</strong> from <strong>$nb_bact_families_human families</strong> 
                   (<strong>human</strong>),<br>
                   • <strong>$nb_bact_strains_mouse strains</strong> from <strong>$nb_bact_families_mouse families</strong> 
                   (<strong>mouse</strong>),<br>
                   • <strong>$nb_bact_strains_rat strains</strong> from <strong>$nb_bact_families_rat families</strong> 
                   (<strong>rat</strong>).<br>
                  </label>
              </div>
              <div class='stats_solo_fig_div'>
                  <div class='adv_search_titles' style='text-align:center;'>
                      Number of bacteria-host protein-protein interactions per host
                  </div>
                  <div class='stats_solo_figure_canvas'>
                    <div id='MainChart' class='Chart_class' style='width:100%; height:350px';>
                  </div></div>
              </div>
          </div>");

          // __________________________________________________________________________________________________
          // Second graph section to get annoteted interactions statistics for number of interactions per taxon

          echo("<!-- Second graph section for annotated interactions -->
          <div class='stats_main_part_div'>
              <div class='stats_fig_div'>
                  <div class='adv_search_titles' style='text-align:center;'>
                      Number of full annotated, partially annotated or unannotated interactions per host
                  </div>
                  <div class='stats_canvas_main_figure'>Global<br>
                    <div id='AnnotChart1_Global' class='Chart_class' style='width:100%; height:350px;'></div></div><br>
                  <div class='stats_canvas_secondary_figure'>Human<br>
                    <div id='AnnotChart1_Human' class='Chart_class' style='width:100%; height:300px;'></div></div>
                  <div class='stats_canvas_secondary_figure'>Mouse<br>
                    <div id='AnnotChart1_Mouse' class='Chart_class' style='width:100%; height:300px;'></div></div>
                  <div class='stats_canvas_secondary_figure'>Rat<br>
                    <div id='AnnotChart1_Rat' class='Chart_class' style='width:100%; height:300px;'></div></div>
              </div>

              <div class='stats_text_div'>
                  <label class='default_text'>
                  BactMentha provides different types of <strong>additional annotations</strong> for protein-protein 
                  interactons (PPIs) that can be displayed in the \"Annotation\" column of the result tables:<br><br>

                   • <strong>Bacterial Protein Annotations (PA):</strong><br>
                   Bacterial proteins have been annotated as virulence factors or effectors after 
                   perfomring BLASTp sequences searches against <a class='table_link' href='http://www.mgc.ac.cn/VFs/' 
                   target='_blank'>Virulence Factor Database (VFDB)</a> and <a class='table_link' 
                   href='https://bastionhub.erc.monash.edu/' target='_blank'>BastionHub</a> databases.<br><br>

                   • <strong>Cellular Components (CC):</strong><br>
                   Uniprot subcellular localization data have been gathered for all the interactors when available.<br><br>

                   • <strong>Binding Regions (BR):</strong><br>
                  Available experimental interaction binding regions have been retreived from IMEx databases for either the 
                  host or the bacterial protein (or both).<br><br>

                   • <strong>MimicINT Interfaces (MI):</strong><br>
                   <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
                   target='_blank'>MimicINT</a> was used to infer interaction interfaces for bacteria-host interactions 
                   using known interaction templates (domain-domain or motif-domain). More details about the mimicINT 
                   workflow can be found in the documentation.<br><br>

                   • <strong>Alphafold Complexes (AF):</strong><br>
                   SFor a subset of protein interaction complex structures have been predicted using 
                   AlphaFold3 via MassiveFold workflow. An archive with all the predicted complexes can 
                   be downloaded directly on the <a href='/Download' class='table_link' target='_blank'>
                   download page</a>.<br><br>
                   
                   The graphs on the left reports statistics on three subsets of BactMentha interaction data:<br>
                   • <strong>Fully annotated interactions</strong> correspond to interactions for which the five possible
                   annotations are present.<br>
                   • <strong>Partially annotated interactions</strong> correspond to interactions for which at least one of 
                   the five types of possible annotations is present.<br>
                   • <strong>Unannotated interactions</strong> correspond to interactions for which none of the five 
                   possible annotations are present.<br>
                  <br>
                  <i><u>N.B.</u>: mimicINT and AlphaFold predictions are only available for the Human host.</i>
                  </label>
              </div>
          </div>");

          // _____________________________________________________________________________________________________________
          // Third graph section to get the proportions of each combinations of annotated interactions for each host taxon

          echo("<!-- Third graph section to get the proportions of each combinations of annotated interactions for each host taxon -->
          <div class='stats_main_part_div'>
              <div class='stats_text_div'>
                  <label class='default_text'>
                  On the right, the plots display the proportions of interaction annotations in the whole dataset and for each host.<br><br>

                   • <label style='background:#f6a951;color:#52a097'>___</label> <strong>Bacterial Protein Annotation (PA)</strong>: 
                      The bacterial protein in the interaction is annotated as virulence factor or effector.<br><br>
                   • <label style='background:#dfb763;color:#52a097'>___</label> <strong>Cellular Components (CC)</strong>: 
                      The subcellular localization is known for at least one of the interactors.<br><br>
                   • <label style='background:#3eb489;color:#52a097'>___</label> <strong>Binding Regions (BR)</strong>: 
                      An expermiental binding region exist for at least one of the interactors.<br><br>
                   • <label style='background:#419d9d;color:#52a097'>___</label> <strong>MimicINT Interfaces (MI)</strong>: 
                      MimicINT interfaces are available for the interaction partners (always both sided and only for human host).<br><br>
                   • <label style='background:#5985c1;color:#52a097'>___</label> <strong>AlphaFold Complexes (AF)</strong>: 
                      The interaction complex have been predicted with AlphaFold3 (only for some bacteria-human interactions).<br>
                  <br>
                  <!-- <strong>Enhancing interaction annotations with mimicINT Interfaces (MI): </strong><br>
                  <br>
                  Integrating MI inference has importantly increased the annotation of protein interactions in BactMentha. 
                  Specifically, <strong>MI-based predictions have led to over 10% more annotated interactions</strong> 
                  (MI alone), Additionally, when MI predictions overlap with known binding tegions (BR), they help refine 
                  existing interaction data.<br>
                  <br>
                  This improvement is valuable for two key reasons:<br>
                   • <strong>Increased resolution:</strong> MI annotations often pinpoint defined interaction determinants within 
                   experimentally identified binding regions. While BR annotations encompass larger protein segments necessary or 
                   sufficient for interaction, MI provide annotations of specific domains or short linear motifs (SLiMs), thus 
                   suggesting a potential interaction molecular mechanism.<br>
                   • <strong>Guiding experimental validation:</strong> The agreement between MI predictions and BR suggests 
                   that MI-based annotations are reliable. These insights can help design new experiments to validate predicted 
                   interactions, especially when no experimental data is available yet.<br>
                  <br> -->
                  </label>
              </div>

              <div class='stats_fig_div'>
                  <div class='adv_search_titles' style='text-align:center;'>
                      Proportions of interactions annotation types (among annotated interactions) per host
                  </div>
                  <div class='stats_canvas_main_figure'>Global<br>
                    <div id='AnnotChart2_Global' class='Chart_class' style='width:100%; height:350px'></div></div><br>
                  <div class='stats_canvas_secondary_figure'>Human<br>
                    <div id='AnnotChart2_Human' class='Chart_class' style='width:100%; height:300px'></div></div>
                  <div class='stats_canvas_secondary_figure'>Mouse<br>
                    <div id='AnnotChart2_Mouse' class='Chart_class' style='width:100%; height:300px'></div></div>
                  <div class='stats_canvas_secondary_figure'>Rat<br>
                    <div id='AnnotChart2_Rat' class='Chart_class' style='width:100%; height:300px'></div></div>
              </div>
          </div>");

          // __________________________________________________________________________________________________
          // Fourth (and last) graph section to get annoteted interactions statistics for number of interactions per taxon

          echo("<!-- Fourth graph section for annotated interactions -->
          <div class='stats_main_part_div'>
              <div class='stats_fig_div'>
                  <div class='adv_search_titles' style='text-align:center;'>
                      Number of interactions (and their proportion of bacterial protein annotations) for the 10 first bacterial taxa
                  </div>
                  <div class='stats_canvas_pairwise_figure'>Global<br>
                    <div id='BactStatsChart_Global' class='Chart_class' style='width:100%; height:350px'></div></div><br>
                  <div class='stats_canvas_pairwise_figure'>Human<br>
                    <div id='BactStatsChart_Human' class='Chart_class' style='width:100%; height:300px'></div></div>
                  <div class='stats_canvas_pairwise_figure'>Mouse<br>
                    <div id='BactStatsChart_Mouse' class='Chart_class' style='width:100%; height:300px'></div></div>
                  <div class='stats_canvas_pairwise_figure'>Rat<br>
                    <div id='BactStatsChart_Rat' class='Chart_class' style='width:100%; height:300px'></div></div>
              </div>

              <div class='stats_text_div'>
                  <label class='default_text'>
                      <strong>Bacterial Protein Annotation generation:</strong><br>
                      <br>
                      To better understand how bacterial proteins interact with host proteins, we annotated bacterial 
                      proteins using data from the <a class='table_link' href='http://www.mgc.ac.cn/VFs/' 
                      target='_blank'>Virulence Factor Database (VFDB)</a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' 
                      target='_blank'>BastionHub</a>. These annotations were assigned through BLASTp similarity searches between each bacterial protein in BactMentha 
                      and the proteins in these databases.<br>
                      <br>
                      A given annotation was assigned to a bacterial protein if it meets the following criteria:<br>
                       • <strong>Sequence Identity ≥ 30%:</strong> The bacterial protein needed to be at least 30% identical to a 
                       known protein in the database.<br>
                       • <strong>Alignment Coverage ≥ 75%:</strong> At least 75% of the bacterial protein sequence should be aligned with 
                       the reference protein.<br>
                      <br>
                      If a bacterial protein meetst both conditions, it is annotated with the given functional category such 
                      as secreted effectors, immune modulation, adherence, or motility.<br>
                  </label>
              </div>
          </div>");
        }
      ?>

    <!-- Scroll-to-top button -->
    <div class="scroll-to-top">
        <a href="#top">
            &#9650; <!-- Up arrow symbol ▲ -->
        </a>
    </div>


<script>

  document.querySelectorAll('.Chart_class').forEach(container => {
    const chart_id = container.id;
    const total_height = parseInt(container.style.height);
    const plot_height = total_height - 20;
    fetch(`/static/img/graphs/${chart_id}.html`)
      .then(response => {
        return response.text();
      })
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        container.innerHTML = doc.body.innerHTML;
        container.querySelectorAll('script').forEach(oldScript => {
          const newScript = document.createElement('script');
          if (oldScript.src) {
            newScript.src = oldScript.src;
          } else {
            newScript.textContent = oldScript.textContent;
          }
          oldScript.replaceWith(newScript);
        });
        const plot = container.querySelector('.plotly-graph-div');
        if (plot) {
          plot.parentElement.style.height = `${plot_height}px`;
          plot.parentElement.style.width = '100%';
          plot.style.height = `${plot_height}px`;
          plot.style.width = '100%';
          Plotly.relayout(plot, {
            width: container.clientWidth,
            height: plot_height
          });
        }
      })
      .catch(error => {
        console.error(chart_id, error);
      });
  });

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

      // Setup - add a text input to each footer cell
      $('#stats_table thead tr')
          .clone(true)
          .addClass('filters')
          .appendTo('#stats_table thead');
  
      var table = $('#stats_table').DataTable({
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

      // Make the toggle column div become active
      document.querySelectorAll('a.toggle-vis').forEach((el) => {
          el.addEventListener('click', function (e) {                   // each time the user clicks
              e.preventDefault();

              var a = $(this).closest('a');
              let columnIdx = e.target.getAttribute('data-column');     // takes the index of the column
              let column = table.column(columnIdx);                     // and retrieve the corresponding column in the table
      
              // Toggle the visibility
              column.visible(!column.visible());                        // change the column visibility
              if (a.hasClass('clicked_button')) {                             // change the class to change the text color
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


      <br><br>

<!---------------------------------------------------------------- VIEW SELECTION ----------------------------------------------------------------->
      <h2 class='home_news_title1'> Select a table to display : </h2>
      <div class='contact_title_bar'></div><br>
      <form name="view_choice"> <br>              <!-- select the view to display by selecting the type of view and the taxon(s) -->
        <div>

        <select name="stats_view_type" method="GET" class='view_selector' required>  <!-- selecting the view type -->
          <option value="" selected>-- select an option --</option>                             <!-- views containing data -->
          <!-- views containing stats for bacterial interactions for one or all taxa -->
          <option value="bacterial_interaction_stats"
                  <?php echo isItSelected($_GET["stats_view_type"], "bacterial_interaction_stats"); ?>
                  >Bacterial interaction statistics</option>
          <!-- views containing stats for bacterial family interactions for one or all taxa -->
          <option value="bacterial_family_interaction_stats"
                  <?php echo isItSelected($_GET["stats_view_type"], "bacterial_family_interaction_stats"); ?>
                  >Bacterial family interaction statistics</option>
          <!-- views containing stats for bacterial annotations for one or all taxa -->
          <option value="bacterial_annotation_stats"
                  <?php echo isItSelected($_GET["stats_view_type"], "bacterial_annotation_stats"); ?>
                  >Bacterial annotation statistics</option>
        </select>

        <select name="stats_taxon" method="GET" class='view_selector' required>     <!-- selecting the taxon or taxa -->
          <option value="" selected>-- select an option --</option>
          <!-- all taxa -->
          <option value="all" 
                  <?php echo isItSelected($_GET["stats_taxon"], "all"); ?>
                  >All</option>
          <!-- only Homo sapiens -->
          <option value="homo_sapiens" 
                  <?php echo isItSelected($_GET["stats_taxon"], "homo_sapiens"); ?>
                  ><i>Homo sapiens</i></option>
          <!-- only Mus musculus -->
          <option value="mus_musculus" 
                  <?php echo isItSelected($_GET["stats_taxon"], "mus_musculus"); ?>
                  ><i>Mus musculus</i></option>
          <!-- only Rattus norvegicus -->
          <option value="rattus_norvegicus" 
                  <?php echo isItSelected($_GET["stats_taxon"], "rattus_norvegicus"); ?>
                  ><i>Rattus norvegicus</i></option>
        </select>

        <input type="submit" value="Display" class="bm_button">

        </div><br>
      </form>

<!----------------------------------------------------------------- RESET BUTTON ------------------------------------------------------------------>
      <?php 
        // button to reset all session variables (selected view, columns...)
        echo "<form id='stat_table_selector_form'> <input type='submit' name='refresh_all' value='Reset' class='bm_button'> </form>"; // corresponding code if isset at the beginning of the page
      ?>

      </main><br><br><br>


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