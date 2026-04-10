<?php
    // ____________________
    // START OF THE SESSION
    if (empty($_SESSION)) {session_start(); }   // if first page (without refresh)
    error_reporting(E_ERROR | E_PARSE);         // to avoid some warning messages

    if(isset($_GET['clearsearch'])) {           // if the reset button is pressed
      session_destroy();                        // empty the session variables
      session_start();                          // start a new session
    }

    // ______________________________________
    // UNSET PREVIOUS PAGES SESSION VARIABLES
    if (isset($_SESSION['data_view_type'])) {         // from the data page
      unset($_SESSION['data_view_type']);
    }
    if (isset($_SESSION['data_taxon'])) {             // from the data page
      unset($_SESSION['data_taxon']);
    }
    if (isset($_SESSION['data_selected_view'])) {     // from the data page
      unset($_SESSION['data_selected_view']);
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


<!--=================================================================== MAIN ====================================================================-->
<main class='home_main'>
    <nav class="navbar">
        <ul>
            <li><a href="/Home?search_value=&clearsearch=Clear">Home</a></li>
            <li><a class="current_page" href="/Browse">Browse</a></li>
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
    <br><br>

    <?php


    if (isset($_GET["filterValues"])) {
        $_SESSION["filterValues"] = $_GET["filterValues"];
    };
    if(!empty($_SESSION["filterValues"])) {
        $filterValues = $_SESSION["filterValues"];
    };
    if (isset($_GET["ResestAdvancedSearchBtn"])) {
        unset($_GET["filterValues"]);
        unset($_SESSION["filterValues"]);
        unset($filterValues);
        echo "<script> window.location.href = '/Search'; </script>";
    }

    if (isset($_GET["unsetBmIdsToExport"])) {
      unset($_SESSION["BmIdsToExport_advSearch"]);
    }
    elseif(isset($_SESSION["BmIdsToExport_advSearch"])) {
      $files = downloadSearchCsvFiles($_SESSION["BmIdsToExport_advSearch"]);
      echo '<script>downloadFiles(' . $files . ');</script>';
    };

    if (isset($_POST["BmIdsToExport"])) {
        $_SESSION["BmIdsToExport_advSearch"] = $_POST["BmIdsToExport"];
    }


if (!empty($filterValues)) {
    // for each filter of the advanced search
    foreach ($filterValues as $row) {
        // Access individual elements for each row
        $advSearchType = $row['advSearchType'];
        $advSearchCondition = $row['advSearchCondition'];
        $advSearchValue = $row['advSearchValue'];
        $verif = checkConditionFormat($advSearchType, $advSearchCondition, $advSearchValue);
        if ($verif == true) {
          continue;
        } 
        else { // if verif == false
          $type_format = getFormatToDisplay($advSearchType);
          echo "<br>One of the values for <strong>$type_format</strong> doesn't seem to match the expected format. Please try again.
          <br>You can refer to the 'Help' button to diplay some help.";
        }
    }
    // Create the condition
    $advancedSearchQuery = createAdvancedSearchQuery($filterValues);
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
                      or die('Unable to connect to the database : ' . pg_last_error());       // starting postgres connection
    $query_result = pg_query($advancedSearchQuery) or die('Error in the query execution : ' . pg_last_error());

    // ____________________________________________
    // RETURN BUTTON TO GET BACK TO ADV SEARCH FORM

    echo("<br><br>
          <div class='backToAdvSearchDiv'><form>
              <button class='bm_button' type='submit' name='ResestAdvancedSearchBtn'>&larr; Back to Advanced Search</button>
          </from></div>");

    //___________________________________________________________________________________________________________
    // DATATABLE TOGGLE COLUMN DIV  (-> always in the 'if a table is selected')

    $col_list = ['mnt_interaction_id', 'pathogen_info', 'bact_prot_annot', 'interactor_ida', 'gene_a', 'taxon_interactor_ida',
                  'interactor_idb', 'gene_b', 'taxon_interactor_ida', 'detection_method', 'interaction_type',
    'publication_id', 'confidence_score', 'annotations'];
    $col_nb = sizeof($col_list);
    $toggleColumnDiv = createToggleColDiv($col_list, $col_nb, 0); // 0 is the index of the bm_id
    echo $toggleColumnDiv;
    // _____________
    // TABLE DISPLAY

    searchTableCreation($query_result);
    $close=pg_close($dbconn);                                                          // Closing postgresql connexion
    echo("<br><br>");

} else { // if not submitted : display the advanced search form
    // ADVANCED SEARCH  
    //  echo("<br>");
    displayAdvSearchPresentation();
    displayAdvSearchHelpDiv();
    echo("<br><br>");
    echo("<div class='advanced_search_div'>\n");
    displayAdvSearchForm();
    echo("<br><br>");
    // displayAdvancedSearchButtons();
    echo("</div>");

} 

?>

<!-- Scroll-to-top button -->
<div class="scroll-to-top">
    <a href="#top">
        &#9650; <!-- Up arrow symbol ▲ -->
    </a>
</div> 


<script>

    // Customize the 'Annotations' column to see if there are infos about mimicint interfaces, annotations, or mimicint_xref
    $(document).ready(function() {

        // Hide the scroll-to-top button initially
        $('.scroll-to-top').hide();

        // update the placeholders when the values of the select dropdowns change
        $('.advanced_search_div').on('change', '.filter_container .selectType, .filter_container .selectCondition', function() {
            updatePlaceholder($(this).closest('.filter_container'));
        });

        // Call the update once when the page is loaded
        $('.filter_container').each(function() {
            updatePlaceholder($(this));
        });

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
        $('#search_table thead tr')
            .clone(true)
            .addClass('filters')
            .appendTo('#search_table thead');
        var table = $('#search_table:not(.child_table)').DataTable({
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
                          var html_Annotcb_text = `<label class='cb_annot_labels' title='Bacterial Protein Annotations'>&nbspPA&nbsp</label></label>
                            <label class='cb_annot_labels' title='Binding Regions'>&nbspBR&nbsp</label>
                            <label class='cb_annot_labels' title='Mimicint Interfaces'>&nbspMI&nbsp</label><div class="annotations_cb_disp">
                            <input class='annotCb Pa_button' type="checkbox" id="Cb_ProtAnnot" name="cb_annotations" title="Bacterial Protein Annotations">
                            <input class='annotCb Br_button' type="checkbox" id="Cb_Features" name="cb_annotations" title="Binding Regions">
                            <input class='annotCb Mi_button' type="checkbox" id="Cb_Mimicint" name="cb_annotations" title="Mimicint Interfaces"></div>`;
                          $(cell).html(html_Annotcb_text);

                          $('.annotCb').on('change', function (e) {
                              // Retrieve the states of all checkboxes (checked or not).
                              var isChecked_mi = $("#Cb_Mimicint").is(":checked");
                              var isChecked_br = $("#Cb_Features").is(":checked");
                              var isChecked_pa = $("#Cb_ProtAnnot").is(":checked");

                              // Check if all checkboxes are unchecked
                              var allUnchecked = !isChecked_mi && !isChecked_br && !isChecked_pa;

                              // Remove the previous custom filter if present
                              $.fn.dataTable.ext.search.pop();

                              // If any of the checkboxes is checked, apply the custom filter function
                              if (!allUnchecked) {
                                  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                                      var row = $(api.row(dataIndex).node());
                                      var has_mi = row.find('a.moreInfoBtnLinkBlue').length > 0;
                                      var has_br = row.find('a.moreInfoBtnLinkGreen').length > 0;
                                      var has_pa = row.find('a.moreInfoBtnLinkYellow').length > 0;

                                      if (isChecked_mi && isChecked_br && isChecked_pa) {
                                          return has_mi && has_br && has_pa;
                                      } else if (isChecked_mi && isChecked_br) {
                                          return has_mi && has_br ;
                                      } else if (isChecked_mi && isChecked_pa) {
                                          return has_mi && has_pa;
                                      } else if (isChecked_br && isChecked_pa) {
                                          return has_br && has_pa ;
                                      } else if (isChecked_mi) {
                                          return has_mi;
                                      } else if (isChecked_br) {
                                          return has_br;
                                      } else if (isChecked_pa) {
                                          return has_pa;
                                      } else {
                                          return true; // Display all rows when no checkboxes are checked.
                                      }
                                  });
                              }

                              // Redraw the table to apply the custom filter (or to remove the filter if all checkboxes are unchecked).
                              api.draw();
                          });

                        } // end of the else for search with 'annotations' column
                        

                    }) // end of the 'for each column index of the api
            }, // end of the initComplete function
            "columnDefs": [
              {
                "className":  "dt-head-center",
                "targets": "_all"
              },
              // hide the annotations column data and display the annotations buttons instead
              {
                "className":      'details-control',
                "orderable":      false,
                // "data":           null,
                "target": 13,
                "render": function (data, type, full, meta) {
                  var htmlText = displayAnnotationButtons(data);    // 0 is the index of the mnt_interaction_id column
                  return htmlText;
                }
              },
              {
                "targets": [3,5],
                "width": "150px"
              }
            ], // end of the column definition
            "order": [], // no default order to keep the one from the DB table
            dom: 'Blfrtip',
            buttons: [
              {text: 'Show/Hide Columns',
                className: 'bm_button',
                attr: {id: 'ToggleColButton'},
                action: function(){
                  $('#dispColChoiceDiv').toggle();
                }},
              {text: 'CSV + annotations to CSV',
                className: 'bm_button',
                attr: {id: 'ExportAllBtn'},
                action: function() {
                    downloadAllInteractionData('#search_table', AllBmInteractionIds);
                }
              },
              // {extend: 'copy',
              //   className: "bm_button",
              //   exportOptions: {columns: ':visible.exportable'}},
              {extend: 'csv',
                className: "bm_button",
                exportOptions: {columns: ':visible.exportable'}},
              {extend: 'excel',
                className: "bm_button",
                exportOptions: {columns: ':visible.exportable'}},
            ],

        }); // end of datatables initialisation
        table.buttons().container().appendTo( $('.dataTables_filter'));
        

        // Add event listener for opening and closing details for mimicint interfaces
        $('#search_table tbody').on('click', '.moreInfoBtnLinkBlue', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            if (row.child.isShown() && tr.hasClass('bluechild')) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('bluechild');
            }
            else {
              if (row.child.isShown() && tr.hasClass('greenchild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('greenchild');
              }
              else if (row.child.isShown() && tr.hasClass('yellowchild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('yellowchild');
              }
              // Open this row
              row.child(format_blue(row.data(), 0)).show();         // 0 is the index of the mnt_interaction_id in the table
              tr.addClass('shown');
              tr.addClass('bluechild');
            }
        });

        // Add event listener for opening and closing details for interaction feature
        $('#search_table tbody').on('click', '.moreInfoBtnLinkGreen', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            if (row.child.isShown() && tr.hasClass('greenchild')) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('greenchild');
            }
            else {
              if (row.child.isShown() && tr.hasClass('bluechild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('bluechild');
              }
              else if (row.child.isShown() && tr.hasClass('yellowchild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('yellowchild');
              }
              // Open this row
              row.child(format_green(row.data(), 0)).show();      // 0 is the index of the mnt_interaction_id in the table
              tr.addClass('shown');
              tr.addClass('greenchild');
            }
        });

        // Add event listener for opening and closing details for protein annotation
        $('#search_table tbody').on('click', '.moreInfoBtnLinkYellow', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            if (row.child.isShown() && tr.hasClass('yellowchild')) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('yellowchild');
            }
            else {
              if (row.child.isShown() && tr.hasClass('greenchild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('greenchild');
              }
              else if (row.child.isShown() && tr.hasClass('bluechild')) {
                row.child.hide();
                tr.removeClass('shown');
                tr.removeClass('bluechild');
              }
              // Open this row
              row.child(format_yellow(row.data(), 0)).show();           // 0 is the index of the mnt_interaction_id in the table
              tr.addClass('shown');
              tr.addClass('yellowchild');
            }
        });

        // // hide the first column (mnt_interaction_id) column by default
        let columnIdx = 0;                                                // takes the index of the column
        let column = table.column(columnIdx);                             // and retrieve the corresponding column in the table
        column.visible(false);                                            // hide the column
        // Make the toggle column div become active
        document.querySelectorAll('a.toggle-vis').forEach((el) => {
            el.addEventListener('click', function (e) {                   // each time the user clicks
                e.preventDefault();

                var a = $(this).closest('a');
                var div = $(this).closest('div');
                let columnIdx = e.target.getAttribute('data-column');     // takes the index of the column
                let column = table.column(columnIdx);                     // and retrieve the corresponding column in the table
        
                // Toggle the visibility
                column.visible(!column.visible());                        // change the column visibility
                if (a.hasClass('clicked_button')) {                       // change the class to change the text color
                    a.removeClass('clicked_button');
                    a.addClass('unclicked_button');
                }
                else if (a.hasClass('unclicked_button')) {
                    a.removeClass('unclicked_button');
                    a.addClass('clicked_button');
                }
                
            }); // end of the eventListener for 'click'

        }); // end of the part that changes column visibility

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
      <img src="/static/img/institutions/DIRCOM-Logo-AMIDEX.png" class="footer_logo">
      <img src="/static/img/institutions/anr.png" class="footer_logo">
      <img src="/static/img/institutions/MESR.png" class="footer_logo">
      <img src="/static/img/institutions/france_2023.png" class="footer_logo">
      <img src="/static/img/institutions/jpi.png" class="footer_logo">
    </div>
    <div class='footer_cpright'>
        <p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><span property="dct:title">BactMentha</span> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt=""><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt=""></a></p> 
      </div>
</footer>
</body>
</html>