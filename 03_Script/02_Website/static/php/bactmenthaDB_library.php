<?php
// Php library for BactMentha Website
// coding : UTF-8
// Author : Lou BERGOGNE


// __________________________________________________________________________________________ //
// _______________________                                           ________________________ //
// _______________________ FOR TABLES AND VIEW SELECTION AND DISPLAY ________________________ //
// __________________________________________________________________________________________ //

// Called in the stats page to know if a view has already been selected by the user
function isItSelected($getCheckedValue, $selectValue) {
    /**
     * This function aims to retrieve a selected value in a select menu with only one choice.
     * If a value has been selected, the corresponding value will be selected by default
     * when the page will refresh.
     * 
     * Args:
     * - $getCheckedValue: the value stocked i the $_POST[] variable corresponding to the select menu.
     * - $sessionCheckedValue: the session variable that stock the previous $_POST[] value for the select menu.
     * - $selectValue: the value of an option in the select menu.
     * 
     * Return:
     * - $option: "selected" if the option is the default selected value, else "".
     */
    if ($getCheckedValue == $selectValue) {
        $option = 'selected' ;
    }
    else {
        $option = '' ;
    }
    return $option ;
}

// Get the database view name using the type of data and the specified taxon
function findViewWithTypeAndTaxon($getViewType, $getTaxon) {
    /**
     * This function permits to get the view name knowing which view type and
     * taxon has been selected.
     * It uses two lists of constants for available views and taxons.
     * 
     * Args:
     * - $getViewType: the value selected for the view type in the scrolling menu.
     * - $getTaxon: the value selected for the taxon in the scrolling menu.
     * 
     * Return:
     * - $databaseViewName: name of the view (or table name) in the database.
     */
    $listViewTypes = array("interaction_data" => "A", "bacterial_interaction_stats" => "B",
     "bacterial_family_interaction_stats"=> "C", "bacterial_annotation_stats"=> "D",
     "who_priority" => "E", "who_interaction_data" => "F", "mi_interaction_data" => "G", 
     "br_interaction_data" => "H", "hg3_interaction_data" => "I", "af_interaction_data" => "K");

    $listTaxa = array("all" => "A", "homo_sapiens" => "B", "mus_musculus" => "C", "rattus_norvegicus" => "D",
                      "bact_who_hazard" => "E", "bact_who" => "F", "bact_hazard2" => "G", "bact_hazard3" => "H");

    $correspondanceTable = array("AA" => "interaction_full", "AB" => "view_interaction_full_homo_sapiens",
    "AC" => "view_interaction_full_mus_musculus", "AD" => "view_interaction_full_rattus_norvegicus",
    "BA" => "view_bact_interaction_stats_global", "BB" => "view_bact_interaction_stats_homo_sapiens",
    "BC" => "view_bact_interaction_stats_mus_musculus", "BD" => "view_bact_interaction_stats_rattus_norvegicus",
    "CA" => "view_bact_family_interaction_stats_global", "CB" => "view_bact_family_interaction_stats_homo_sapiens",
    "CC" => "view_bact_family_interaction_stats_mus_musculus", "CD" => "view_bact_family_interaction_stats_rattus_norvegicus",
    "DA" => "view_annotation_stats_global", "DB" => "view_annotation_stats_homo_sapiens", "DC" => "view_annotation_stats_mus_musculus", 
    "DD" => "view_annotation_stats_rattus_norvegicus", "EE" => "view_who_priority_hazard_group", "EF" => "view_who_priority", 
    "EG" => "view_hazard_group2", "EH" => "view_hazard_group3", "FA" => "interaction_full", "FB" => "view_interaction_full_homo_sapiens", 
    "FC" => "view_interaction_full_mus_musculus", "FD" => "view_interaction_full_rattus_norvegicus",
    "GB" => "view_interaction_full_homo_sapiens", "HB" => "view_interaction_full_homo_sapiens", "IB" => "view_interaction_full_homo_sapiens",
    "KB" => "view_interaction_full_homo_sapiens"
    );

    $viewLetters = "" ;

    foreach ($listViewTypes as $Type => $TypeLetter){
        if ($Type == $getViewType){
            $viewLetters .= $TypeLetter ;
        }
    }
    foreach ($listTaxa as $Tax => $TaxLetter){
        if ($Tax == $getTaxon){
            $viewLetters .= $TaxLetter ;
        }
    }

    foreach ($correspondanceTable as $lettersPair => $viewName){
        if ($lettersPair == $viewLetters) {
            $databaseViewName = $viewName ;
        }
    }
    return $databaseViewName ;
}

// Get an official name to display in the option slect for the database tables / views
function databaseToOfficialViewName($getSelectedView) {
    /**
     * This function is used to convert the view name (with underscores) into a more readable name
     * to display on the website when the view is selected.
     * 
     * Args:
     * - $getSelectedView: view name (with uderscores) from the select menu (same as database view name).
     * 
     * Return:
     * - $labelViewName: Name of the view to display on the website (more readable).
     */
    $view_names_list = array("interaction_full" => "Global interactions",
        "view_interaction_full_homo_sapiens" => "<i>Homo sapiens</i> interactions",
        "view_interaction_full_mus_musculus" => "<i>Mus musculus</i> interactions",
        "view_interaction_full_rattus_norvegicus" => "<i>Rattus norvegicus</i> interactions",
        "view_bact_interaction_stats_global" => "Global bacteria interaction statistics",
        "view_bact_interaction_stats_homo_sapiens" => "Bacteria interaction statistics for <i>Homo sapiens</i>",
        "view_bact_interaction_stats_mus_musculus" => "Bacteria interaction statistics for <i>Mus musculus</i>",
        "view_bact_interaction_stats_rattus_norvegicus" => "Bacteria interaction statistics for <i>Rattus norvegicus</i>",
        "view_bact_family_interaction_stats_global" => "Global bacteria family interaction statistics",
        "view_bact_family_interaction_stats_homo_sapiens" => "Bacteria family interaction statistics for <i>Homo sapiens</i>",
        "view_bact_family_interaction_stats_mus_musculus" => "Bacteria family interaction statistics for <i>Mus musculus</i>",
        "view_bact_family_interaction_stats_rattus_norvegicus" => "Bacteria family interaction statistics for <i>Rattus norvegicus</i>",
        "view_annotation_stats_global" => "Global bacteria annotation statistics",
        "view_annotation_stats_homo_sapiens" => "Bacteria annotation statistics for <i>Homo sapiens</i>",
        "view_annotation_stats_mus_musculus" => "Bacteria annotation statistics for <i>Mus musculus</i>",
        "view_annotation_stats_rattus_norvegicus" => "Bacteria annotation statistics for <i>Rattus norvegicus</i>",
        "view_who_priority_hazard_group" => "Bacterial pathogens concerned as who priority or in hazard groups",
        "view_who_priority" => "Bacterial pathogens concerned as who priority or in hazard groups",
        "view_hazard_group2" => "Bacterial pathogens in hazard group 2",
        "view_hazard_group3" => "Bacterial pathogens in hazard group 3",
    );
    
    foreach ($view_names_list as $databaseName => $officialName){
        if ($databaseName == $getSelectedView){
            $labelViewName = $officialName;
        }
    }
    return $labelViewName;
}

// Called in the data / browse page to check if a table / view has already been selected by the user
function isViewSelected($sessionSelectedView) {
    /**
     * This function is used to find the selected table from the SESSION table
     * if not empty. If it is empty, the table name will be empty too.
     * 
     * Args:
     * - $sessionSelectedView: $_SESSION['selected_view'] table.
     * 
     * Return:
     * - $table_name: name of the view (or table) in the database.
     */
    if (!empty ($sessionSelectedView)) {
        $table_name = $sessionSelectedView ;
    }
    else {
        $table_name = "" ;
    }
    return $table_name ;
}

// Creates the div to select the columns to hide or to show (this div is hidden by default)
function createToggleColDiv($col_list, $col_nb, $bmId_index) {
    /**
     * Creates a toggle Column div to select the column to display or not using dataTables.
     * It creates a link for each column.
     * 
     * Args:
     * - col_list: list of column names for the chosen table
     * - col_nb: number of column/size of the list
     * - bmId_index: index of the bm_id column to hide (no button to show it)
     * 
     * Return:
     * - toggleDiv: the div to display in html before to be modified with Jquery and dataTable
     */
    $toggleDiv = "<br><br><div class='toggle_col_div' id='dispColChoiceDiv' style='display:none'><fieldset>Click on the name of a column to hide/show it:<br><br>";
    for ($i = 0 ; $i < $col_nb ; $i++) {
        $colname = $col_list[$i];
        $disp_name = MapColnameDispname($col_list[$i]);
        if ($i != $bmId_index) {
            $linkDiv = "<a class='toggle-vis clicked_button' data-column='${i}'> ${disp_name} </a>"; //<label style='color:#FFA52C'>|</label>
            $toggleDiv = $toggleDiv.$linkDiv;
        }
    }
    $toggleDiv = $toggleDiv."</fieldset></div><br><br>";
    return $toggleDiv;
}

// Map the actual table / view column names to the official names to display for the user
function MapColnameDispname($colname){
    /**
     * This function returns the column name to display depending on the column name.
     * 
     * Args:
     * - colname: name of the column in the database.
     * 
     * Return:
     * - dispname: name to dislay in the column and in the toggle column buttons.
     */
    $changingName = array("publication_id" => "PubMed",
                    "bact_prot_annot" => "Bact Prot Annot",
                    "interactor_ida" => "Identifier A",
                    "interactor_idb" => "Identifier B",
                    "taxon_interactor_ida" => "Taxon A",
                    "taxon_interactor_idb" => "Taxon B",
                    "detection_method" => "Detection Method",
                    "interaction_type" => "Interaction Type",
                    "mi_score" => "MI Score",
                    "annotations" => "Annotations",
                    "taxon_name" => "Taxon Name",
                    "taxon_id" => "Taxon ID",
                    "taxon_family" => "Taxon Family",
                    "annotated_proteins" => "Annotated Proteins",
                    "annotated_interactions" => "Annotated Interactions",
                    "source_vfdb" => "Source VFDB",
                    "source_bastionhub" => "Source BastionHub",
                    "total_annotated_proteins" => "Total Annotated Proteins",
                    "total_annotated_interactions" => "Total Annotated Interactions",
                    "total_source_vfdb" => "Total Source VFDB",
                    "total_source_bastionhub" => "Total Source BastionHub",
                    "annotation_description" => "Annotation Description",
                    "annotation_source" => "Annotation Source",
                    "interactions" => "Interactions",
                    "gene_a" => "Gene A",
                    "gene_b" => "Gene B",
                    "pathogen_info" => "Pathogen Info",
                    "who_priority" => "WHO priority",
                    "hazard_group" => "Hazard Group"
                    ); 
    $dispname = $colname; // Set an initial value

    foreach ($changingName as $DBname => $DispName) {
        if ($DBname == $colname) {
            $dispname = $DispName; // Update only if a match is found
            break; // No need to continue the loop if a match is found
        }
    }
    // Perform the replacement of underscores with spaces for non-matching columns
    $dispname = preg_replace('/_+/', ' ', $dispname);
    return $dispname;
}

// Called in browse / data page to create the query used to display the default table corresponding to specified the taxon
function browsePageCreationQuery($view_name) {
    /**
     * This function is creating a query to display the view from interaction full for the wanted taxon (in $view_name)
     * and add to it further informations about the gene name (uniprot_gene) and taxon name (taxon_name) from other tables.
     * 
     * Args:
     * - $view_name: name of the view to display with the further informations from uniprot_protein and uniprot_taxonomy
     * 
     * Return:
     * - psql_query: postgresql query to use in the Browse page query to display the resulting table
     */
    $psql_query = "SELECT 
    CONCAT(
        'who priority: ', 
        COALESCE(
            (SELECT UT_A.who_priority FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA), 
            'N/A'
        ), 
        '<br>hazard_group: ', 
        COALESCE(
            (SELECT UT_A.hazard_group FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA), 
            'N/A'
        ),
        ''
    ) AS pathogen_info,
    COALESCE(bp.bact_prot_annot, '-') AS bact_prot_annot,
    IF.interactor_idA,
    CONCAT(
        UP_A.uniprot_gene,
        '<br><br>',
        SPLIT_PART(UT_A.taxon_name, ' (', 1)
    ) AS gene_A,
    IF.taxon_interactor_idA,
    IF.interactor_idB, 
    CONCAT(
        UP_B.uniprot_gene,
        '<br><br>',
        SPLIT_PART(UT_B.taxon_name, ' (', 1)
    ) AS gene_B,
    IF.taxon_interactor_idB,
    IF.publication_id,
    IF.interaction_type,
    IF.detection_method,
    IF.MI_score, 
    IF.mnt_interaction_id,
    IF.annotations
    FROM
        ".$view_name." AS IF
    INNER JOIN uniprot_xref AS UX_A ON UX_A.interactor_id = IF.interactor_idA
    INNER JOIN uniprot_protein AS UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac
    INNER JOIN uniprot_taxonomy AS UT_A ON UT_A.taxon_id = UP_A.uniprot_taxon_id
    INNER JOIN uniprot_xref AS UX_B ON UX_B.interactor_id = IF.interactor_idB
    INNER JOIN uniprot_protein AS UP_B ON UP_B.uniprot_ac = UX_B.uniprot_ac
    INNER JOIN uniprot_taxonomy AS UT_B ON UT_B.taxon_id = UP_B.uniprot_taxon_id
    LEFT JOIN (
        SELECT 
            uniprot_ac, 
            STRING_AGG(DISTINCT annotation_description, ', ') AS bact_prot_annot
        FROM view_bact_prot_annotation
        GROUP BY uniprot_ac
    ) AS bp ON bp.uniprot_ac = SPLIT_PART(IF.interactor_ida, '-', 1)
    ";
    return $psql_query;
}

// check if the view requires the "Export All Data" button for datatables.
function getAddExportAllBtn($selected_view) {
    $dict_views = array(
    "interaction_full" => "true", 
    "view_interaction_full_homo_sapiens" => "true",
    "view_interaction_full_mus_musculus" => "true", 
    "view_interaction_full_rattus_norvegicus" => "true",
    "view_bact_interaction_stats_global" => "false", 
    "view_bact_interaction_stats_homo_sapiens" => "false",
    "view_bact_interaction_stats_mus_musculus" => "false", 
    "view_bact_interaction_stats_rattus_norvegicus" => "false",
    "view_bact_family_interaction_stats_global" => "false", 
    "view_bact_family_interaction_stats_homo_sapiens" => "false",
    "view_bact_family_interaction_stats_mus_musculus" => "false", 
    "view_bact_family_interaction_stats_rattus_norvegicus" => "false",
    "view_annotation_stats_global" => "false", 
    "view_annotation_stats_homo_sapiens" => "false", 
    "view_annotation_stats_mus_musculus" => "false", 
    "view_annotation_stats_rattus_norvegicus" => "false", 
    "view_who_priority_hazard_group" => "false", 
    "view_who_priority" => "false", 
    "view_hazard_group2" => "false", 
    "view_hazard_group3" => "false"
    );
    return $dict_views[$selected_view];
}

// __________________________________________________________________________________________ //
// __________________________________                       _________________________________ //
// __________________________________ QUERYING THE DATABASE _________________________________ //
// __________________________________________________________________________________________ //

// ________________________________________________________________
// FOR THE NEWS DIV AND BASIC STATS (RIGHT LAYER) + STATISTICS PAGE

// Get the date of the last update
function findLastUpdate($dbconn) {
    /**
     * Retrieve the last update date from the metadata table.
     * 
     * Args:
     * - dbconn: connection to the database
     * 
     * Return:
     * - date: of the last update
     */
    $psql_query = 'SELECT "version_or_update" FROM "metadata" WHERE "metadata" = \'last_update\'' ;
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $date = pg_fetch_row($query_result)[0];
    return $date ;
}

function getAllInteractionsData($dbconn) {
    $query = "
        SELECT taxon, COUNT(DISTINCT mnt_interaction_id) AS num_interactions
        FROM (
            SELECT 'global' AS taxon, mnt_interaction_id FROM interaction_full
            UNION ALL
            SELECT 'homo_sapiens', mnt_interaction_id FROM view_interaction_full_homo_sapiens
            UNION ALL
            SELECT 'mus_musculus', mnt_interaction_id FROM view_interaction_full_mus_musculus
            UNION ALL
            SELECT 'rattus_norvegicus', mnt_interaction_id FROM view_interaction_full_rattus_norvegicus
        ) t
        GROUP BY taxon;
    ";
    $result = pg_query($dbconn, $query) or die("Error:" . pg_last_error());
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[$row['taxon']] = $row['num_interactions'];
    }
    return $data;
}

function getAllNumberOfProts($dbconn) {
    $query = "
        SELECT taxon, 
               COUNT(DISTINCT interactor_ida) AS num_patho_prots, 
               COUNT(DISTINCT interactor_idb) AS num_host_prots
        FROM (
            SELECT 'global' AS taxon, interactor_ida, interactor_idb FROM interaction_full
            UNION ALL
            SELECT 'homo_sapiens', interactor_ida, interactor_idb FROM view_interaction_full_homo_sapiens
            UNION ALL
            SELECT 'mus_musculus', interactor_ida, interactor_idb FROM view_interaction_full_mus_musculus
            UNION ALL
            SELECT 'rattus_norvegicus', interactor_ida, interactor_idb FROM view_interaction_full_rattus_norvegicus
        ) t
        GROUP BY taxon;
    ";
    $result = pg_query($dbconn, $query) or die("Error:" . pg_last_error());
    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[$row['taxon']] = [
            'A' => $row['num_patho_prots'],  // Pathogen proteins
            'B' => $row['num_host_prots']    // Host proteins
        ];
    }
    return $data;
}

function getNumberOfBactStrainsAndFamPerTaxon($dbconn) {
    $taxa = [
        'global' => 'interaction_full',
        'homo_sapiens' => 'view_interaction_full_homo_sapiens',
        'mus_musculus' => 'view_interaction_full_mus_musculus',
        'rattus_norvegicus' => 'view_interaction_full_rattus_norvegicus'
    ];

    $data = [];

    foreach ($taxa as $label => $table) {
        $query = "
            SELECT 
                COUNT(DISTINCT i.taxon_interactor_idA) AS num_strains,
                COUNT(DISTINCT u.taxon_family) AS num_families
            FROM $table i
            LEFT JOIN uniprot_taxonomy u ON i.taxon_interactor_idA = u.taxon_id
        ";
        $result = pg_query($dbconn, $query) or die("Error in joined query ($label): " . pg_last_error());
        $row = pg_fetch_assoc($result);

        $data[$label] = [
            'strains' => $row['num_strains'],
            'families' => $row['num_families']
        ];
    }

    return $data;
}


// __________________________________________________________________________________________ //
// ________________________________                          ________________________________ //
// ________________________________ DISPLAYING THE HOME PAGE ________________________________ //
// __________________________________________________________________________________________ //


// ____________________________________________
// FOR THE BUTTONS AND SEARCH DIVS (LEFT LAYER)

// display the search bar
function displaySearchBarDiv() {
    echo("<div class='search_div' id='HomeSearchBarDiv'>
            <form id='searchBarForm' method='GET'>\n
                <label class='search_bactmentha'>Search BactMentha :</label><br><br>\n
                <input type='text' placeholder='    Search  by  uniprot_ac ,  taxon_id ,  publication_id  or  uniprot_gene ...' 
                name='search_value' class='searchbar'>\n
                <input type='submit' name='searchbtn' value='Search' class='bm_button'>\n
                <input type='button' name='AdvancedSearchBtn' id='AdvancedSearchBtn' onclick='redirectToAdvancedSearch()' 
                value='Advanced Search' class='bm_button'>
                <br>
                <div class='search_bar_description'>
                    You can search by 
                    <span class='tooltip'><u>Uniprot_ac</u>
                        <span class='tooltip-text'>A <strong>uniprot_ac</strong> or uniprot accession number must be composed 
                        of 6 to 10 characters and must only contain letters and digits.</span>
                    </span> (<i>ex: P10844</i>), 

                    by <span class='tooltip'><u>Uniprot_gene</u>
                        <span class='tooltip-text'>A <strong>uniprot_gene</strong> can only contain letters, digits and 
                        underscores. This is the shorted version of the gene name, also called gene symbol.</span>
                    </span> (<i>ex: botB</i>),

                    by <span class='tooltip'><u>taxon_id</u>
                        <span class='tooltip-text'>A <strong>taxon_id</strong> or taxon identifier consists of 1 to 9 digits, 
                        inclusive.</span>
                    </span> (<i>ex: 1491</i>),

                    or by <span class='tooltip'><u>Publication_id</u>
                        <span class='tooltip-text'>A <strong>publication_id</strong> or PubMed identifier must be composed 
                        of 8 digits.</span>
                    </span> (<i>ex: 17167418</i>).
                </div>
            </form>\n
        </div>");
}

// display the help div
function displayHelpDiv() {
    echo("<div class='search_help_div' id='searchHelpDiv' style='display:none;'>
            <br>
            <label>
                A <b>uniprot_ac:</b> (or uniprot accession number) must be composed of 6 or 10 characters and must only contain letters and digits. 
                    <i>Ex: <u>P10844</u></i>.<br>\n
                A <b>uniprot_gene:</b> can contain letters, digits and underscores. This is the shorter version of the gene name, also called gene symbol. 
                    <i>Ex: <u>botB</u> for Botulinum neurotoxin. type B</i>.<br>\n
                A <b>taxon_id:</b> must consist of 1 to 9 digits, inclusive. 
                    <i>Ex: <u>1491</u> for Clostridium botulinum</i>.<br>\n
                A <b>publication_id:</b> (PubMed identifier) must be composed of 8 digits. 
                    <i>Ex: <u>17167418</u></i>.<br><br>\n
                The search is not case sensitive : you can write either <i>ABCD</i>, <i>Abcd</i> or <i>abcd</i><br>
            </label><br>
            <div class='bm_button closeHelpDivBtn' id='closeHelpDivBtn'>close help</div>
            </div>");
}


// display the help div
function displayAdvSearchHelpDiv() {
    echo("<div class='adv_search_help_div' id='searchHelpDiv'>
            <div class='adv_help_div_main_part'>
                <div class='adv_help_div_left'>
                    <div class='adv_search_titles'>
                        Advanced Search features and authorized format:
                    </div><br><br>
                    <div>
                        <table class='advSearchTable default_text'>
                            <tr><th>Format</th><th><b>letters</b></th><th><b>digits</b></th><th><b>spaces</b></th><th><b>points</b></th><th><b>underscores</b></th><th><b>length</b></th><th><b>Example</b></th></tr>
                            <tr><th>Uniprot AC</th>
                                <td>&#10004;</td><td>&#10004;</td><td></td><td></td><td></td><td>6 to 10 char</td><td><i><u>P10844</u></i></td></tr>
                            <tr><th>Uniprot Gene symbol</th>
                                <td>&#10004;</td><td>&#10004;</td><td></td><td></td><td>&#10004;</td><td></td><td><i><u>botB</u> for Botulinum neurotoxin. type B</i></td></tr>
                            <tr><th>Taxon ID</th>
                                <td></td><td>&#10004;</td><td></td><td></td><td></td><td>1 to 9 digits</td><td><i><u>1491</u> for Clostridium botulinum</i></td></tr>
                            <tr><th>Taxon Name</th>
                                <td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td></td><td></td><td><i><u>homo sapiens</u></i></td></tr>
                            <tr><th>Publication ID</th>
                                <td></td><td>&#10004;</td><td></td><td></td><td></td><td>8 digits</td><td><i><u>17167418</u></i></td></tr>
                            <tr><th>Detection Method</th>
                                <td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td></td><td>&leq; 100 char</td><td><i><u>Pull down</u></i></td></tr>
                            <tr><th>Interaction Type</th>
                                <td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td>&#10004;</td><td></td><td>&leq; 50 char</td><td><i><u>Association</u></i></td></tr>
                            <tr><th>WHO Priority Level</th>
                                <td>&#10004;</td><td></td><td></td><td></td><td></td><td>&leq; 10 char</td><td><i><u>medium</u></i></td></tr>
                            <tr><th>Hazard Group</th>
                                <td colspan='7'>Can only correspond to one of the following values: 1, 2, 3, 4, 'NULL'.</td></tr>
                        </table>
                    </div><br><br>
                    <div class='adv_search_titles'>Advanced Search Guide</div>
                    <br><br>
                    <div class='adv_search_text' style='line-height:160%'>
                        The search is <strong>not case sensitive</strong> : you can write either <i>ABCD</i>, <i>Abcd</i> or 
                        <i>abcd</i>.<br><br>
                        You can search for a <strong>Uniprot Accession number</strong>, a <strong>Uniprot gene symbol</strong>, 
                        a <strong>taxon ID</strong> or a <strong>taxon name</strong> from a bacterial or a host taxon. You can 
                        also look for a <strong>publication identifier</strong> from PubMed, a <strong>detection method</strong> 
                        or an <strong>interaction type</strong>. The <strong>WHO Priority levels</strong> and <strong>Hazard groups</strong> only concern the pathogen taxa.<br><br>
                        The advanced search feature allows you to build complex queries by combining multiple 
                        filters. Each filter consists of three components:<br>
                        <strong> • Filed Selection:</strong> Choose the type of data you want to filter, such as 
                        Uniprot AC, Taxon ID, Publication ID, or Detection Method.<br>
                        <strong> • Condition Selection:</strong> Define how the value should be compared, using options 
                        like 'is equal to', 'contains', or 'does not contain'.<br>
                        <strong> • Value Input:</strong> Enter the search term(s) based on your chosen field and condition.<br>
                        <br>
                        Conditions and input rules:<br>
                        <strong> • \"is equal to\"</strong> and <strong>\"is different from\"</strong> accept only <strong>one value</strong>.
                        The results should match exactly the value.<br>
                        <strong> • \"contains\"</strong> and <strong>\"does not contain\"</strong> accept <strong>multiple values</strong>, 
                        separated by a comma. These can be partial values (for exemple: <i>'sapiens'</i> instead of <i>'Homo sapiens'</i> 
                        or <i>'mass spec'</i> instead of <i>'mass spectrometry studies of complexes'</i>.<br>
                        <br>
                        Combining multiple filters: you can add <strong>multiple filters</strong> by clicking <i>\"Add condition\"</i>.
                        Filters can be combined using <strong>AND</strong> or <strong>OR</strong> to refine your query:<br>
                        <strong> • AND:</strong> Results must match <strong>all</strong> conditions. (Stricter search)<br>
                        <strong> • OR:</strong> Results must match <strong>at least one</strong> condition. (Broader search)
                        <br><br>
                        <strong><u>NB</u>: Any OR clause is associated to the last AND clause !</strong><br>
                            For example if you set the following filters:<br>
                                > condition1 <strong>AND</strong> condition2 <strong>OR</strong> condition3 <strong>OR</strong> 
                                condition4 <strong>AND</strong> condition5 <strong>OR</strong> condition6<br>
                            The resulting condition query would be:<br>
                                > condition1 <strong>AND (<u></strong>condition2 <strong>OR</strong> condition3 <strong>OR</strong> 
                                condition4<strong></u>) AND (<u></strong>condition5 <strong>OR</strong> condition6<strong></u>)</strong>
                    </div>
                </div> <!-- end of the left div -->

                <div class='adv_help_div_right'><label class='default_text'>
                    <br><div class='adv_search_titles' style='text-align:center;'>Examples of use:</div><br><br>
                    <div class='adv_search_text'>

                        <strong> • <u>Example 1</u>: Simple query with two filters</strong><br><br>
                            <i>\"I’m studying human proteins and want to find interactions involving the bacterium 
                            Escherichia coli (Taxon ID: 562) that interact with human proteins.\"</i>
                            <br><br>
                            Filters used:<br>
                                 > (host) Taxon Name | is equal to | <u><i>Homo sapiens</i></u><br>
                                 > <strong>AND</strong> (bacterial) Taxon ID | is equal to | <u><i>562</i></u>
                            <br><br>

                        <div class='contact_title_bar'></div><br><br>

                        <strong> • <u>Example 2</u>: More complex query using 'contains' condition and AND clauses</strong><br><br>
                            <i>\"I’m studying the influence of Francisella tularensis subsp. tularensis (strain SCHU S4 / Schu 4) 
                            (ID: 177416) proteins on the Human (Homo sapiens) JUN protein (Gene symbol: JUN) interactions. 
                            My work model is the Mouse (Mus musculus). I would like to extract ony the interactions involving
                            Mouse or Human JUN proteins against proteins of my Francisella tularensis strain.\"</i>
                            <br><br>
                            Filters used:<br>
                                 > (host) Taxon Name | contains | <u><i>Homo , Mus</i></u><br>
                                 > <strong>AND</strong> (bacterial) Taxon ID | is equal to | <u><i>177416</i></u><br>
                                 > <strong>AND</strong> (host) Uniprot Gene Symbol | is equal to | <u><i>jun</i></u><br>
                            <i>Nb: No need to use the complete host names here with CONTAINS as BactMentha doesn't contain 
                            other Homo or Mus species !</i>
                            <br><br>

                        <div class='contact_title_bar'></div><br><br>

                        <strong> • <u>Example 3</u>: Complex Query Combining AND & OR Clauses with WHO Priority Group</strong><br><br>
                            <i>\"My research team and I have identified numerous human-bacteria protein-protein interactions 
                            using two-hybrid approaches. Now, we want to verify if any of these interactions have been 
                            independently confirmed in the BactMentha database but detected using other experimental methods. 
                            Since this could generate a large number of results, we plan to narrow our focus to interactions 
                            involving bacteria classified as a priority by the World Health Organization (WHO). Specifically, 
                            we want to include pathogens that are assigned to any WHO Priority Level or belong to a defined 
                            Hazard Group, as these are of particular concern for global health.\"</i>
                            <br><br>
                            Filters used:<br>
                                 > (host) Taxon ID | is equal to | <u><i>9606</i></u><br>
                                 > <strong>AND</strong> Detection Method | does not contain | <u><i>two hybrid</i></u><br>
                                 > <strong>AND</strong> Pathogen WHO Priority Level | is different from | <u><i>NULL</i></u><br>
                                 > <strong>OR</strong> Pathogen Hazard Group | is different from | <u><i>NULL</i></u><br> 
                            <br><br>
                    </div>
                </div>
            </div>");
}

// display the home page buttons if no search or advanced search yet
function displayButtonsDiv() {
    echo("<div class='home_buttons_space'>\n
            <div class='home_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=homo_sapiens' 
                    title='Human (Homo sapiens)'>\n
                    <img src='/static/img/buttons/manwoman_v2.png' class='home_button_png'>
                </a>
            </div>\n
            <div class='home_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=mus_musculus' 
                    title='Mouse (Mus musculus)'>\n
                    <img src='/static/img/buttons/mouse.png' class='home_button_png'>
                </a>
            </div>\n
            <div class='home_button'>
                <a href='/Browse?data_view_type=interaction_data&data_taxon=rattus_norvegicus' 
                    title='Rat (Rattus norvegicus)'>\n
                    <img src='/static/img/buttons/rat.png' class='home_button_png'>
                </a>
            </div>\n<br>
            <div class='home_button'>
                <a href='/Browse'>\n
                    Browse Database
                </a>
            </div>\n
            <div class='home_button'>
                <a href='/Documentation'>\n
                    Documentation
                </a>
            </div>\n
            <div class='home_button'>
                <a href='/Download'>\n
                    Download
                </a>
            </div>\n
        </div>");
}

// display the advanced search form
function displayAdvSearchForm() {
    echo("<form>
            <div class='advanced_search_container'>
                <div class='filter_container'>
                    <div class='filter_content_container'>
                        <select class='filter_element selectType' name='adv_search_type'>
                            <!-- Options for the first select -->
                            <option value='UP_A.uniprot_ac'>(bacterial) Uniprot AC</option>
                            <option value='UP_A.uniprot_gene'>(bacterial) Uniprot Gene symbol</option>
                            <option value='UT_A.taxon_id'>(bacterial) Taxon ID</option>
                            <option value='UT_A.taxon_name'>(bacterial) Taxon Name</option>
                            <option value='UP_B.uniprot_ac'>(host) Uniprot AC</option>
                            <option value='UP_B.uniprot_gene'>(host) Uniprot Gene symbol</option>
                            <option value='UT_B.taxon_id'>(host) Taxon ID</option>
                            <option value='UT_B.taxon_name'>(host) Taxon Name</option>
                            <option value='IF.publication_id'>Publication ID (PubMed ID)</option>
                            <option value='IF.detection_method'>Detection Method</option>
                            <option value='IF.interaction_type'>Interaction Type</option>
                            <option value='UT_A.who_priority'>Pathogen WHO Priority Level</option>
                            <option value='UT_A.hazard_group'>Pathogen Hazard Group</option>
                        </select>
                        <select class='filter_element selectCondition' name='adv_search_condition'>
                            <option value=' = '> is equal to </option>
                            <option value=' != '> is different from </option>
                            <option value=' like[] '> contains </option>
                            <option value=' not like[] '> does not contain </option>
                        </select>
                        <input class='filter_element adv_search_value' name='adv_search_value' type='text' style='flex: 1;' placeholder='e. g. : uniprot_ac  |  =  |  P0ABE7'>
                    </div>
                    <!-- Bin Button -->
                    <!-- <div class='filter_element bin_button' onclick='removeFilterContainer(this)'><img src='/static/img/buttons/trash-icon.png'></div> -->
                </div>
            </div>
            <div class='advanced_search_button_container'>
                <button class='bm_button' type='button' name='ResestAdvancedSearchBtn' onclick='resetAdvancedSearch()'>Reset</button>
                <button class='bm_button' type='button' name='AdvancedSearchApply' onclick='getAllFilterValues()'>Search</button>
                <button class='bm_button' type='button' onclick='addFilterContainer()' title='add a filter'>Add condition</button>
            </div>
        </form>");
}

// Display the presentation text with the advanced search
function displayAdvSearchPresentation() {
    echo("<div id='advSearchDescription'>
            <h2 class='home_news_title'>Welcome on Advanced Search</h2>
            <div class='contact_title_bar'></div>
            <br><br>
        </div>");
}

// retrieve the text to display depending on the given values
function getFormatToDisplay($value) {
    $attributeMappings = array(
        'UP_A.uniprot_ac' => '(bacterial) Uniprot AC',
        'UP_A.uniprot_gene' => '(bacterial) Uniprot Gene symbol',
        'UT_A.taxon_id' => '(bacterial) Taxon ID',
        'UT_A.taxon_name' => '(bacterial) Taxon Name',
        'UP_B.uniprot_ac' => '(host) Uniprot AC',
        'UP_B.uniprot_gene' => '(host) Uniprot Gene symbol',
        'UT_B.taxon_id' => '(host) Taxon ID',
        'UT_B.taxon_name' => '(host) Taxon Name',
        'IF.publication_id' => 'Publication ID (PubMed ID)',
        'IF.detection_method' => 'Detection Method',
        'IF.mi_score' => 'MI score',
        'IF.interaction_type' => 'Interaction Type',
        'UT_A.who_priority' => 'Pathogen WHO Priority Level',
        'UT_A.hazard_group' => 'Pathogen Hazard Group'
    );
    return $attributeMappings[$value];
}


// __________________________________________________________________________________________ //
// _______________________________                            _______________________________ //
// _______________________________ TABLES ANNOTATIONS DISPLAY _______________________________ //
// __________________________________________________________________________________________ //

// Get the index of the bm_id (HIDDEN) and annotations (DISPLAY BUTTONS) columns depending on the view name
function getbmIdAndAnnotIndex($viewType) {
    /**
     * Retrieve the index of the last colonne to display the annotation buttons
     * depending on the table name.
     * 
     * Args:
     * - viewType: type of view selected by the user
     * 
     * Return:
     * - annotationIndex : index is number of columns in the view +1
     * - bmIdIndex : index of the bmId to use as an argument for the other functions.
     */
    if ($viewType == "interaction_data") {
        $annotationIndex = 12;
        $bmIdIndex = 11;
    }
    $result = [$annotationIndex, $bmIdIndex];
    return $result;
}

// Check if need to call the mimicint_interface table in the child row
function IsItInMimicintInterface($bmId) {
    /**
     * Find if the line interaction is found in mimicint interface by checking
     * if the bmId is found in the mimicint xref table.
     * 
     * Args:
     * - bmId: first element of the table row to compare with mimicint_xref
     * 
     * Return:
     * - 'f' (not found in mimicint xref) or 't' (found in mimicint xref)
     */
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());             // starting postgres connection
    $psql_query = 'SELECT exists (SELECT * FROM mimicint_xref WHERE mnt_interaction_id = \''.$bmId.'\' LIMIT 1)';
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];                                           // t (if in mimicint tables) or f.
    $close=pg_close($dbconn);                                                           // Closing postgresql connection
    return $result ;
}

// Check if need to call the interaction_feature table in the child row
function IsItInInteractionFeature($bmId) {
    /**
     * Find if the line interaction is found ininteraction_feature by checking
     * if the bmId is found in the interaction_feature table.
     * 
     * Args:
     * - bmId: first element of the table row to compare with interaction_feature
     * 
     * Return:
     * - 'f' (not found in interaction_feature) or 't' (found in interaction_feature)
     */
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());             // starting postgres connection
    $psql_query = 'SELECT exists (SELECT * FROM interaction_feature WHERE mnt_interaction_id = \''.$bmId.'\' LIMIT 1)';
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];                                           
    $close=pg_close($dbconn);                                                           // Closing postgresql connection
    return $result ;
}

// Check if need to call the protein_annotation table in the child row
function IsItInProteinAnnotations($bmId) {
    /**
     * Find if the bacterial interactor (interactor_idb) is annotated in the table protein_annotation.
     * 
     * Args:
     * - bmId: first element of the table row to use in the select to find the interactors
     * 
     * Return: (one of the following possibilities)
     * - ff (none of the interactors are found in Portein annotation)
     * - tf (only interactor_idA is found in protein_interaction)
     * - ft (only interactor_idB is found in protein_interaction)
     * - tt (both interactors are found in protein_interaction)
     */
    // For interactor_idb
    $psql_query = 'SELECT exists (SELECT * FROM protein_annotation AS PA 
    INNER JOIN uniprot_protein AS UP ON PA.uniprot_ac = UP.uniprot_ac 
    INNER JOIN uniprot_xref AS UX ON UP.uniprot_ac = UX.uniprot_ac 
    INNER JOIN interaction_full AS IF ON UX.interactor_id = IF.interactor_ida
    WHERE IF.mnt_interaction_id = \''.$bmId.'\' LIMIT 1)';

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
    or die('Unable to connect to the database : ' . pg_last_error());             // starting postgres connection
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];
    $close=pg_close($dbconn);                                                           // Closing postgresql connection
    // returns 'f' (not found) or 't' (found)
    return $result ;
}

// Check if need to call the cellular_components table in the child row
function IsItInCellularComponents($bmId) {
    /**
     * Find if the bacterial interactor (interactor_idb) is annotated in the table protein_annotation.
     * 
     * Args:
     * - bmId: first element of the table row to use in the select to find the interactors
     */
    // For interactor_idb
    $psql_query = 'SELECT exists (SELECT CC.uniprot_ac AS uniprot_ac, 
        CC.cc_value AS cellular_component_value, 
        CC.cc_id AS cellular_component_id, 
        CC.topology_value AS topology_value, 
        CC.topology_id AS topology_id, 
        CC.annotation_source AS annotation_source 
    FROM interaction_full AS IF 
    INNER JOIN cellular_components AS CC
        ON CC.uniprot_ac = IF.interactor_idA
        OR CC.uniprot_ac = IF.interactor_idB
    WHERE IF.mnt_interaction_id = \''.$bmId.'\' LIMIT 1)';
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
    or die('Unable to connect to the database : ' . pg_last_error());             // starting postgres connection
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];
    $close=pg_close($dbconn);                                                           // Closing postgresql connection
    // returns 'f' (not found) or 't' (found)
    return $result ;
}

// Check if need to call the af3_predictions table in the child row
function IsItInAlfphafoldComplexes($bmId) {
    /**
     * Find if the bacterial interactor (interactor_idb) is annotated in the table protein_annotation.
     * 
     * Args:
     * - bmId: first element of the table row to use in the select to find the interactors
     */
    // For interactor_idb
    $psql_query = 'SELECT exists (SELECT * FROM af3_predictions AS AF 
    WHERE AF.mnt_interaction_id = \''.$bmId.'\' LIMIT 1)';
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
    or die('Unable to connect to the database : ' . pg_last_error());             // starting postgres connection
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];
    $close=pg_close($dbconn);                                                           // Closing postgresql connection
    // returns 'f' (not found) or 't' (found)
    return $result ;
}


// __________________________________________________________________________________________ //
// _______________________________                           ________________________________ //
// _______________________________ CHILD ROWS TABLES DISPLAY ________________________________ //
// __________________________________________________________________________________________ //


// creates mimicint interface child table
function writeMimicintChildTable($queryResult, $tableClass) {
    /**
     * This function is used to create simple html tables using the result of the psql
     * queries sent when the user click on one of the three child row buttons.
     * Ths resulting table will be displayed in the child row.
     * 
     * Args:
     * - queryResult: an array of arrays containing all the table data
     * 
     * Return:
     * - childRowTable: the full html table into a string variable
     */
    $child_row_table = "<table class='${tableClass}'>\n<thead><tr>";        // beginning of the table

    // HEADER
    $col_number = pg_num_fields($queryResult);                              // number of columns
    $dispColList = [];                                                      // list of columns to display
    for ($i = 0; $i < $col_number; $i++) {                                  // iterate through the column names
        $col_name = pg_field_name($queryResult, $i);                          // takes the new column name
        $disp_colname = preg_replace('/_+/', ' ', $col_name);                 // replace underscores by spaces in headers names
        $lastLetter = strlen($disp_colname) -1;
        $disp_colname = ucfirst(substr($disp_colname, 0, $lastLetter)) . strtoupper(substr($disp_colname, $lastLetter, 1));
        $child_row_table .= "\n<th>${disp_colname}</th>" ;    // display the col name in a new cell on same line
        $dispColList[] = $col_name ;                                          // add the column to display to be used as key
      }
    $child_row_table = $child_row_table."</tr></thead><tbody>"; 
    // DATA
    $row_index = 0 ;                                                        // to put a specific index in rows
    while($row_elem = pg_fetch_row($queryResult)) {                         // for all the rows in the resulting table
      $child_row_table = $child_row_table."\n<tr>";                         // creates a new line in the html table
      $count_cells = count($row_elem);                                      // number of elements in the row
      for ($j = 0; $j < $count_cells; $j++) {                               // for all the elements in the line
        $current_cell = current($row_elem);                                 // current line cell element
        if ($j == 1 || $j == 5) {
            if (substr($current_cell, 0, 3) == "IPR") {
                $child_row_table .= "<td><a class='table_link' href='https://www.ebi.ac.uk/interpro/entry/InterPro/".$current_cell."/' 
                                    target='_blank' title='see on InterPro'>".$current_cell."</a></td>";
            } else {
                $child_row_table .= "<td><a class='table_link' href='http://elm.eu.org/elms/".$current_cell."' 
                                    target='_blank' title='see on ELM'>".$current_cell."</a></td>";
            }
        } else {
            $child_row_table = $child_row_table."<td>".$current_cell."</td>";
        }
        next($row_elem);                                                    // taking the next element of the row
      }
      $row_index++ ;                                                        // next row index
      $child_row_table = $child_row_table."</tr>";                          // last closing line
    }
    $child_row_table = $child_row_table."</tbody></table>";                 // end of the table
                    
    return $child_row_table; 
}

// creates interaction_feature child table
function writeFeaturesChildTable($queryResult, $tableClass) {
    /**
     * This function is used to create simple html tables using the result of the psql
     * queries sent when the user click on one of the three child row buttons.
     * Ths resulting table will be displayed in the child row.
     * 
     * Args:
     * - queryResult: an array of arrays containing all the table data
     * 
     * Return:
     * - childRowTable: the full html table into a string variable
     */
    $child_row_table = "<table class='${tableClass}'>\n<thead><tr>";        // beginning of the table
    // HEADER
    $col_number = pg_num_fields($queryResult);                              // number of columns
    $dispColList = [];                                                      // list of columns to display
    for ($i = 0; $i < $col_number; $i++) {                                  // iterate through the column names
      $col_name = pg_field_name($queryResult, $i);                          // takes the new column name
      $disp_colname = preg_replace('/_+/', ' ', $col_name);                 // replace underscores by spaces in headers names
      $lastLetter = strlen($disp_colname) -1;
      $disp_colname = ucfirst(substr($disp_colname, 0, $lastLetter)) . strtoupper(substr($disp_colname, $lastLetter, 1));
      $child_row_table .= "\n<th>${disp_colname}</th>" ;    // display the col name in a new cell on same line
      $dispColList[] = $col_name ;                                          // add the column to display to be used as key
    }
    $child_row_table = $child_row_table."</tr></thead><tbody>"; 
    // DATA
    $row_index = 0 ;                                                        // to put a specific index in rows
    while($row_elem = pg_fetch_row($queryResult)) {                         // for all the rows in the resulting table
      $child_row_table = $child_row_table."\n<tr>";                         // creates a new line in the html table
      $count_cells = count($row_elem);                                      // number of elements in the row
      for ($j = 0; $j < $count_cells; $j++) {                               // for all the elements in the line
        $current_cell = current($row_elem);                                 // current line cell element
        $child_row_table = $child_row_table."<td>".$current_cell."</td>";   // display the current element in a new cell
        next($row_elem);                                                    // taking the next element of the row
      }
      $row_index++ ;                                                        // next row index
      $child_row_table = $child_row_table."</tr>";                          // last closing line
    }
    $child_row_table = $child_row_table."</tbody></table>";                 // end of the table
                    
    return $child_row_table; 
}

// creates cellular components child table
function wirteCellCompChildTable($queryResult, $tableClass) {
    /**
     * This function is used to create simple html tables using the result of the psql
     * queries sent when the user click on one of the three child row buttons.
     * Ths resulting table will be displayed in the child row.
     * 
     * Args:
     * - queryResult: an array of arrays containing all the table data
     * 
     * Return:
     * - childRowTable: the full html table into a string variable
     */
    $child_row_table = "<table class='${tableClass}'>\n<thead><tr>";        // beginning of the table
    // HEADER
    $col_number = pg_num_fields($queryResult);                              // number of columns
    $dispColList = [];                                                      // list of columns to display
    for ($i = 0; $i < $col_number; $i++) {
        $col_name = pg_field_name($queryResult, $i);
        $disp_colname = preg_replace('/_+/', ' ', $col_name); // replace underscores by spaces in headers names and capitalize first letter
        $child_row_table .= "\n<th>${disp_colname}</th>";
        $dispColList[] = $col_name;
    }
    $child_row_table = $child_row_table."</tr></thead><tbody>"; 
    // DATA
    $row_index = 0 ;                                                        // to put a specific index in rows
    while($row_elem = pg_fetch_row($queryResult)) {                         // for all the rows in the resulting table
      $child_row_table = $child_row_table."\n<tr>";                         // creates a new line in the html table
      $count_cells = count($row_elem);                                      // number of elements in the row
      for ($j = 0; $j < $count_cells; $j++) {                               // for all the elements in the line
        $current_cell = current($row_elem);                                 // current line cell element
        $child_row_table = $child_row_table."<td>".$current_cell."</td>";   // display the current element in a new cell
        next($row_elem);                                                    // taking the next element of the row
      }
      $row_index++ ;                                                        // next row index
      $child_row_table = $child_row_table."</tr>";                          // last closing line
    }
    $child_row_table = $child_row_table."</tbody></table>";                 // end of the table         
    return $child_row_table; 
}

// creates cellular components child table
function writeAlphaFoldChildTable($queryResult, $tableClass) {
    /**
     * This function is used to create simple html tables using the result of the psql
     * queries sent when the user click on one of the three child row buttons.
     * Ths resulting table will be displayed in the child row.
     * 
     * Args:
     * - queryResult: an array of arrays containing all the table data
     * 
     * Return:
     * - childRowTable: the full html table into a string variable
     */
    $child_row_table = "<table class='${tableClass}'>\n<thead><tr>";        // beginning of the table
    // HEADER
    $col_number = pg_num_fields($queryResult);                              // number of columns
    $dispColList = [];                                                      // list of columns to display
    for ($i = 0; $i < $col_number; $i++) {
        $col_name = pg_field_name($queryResult, $i);
        $disp_colname = preg_replace('/_+/', ' ', $col_name); // replace underscores by spaces in headers names and capitalize first letter
        if (in_array($i, [0, 1, 2, 3, 11, 12])) {
            $disp_colname = substr($disp_colname, 0, -1) . strtoupper(substr($disp_colname, -1));
        }
        $child_row_table .= "\n<th>${disp_colname}</th>";
        $dispColList[] = $col_name;
    }
    $child_row_table = $child_row_table."</tr></thead><tbody>"; 
    // DATA
    $row_index = 0 ;                                                        // to put a specific index in rows
    while($row_elem = pg_fetch_row($queryResult)) {                         // for all the rows in the resulting table
      $child_row_table = $child_row_table."\n<tr>";                         // creates a new line in the html table
      $count_cells = count($row_elem);                                      // number of elements in the row
      for ($j = 0; $j < $count_cells; $j++) {                               // for all the elements in the line
        $current_cell = current($row_elem);                                 // current line cell element
        if (in_array($j, [1, 3])) {
            $current_cell = number_format((float) $current_cell, 2, '.', '');
        }
        $child_row_table = $child_row_table."<td>".$current_cell."</td>";   // display the current element in a new cell
        if ($row_index == 0 & $j == 0) { // first row first column
            $idA = $current_cell;
        }
        if ($row_index == 0 & $j == 2) { // first ro third column
            $idB = $current_cell;
        }
        next($row_elem);                                                    // taking the next element of the row
      }
      $row_index++ ;                                                        // next row index
      $child_row_table = $child_row_table."</tr>";                          // last closing line
    }
    // add a download link for the complexes at the end of the table:
    $filename = $idA."_in_complex_with_".$idB.".zip";
    $download_link = "<tr><td class='first_link' colspan='13'><a class='table_link' href='/static/complexes/".$filename."' download='".$filename."'>".basename($filename)."</a></td>";
    $child_row_table_with_link = $child_row_table.$download_link."</tbody></table>";                 // end of the table         
    return $child_row_table_with_link; 
}

// creates protein annotation child table
function writeProtAnnotChildTable($queryResult, $tableClass) {
    /**
     * This function is used to create simple html tables using the result of the psql
     * queries sent when the user click on one of the three child row buttons.
     * Ths resulting table will be displayed in the child row.
     * 
     * Args:
     * - queryResult: an array of arrays containing all the table data
     * 
     * Return:
     * - childRowTable: the full html table into a string variable
     */
    $child_row_table = "<table class='${tableClass}'>\n<thead><tr>";        // beginning of the table
    // HEADER
    $col_number = pg_num_fields($queryResult);                              // number of columns
    $dispColList = [];                                                      // list of columns to display
    // for ($i = 0; $i < $col_number; $i++) {                                  // iterate through the column names
    //   $col_name = pg_field_name($queryResult, $i);                          // takes the new column name
    //   $disp_colname = preg_replace('/_+/', ' ', $col_name);                 // replace underscores by spaces in headers names
    //   $child_row_table = $child_row_table."\n<th>${disp_colname}</th>" ;    // display the col name in a new cell on same line
    //   $dispColList[] = $col_name ;                                          // add the column to display to be used as key
    // }
    for ($i = 0; $i < $col_number; $i++) {
        $col_name = pg_field_name($queryResult, $i);
        $disp_colname = preg_replace('/_+/', ' ', $col_name); // replace underscores by spaces in headers names and capitalize first letter
        // Capitalize first letter (and last one for first column)
        if ($i == 0) {
            $lastLetter = strlen($disp_colname) -1;
            $disp_colname = ucfirst(substr($disp_colname, 0, $lastLetter)) . strtoupper(substr($disp_colname, $lastLetter, 1));
        }
        $child_row_table .= "\n<th>${disp_colname}</th>";
        $dispColList[] = $col_name;
    }
    $child_row_table = $child_row_table."</tr></thead><tbody>"; 
    // DATA
    $row_index = 0 ;                                                        // to put a specific index in rows
    while($row_elem = pg_fetch_row($queryResult)) {                         // for all the rows in the resulting table
      $child_row_table = $child_row_table."\n<tr>";                         // creates a new line in the html table
      $count_cells = count($row_elem);                                      // number of elements in the row
      for ($j = 0; $j < $count_cells; $j++) {                               // for all the elements in the line
        $current_cell = current($row_elem);                                 // current line cell element
        $child_row_table = $child_row_table."<td>".$current_cell."</td>";   // display the current element in a new cell
        next($row_elem);                                                    // taking the next element of the row
      }
      $row_index++ ;                                                        // next row index
      $child_row_table = $child_row_table."</tr>";                          // last closing line
    }
    $child_row_table = $child_row_table."</tbody></table>";                 // end of the table         
    return $child_row_table; 
}

// Call the writeChildTable to display the mimicint_interface child table when the blue button is clicked
function dispMimicintChildRow($bmId) {
    /**
     * Creates a child table in html to display in the child row depending on the row informations.
     * 
     * Args:
     * - $bmId: bmId to search in mimicint xref table for the inner join with mimicint interface
     * 
     * Return:
     * -childTable: the html table to display in the child row.
     */
    // Searching for infos in mimicint interface
    $psql_query = "SELECT MI.interface_interactor_ida AS identifier_A, --MI.mimicint_interaction_id not selected anymore
        MI.interface_type_ida AS interface_type_A,
        CAST(SPLIT_PART(MI.interface_start_interactor_ida, '.', 1) AS INTEGER) AS interface_start_A, 
        CAST(SPLIT_PART(MI.interface_end_interactor_ida, '.', 1) AS INTEGER) AS interface_end_A, 
        MI.interface_interactor_idb AS identifier_B, 
        MI.interface_type_idb AS interface_type_B,
        CAST(SPLIT_PART(MI.interface_start_interactor_idb, '.', 1) AS INTEGER) AS interface_start_B, 
        CAST(SPLIT_PART(MI.interface_end_interactor_idb, '.', 1) AS INTEGER) AS interface_end_B 
    FROM mimicint_interface AS MI 
    INNER JOIN mimicint_xref AS MX ON MI.mimicint_interaction_id = MX.mimicint_interaction_id 
    WHERE MX.mnt_interaction_id = '${bmId}' ";

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')      // starting postgres connection
    or die('Unable to connect to the database : ' . pg_last_error());                                   // or send an error message
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());  // query execution
    $close=pg_close($dbconn);                                                                           // closing postgresql connection

    $childRowTable = writeMimicintChildTable($query_result, 'display stripe child_table_MI');                      // child table creation

    return $childRowTable ;                                                                             // return the html table in a string
}

// Call the writeChildTable to display the interaction_feature child table when the green button is clicked
function dispInteractionFeatureChildRow($bmId) {
    /**
     * Creates a child table in html to display in the child row depending on the row informations.
     * 
     * Args:
     * - $bmId: bmId to search in interaction_feature
     * 
     * Return:
     * -childTable: the html table to display in the child row.
     */
    // Searching for infos in mimicint interface
    $psql_query = "SELECT IFT.interactor_idA AS identifier_A, 
        IFT.feature_interactor_idA AS feature_A, 
        IFT.feature_start_interactor_idA AS feature_start_A, 
        IFT.feature_end_interactor_idA AS feature_end_A,
        IFT.interactor_idB AS identifier_B, 
        IFT.feature_interactor_idB AS feature_B, 
        IFT.feature_start_interactor_idB AS feature_start_B, 
        IFT.feature_end_interactor_idB AS feature_end_B
    FROM interaction_feature AS IFT
    WHERE IFT.mnt_interaction_id = '${bmId}' 
    ORDER BY
    CASE 
        WHEN IFT.feature_start_interactor_idA = '-' THEN 9999
        ELSE CAST(SPLIT_PART(SPLIT_PART(IFT.feature_start_interactor_idA, ',', 1), '..', 1) AS INTEGER)
    END,
    CASE 
        WHEN IFT.feature_end_interactor_idA = '-' THEN 9999
        ELSE CAST(SPLIT_PART(SPLIT_PART(IFT.feature_end_interactor_idA, ',', 1), '..', 1) AS INTEGER)
    END,
    CASE 
        WHEN IFT.feature_start_interactor_idB = '-' THEN 9999
        ELSE CAST(SPLIT_PART(SPLIT_PART(IFT.feature_start_interactor_idB, ',', 1), '..', 1) AS INTEGER)
    END,
    CASE 
        WHEN IFT.feature_end_interactor_idB = '-' THEN 9999
        ELSE CAST(SPLIT_PART(SPLIT_PART(IFT.feature_end_interactor_idB, ',', 1), '..', 1) AS INTEGER)
    END;";

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')      // starting postgres connection
    or die('Unable to connect to the database : ' . pg_last_error());                                   // or send an error message
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());  // query execution
    $close=pg_close($dbconn);                                                                           // closing postgresql connection
    $childRowTable = writeFeaturesChildTable($query_result, 'display stripe child_table_BR');                      // child table creation
    return $childRowTable ;                                                                             // return the html table in a string
}

// Call the writeChildTable to display the protein_annotation child table when the yellow button is clicked
function dispProtAnnotationChildRow($bmId) {
    /**
     * Creates a child table in html to display in the child row depending on the row informations.
     * 
     * Args:
     * - $bmId: bmId to search in interaction_feature
     * 
     * Return:
     * -childTable: the html table to display in the child row.
     */
    $psql_query = "SELECT IF.interactor_idA AS Identifier_A, 
        UP.uniprot_gene AS Gene_symbol, 
        UP.uniprot_name AS Gene_name, 
        PA.annotated_sequence AS Annotated_seq, 
        PA.annotation_source AS Annotation_source, 
        PA.annotation_id AS Annotation_id, 
        A.annotation_description AS Annotation_description, 
        PA.inference_method AS Inference_method, 
        ROUND(CAST(PA.sequence_identity AS numeric), 1) AS Sequence_identity, 
        PA.bit_score AS Bit_score, 
        ROUND(CAST(PA.alignment_coverage AS numeric), 1) AS Alignment_coverage 
    FROM protein_annotation AS PA 
    INNER JOIN annotation AS A ON A.annotation_id = PA.annotation_id 
    INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = PA.uniprot_ac 
    INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP.uniprot_ac 
    INNER JOIN interaction_full AS IF ON IF.interactor_idA = UX.interactor_id 
    WHERE IF.mnt_interaction_id = '${bmId}' 
    ORDER BY Sequence_identity DESC;";

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')      // starting postgres connection
    or die('Unable to connect to the database : ' . pg_last_error());                                   // or send an error message
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());  // query execution
    $close=pg_close($dbconn);                                                                           // closing postgresql connection
    $childRowTable = writeProtAnnotChildTable($query_result, 'display stripe child_table_PA');                    // child table creation
    return $childRowTable ;
}

// Call the writeChildTable to display the protein_annotation child table when the yellow button is clicked
function dispCellCompChildRow($bmId) {
    /**
     * Creates a child table in html to display in the child row depending on the row informations.
     * 
     * Args:
     * - $bmId: bmId to search in interaction_feature
     * 
     * Return:
     * -childTable: the html table to display in the child row.
     */
    $psql_query = "SELECT CC.uniprot_ac AS uniprot_ac, 
        CC.cc_value AS cellular_component_value, 
        CC.cc_id AS cellular_component_id, 
        CC.topology_value AS topology_value, 
        CC.topology_id AS topology_id, 
        CC.annotation_source AS annotation_source 
    FROM interaction_full AS IF 
    INNER JOIN cellular_components AS CC
        ON CC.uniprot_ac = IF.interactor_idA
        OR CC.uniprot_ac = IF.interactor_idB
    WHERE IF.mnt_interaction_id = '${bmId}' 
    ORDER BY CC.uniprot_ac;";
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')      // starting postgres connection
    or die('Unable to connect to the database : ' . pg_last_error());                                   // or send an error message
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());  // query execution
    $close=pg_close($dbconn);                                                                           // closing postgresql connection
    $childRowTable = wirteCellCompChildTable($query_result, 'display stripe child_table_CC');                    // child table creation
    return $childRowTable ;
}

// Call the writeChildTable to display the protein_annotation child table when the yellow button is clicked
function dispAlphaFoldChildRow($bmId) {
    /**
     * Creates a child table in html to display in the child row depending on the row informations.
     * 
     * Args:
     * - $bmId: bmId to search in interaction_feature
     * 
     * Return:
     * -childTable: the html table to display in the child row.
     */
    $psql_query = "SELECT AF.interactor_idA AS Identifier_A, 
        AF.avg_pLDDT_idA AS avg_pLDDT_A, 
        AF.interactor_idB AS Identifier_B, 
        AF.avg_pLDDT_idB AS avg_pLDDT_B, 
        AF.model AS model, 
        ROUND(CAST(AF.ptm AS numeric), 2) AS ptm, 
        ROUND(CAST(AF.iptm AS numeric), 2) AS iptm, 
        ROUND(CAST(AF.pDockQ2_score AS numeric), 3) AS pDockQ2_score, 
        AF.pDockQ2_range AS pDockQ2_range, 
        AF.potential_clashes AS potential_clashes, 
        AF.residue_contacts AS residue_contacts, 
        AF.contacts_on_A AS contacts_on_A, 
        AF.contacts_on_B AS contacts_on_B
    FROM af3_predictions AS AF 
    WHERE AF.mnt_interaction_id = '${bmId}' 
    ORDER BY model;";

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')      // starting postgres connection
    or die('Unable to connect to the database : ' . pg_last_error());                                   // or send an error message
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());  // query execution
    $close=pg_close($dbconn);                                                                           // closing postgresql connection
    $childRowTable = writeAlphaFoldChildTable($query_result, 'display stripe child_table_AF');                    // child table creation
    return $childRowTable ;
}


// Call the function corresponding to the button clicked to display the child row using the bm_id of the line
if(isset($_GET['action']) && !empty($_GET['action'])) {
    $called_function = $_GET['action'];
    switch($called_function) {
        case 'dispMimicintChildRow' : echo dispMimicintChildRow($_GET['bmId']); break;
        case 'dispInteractionFeatureChildRow' : echo dispInteractionFeatureChildRow($_GET['bmId']); break;
        case 'dispProtAnnotationChildRow' : echo dispProtAnnotationChildRow($_GET['bmId']); break;
        case 'dispCellCompChildRow' : echo dispCellCompChildRow($_GET['bmId']); break;
        case 'dispAlphaFoldChildRow' : echo dispAlphaFoldChildRow($_GET['bmId']); break;
    }
}

// __________________________________________________________________________________________ //
// _______________________________                            _______________________________ //
// _______________________________ BASIC SEARCH VERIFICATIONS _______________________________ //
// __________________________________________________________________________________________ //

// Returns the types / formats that match the value
function whatIsInSearchValue($searchValue) {
    /**
     * Check the format of the searchValue to find if it corresponds to an authorized one.
     * 
     * Args:
     * - serachValue: a string that must correspond to a certain format.
     * 
     * Return:
     * - result: list that contain at least one of those elements :
     *          ['wrong_format', 'uniprot_ac', 'uniprot_gene', 'taxon_id',
     *          'publication_id']
     */
    // allowed regex expression
    $publication_id = "/^[0-9]{6,8}$/";                     // 8 digits
    $taxon_id = "/^[0-9]{1,9}$/";                           // 1 to 9 digits
    //$uniprot_ac = "/^[A-Z\d]{6}(?:[A-Z\d]{4})?$/";          
    $uniprot_ac = "/^([OPQ][0-9][A-Z0-9]{3}[0-9]|[A-NR-Z][0-9]([A-Z][A-Z0-9]{2}[0-9]){1,2})$/"; // uniprot_ac regex given by uniprot
    $uniprot_gene = "/^(?=.*[A-Za-z])[A-Za-z0-9_\-()]+$/";  // at least one letter, digits, underscores
    $result = [];                                           // will contain the matching formats

    // compare searchvalue to regex expressions
    if (preg_match($publication_id, $searchValue) === 1) {
        $result[] = "IF.publication_id";
    }
    if (preg_match($taxon_id, $searchValue) === 1) {
        $result[] = "UT.taxon_id";
    }
    if (preg_match($uniprot_ac, strtoupper($searchValue)) === 1) {
        $result[] = "UP.uniprot_ac";
    }
    if (preg_match($uniprot_gene, strtoupper($searchValue)) === 1) {
        $result[] = "UP.uniprot_gene";
    }
    return $result;
}

// Check is the database contains the value at the specified type / format
function verifDBcontainSearch($searched_element, $type) {
    /**
     * Query the database to know if the searched element is in the database
     * 
     * Args:
     * - searched element: the given element in the searchbar
     * - type: type that could match the format in ['uniprot_ac', 'uniprot_gene',
     *         'taxon_id', 'publication_id']
     * 
     * Return:
     * - $result: boolean (t or f)
     */

    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error()); // starting postgres connection

    // define the table and column name to search with given type
    if ($type == "IF.publication_id") {
        $table = "interaction_full";
        $alias = "IF";
        $column = "IF.publication_id" ;
    }
    if ($type == "UT.taxon_id") {
        $table = "uniprot_taxonomy";
        $alias = "UT";
        $column = "UT.taxon_id" ;
    }
    if ($type == "UP.uniprot_ac") {
        $table = "uniprot_protein";
        $alias = "UP";
        $column = "UP.uniprot_ac" ;
    }
    if ($type == "UP.uniprot_gene") {
        $table = "uniprot_protein";
        $alias = "UP";
        $column = "UP.uniprot_gene" ;
    }
    // ask the database (not caring about upper or lowercase by converting letters into uppercase)
    $psql_query = 'SELECT EXISTS (SELECT '.$column.' FROM '.$table.' AS '.$alias.' WHERE upper('.$column.') LIKE upper(\''.$searched_element.'\') LIMIT 1)' ;
    $query_result = pg_query($psql_query) or die("\nError in the query execution : ".pg_last_error());
    $result = pg_fetch_row($query_result)[0];           // t or f.
    $close=pg_close($dbconn);                           // Closing postgresql connection
    return $result ;
}

// Extract and clean the values of the input fields
function extractValuesFromString($str_chain) {
    // Remove brackets and trim each value
    $str_chain = str_replace(['(', ')', '[', ']'], '', $str_chain);
    // Split the string into an array using ','
    $values = array_map('trim', explode(',', $str_chain));
    return $values;
}

// Create a dict containing the values of the filter containers 
function createFilterValuesDict($AndOrValue, $format, $operator, $value) {
    return array(
        array('advSearchAndOr' => $AndOrValue, 'advSearchType' => $format, 'advSearchCondition' =>  $operator, 'advSearchValue' => $value)
    );
}

// __________________________________________________________________________________________ //
// _____________________________                               ______________________________ //
// _____________________________ ADVANCED SEARCH VERIFICATIONS ______________________________ //
// __________________________________________________________________________________________ //

// Check if the value matches the uniprot_ac format
function checkUniprotAcFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in uniprot_ac
    $uniprot_ac = "/^([OPQ][0-9][A-Z0-9]{3}[0-9]|[A-NR-Z][0-9]([A-Z][A-Z0-9]{2}[0-9]){1,2})$/"; // uniprot_ac regex given by uniprot
    if (preg_match($uniprot_ac, strtoupper($value)) === 1) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Check if the value matches the uniprot_gene format
function checkUniprotGeneFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in uniprot_gene
    $uniprot_gene = "/^(?=.*[A-Za-z])[A-Za-z0-9_\-()]+$/";  // at least one letter, digits, underscores
    if (preg_match($uniprot_gene, strtoupper($value)) === 1) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Check if the value matches the taxon_id format
function checkTaxonIdFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in taxon id
    $taxon_id = "/^[0-9]{1,9}$/";                            // 1 to 9 digits
    if (preg_match($taxon_id, strtoupper($value)) === 1) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Check if the value matches the taxon_name format
function checkTaxonNameFormat($value) {
    $taxon_name = "/^[A-Za-z\d. ]{1,100}$/";                // letters and digits, points and spaces allowed only.
    if (preg_match($taxon_name, $value) === 1) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Check if the value matches the publication_id format
function checkPublicationIdFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in pubmed id
    $publication_id = "/^[0-9]{6,8}$/";                     // 8 digits
    if (preg_match($publication_id, strtoupper($value)) === 1) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Check if the value matches the detection_method format
function checkDetectionMethodFormat($value) {
    $detection_method = "/^[A-Za-z\d. ]{1,100}$/"; // letters, digits, points and spaces authorized only.
    if (preg_match($detection_method, $value) === 1) {
        return true;
    } else {
        return false;
    }
}

// Check if the value matches the interaction_type format
function checkInteractionTypeFormat($value) {
    $interaction_type = "/^[A-Za-z\d. ]{1,50}$/"; // letters, digits, points and spaces authorized only.
    if (preg_match($interaction_type, $value) === 1) {
        return true;
    } else {
        return false;
    }
}

// Check if the value matches the interaction_type format
function checkWhoPriorityFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in uniprot_ac
    $who_priority = "/^[A-Za-z]{1,10}$/"; // small and maj letters only for max size = 10 letters.
    if (preg_match($who_priority, $value) === 1) {
        return true;
    } else {
        return false;
    }
}

// Check if the value matches the interaction_type format
function checkHazardGroupFormat($value) {
    $value = str_replace(' ', '', $value); // remove eventual spaces in uniprot_ac
    $hazard_group = "/^(1|2|3|4|NULL)$/i"; // 1, 2, 3, 4 or Null (not case sensitive)
    if (preg_match($hazard_group, $value) === 1) {
        return true;
    } else {
        return false;
    }
}

// Check if an input got an accepted format even if it doesn't match the wanted format (for like conditions, inputs might be uncomplete)
function checkAcceptedFormat($value) {
    $authorizedRegex = "/^[A-Za-z\d._ ]{1,100}$/"; // letters, digits, points, underscores and spaces authorized only.
    if (preg_match($authorizedRegex, $value) === 1) {
        return true;
    } else {
        return false;
    }
}

// Check the format of the value(s) knowing the condition (that can allow unique or multiple values)
function checkConditionFormat($type, $condition, $value) {
    $validationFunctions = [
        'UP_A.uniprot_ac' => 'checkUniprotAcFormat',
        'UP_B.uniprot_ac' => 'checkUniprotAcFormat',
        'UT_A.taxon_id' => 'checkTaxonIdFormat',
        'UT_B.taxon_id' => 'checkTaxonIdFormat',
        'UT_A.taxon_name' => 'checkTaxonNameFormat',
        'UT_B.taxon_name' => 'checkTaxonNameFormat',
        'UP_A.uniprot_gene' => 'checkUniprotGeneFormat',
        'UP_B.uniprot_gene' => 'checkUniprotGeneFormat',
        'IF.publication_id' => 'checkPublicationIdFormat',
        'IF.detection_method' => 'checkDetectionMethodFormat',
        'IF.interaction_type' => 'checkInteractionTypeFormat',
        'UT_A.who_priority' => 'checkWhoPriorityFormat',
        'UT_A.hazard_group' => 'checkHazardGroupFormat',
    ];    
    $validationFunction = $validationFunctions[$type];
    $valueToTest = extractValuesFromString($value);
    if ($condition == ' = ' || $condition == ' != ') { // format must match exactly
        foreach ($valueToTest as $singleValue) {
            if (!$validationFunction($singleValue)) {
                return FALSE;  // If any value doesn't match the format, return false
            }
        }
        return TRUE;
    } else { // for 'like' or 'not like' conditions, the values can be uncomplete  
        foreach ($valueToTest as $singleValue) {
            if (!$validationFunction($singleValue)) { // if the value doesn't match the specific type format
                if (!checkAcceptedFormat($singleValue)) { // check if the format is still accepted
                    return FALSE;
                } 
            }
        }
        return TRUE;
    }
}


// __________________________________________________________________________________________ //
// ___________                                                                 ______________ //
// ___________ BASIC SEARCH AND ADVANCED SEARCH COMMON QUERY PARTS AND DISPLAY ______________ //
// __________________________________________________________________________________________ //

// Common part for the basic and advanced search queries (before adding the condition(s))
function writeCommonQueryPart() {
    $common_query_part = "SELECT DISTINCT IF.mnt_interaction_id, 
    CONCAT(
        'who priority: ', 
        COALESCE(
            (SELECT UT_A.who_priority FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA), 
            'N/A'
        ), 
        '<br>hazard_group: ', 
        COALESCE(
            (SELECT UT_A.hazard_group FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA), 
            'N/A'
        ),
        ''
    ) AS pathogen_info,
    COALESCE(bp.bact_prot_annot, '-') AS bact_prot_annot,
    IF.interactor_ida, 
    CONCAT(
        (SELECT UP_A.uniprot_gene FROM uniprot_xref UX_A 
        INNER JOIN uniprot_protein UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac 
        WHERE UX_A.interactor_id = IF.interactor_idA),
        '<br><br>',
        SPLIT_PART(
            (SELECT UT_A.taxon_name FROM uniprot_xref UX_A 
            INNER JOIN uniprot_protein UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac 
            INNER JOIN uniprot_taxonomy UT_A ON UT_A.taxon_id = UP_A.uniprot_taxon_id 
            WHERE UX_A.interactor_id = IF.interactor_idA
            ), ' (', 1
        )
    ) AS gene_a,
    IF.taxon_interactor_idA,
    IF.interactor_idb, 
    CONCAT(
        (SELECT UP_B.uniprot_gene FROM uniprot_xref UX_B 
        INNER JOIN uniprot_protein UP_B ON UP_B.uniprot_ac = UX_B.uniprot_ac 
        WHERE UX_B.interactor_id = IF.interactor_idB),
        '<br><br>',
        SPLIT_PART(
            (SELECT UT_B.taxon_name FROM uniprot_xref UX_A 
            INNER JOIN uniprot_protein UP_B ON UP_B.uniprot_ac = UX_A.uniprot_ac 
            INNER JOIN uniprot_taxonomy UT_B ON UT_B.taxon_id = UP_B.uniprot_taxon_id 
            WHERE UX_A.interactor_id = IF.interactor_idB
            ), ' (', 1
        )
    ) AS gene_B,
    IF.taxon_interactor_idB,
    IF.detection_method,
    IF.interaction_type, 
    IF.publication_id, 
    IF.MI_score, 
    IF.annotations

    FROM interaction_full AS IF

    LEFT JOIN (
        SELECT 
            uniprot_ac, 
            STRING_AGG(DISTINCT annotation_description, ', ') AS bact_prot_annot
        FROM view_bact_prot_annotation
        GROUP BY uniprot_ac
    ) AS bp ON bp.uniprot_ac = SPLIT_PART(IF.interactor_ida, '-', 1)";

    return $common_query_part;
}

// Creates the basic search table using the query constructed with the specified condition
function searchTableCreation($query_result) {
    $AllBmInteractionIds = [];
    // HEADER                                                                         // Displaying the resulting table in HTML
    echo("<table id='search_table' class='display stripe table_php'>\n<thead><tr>");  // Creation of the table and its header ()
    $col_number = pg_num_fields($query_result);                                       // i = number of columns
    $dispColList = [];                                                                // list of columns to display
    for ($i = 0; $i < $col_number; $i++) {                                            // iterate through the col names
        $col_name = pg_field_name($query_result, $i);                                 // takes the new col name
        $disp_colname = MapColnameDispname($col_name);                                // replace underscores by spaces in headers names
        if ($col_name != 'annotations') {                                             // avoid exporting annotations column
            echo "\n<th col-id='${col_name}' col-index='".($i)."' class='exportable'>${disp_colname}</th>" ; 
        }
        else {                                                                        // display the col name in a new cell on same line and a select filter
            echo "\n<th col-id='${col_name}' col-index='".($i)."'>${disp_colname}</th>" ; 
        }
        $dispColList[] = $col_name ;                                                  // add the column to display to be used as key
    }
    echo("</tr></thead><tbody>");                                                                                 
    // DATA
    $row_index = 0 ;                                                                  // to put a specific index in rows
    while($row_elem = pg_fetch_row($query_result)) {                                  // for all the rows in the resulting table
        echo("\n<tr row-index=".$row_index.">");                                      // creates a new line in the html table
        $count_cells = count($row_elem);                                              // number of elements in the row
        for ($j = 0; $j < $count_cells; $j++) {                                       // for all the elements in the line
            $current_cell = current($row_elem);                                       // current line cell element
            if ($j == 3 or $j == 6){                                                  // for the identifiers
                // display the identifier with a link to uniprot
                echo("<td><a class='table_link' href='https://www.uniprot.org/uniprotkb/".$current_cell."/entry' 
                    target='_blank' title='see on Uniprot'>".$current_cell."</a></td>");
            }
            else if ($j == 11) {
                echo("<td><a class='table_link' href='https://pubmed.ncbi.nlm.nih.gov/".$current_cell."/' 
                    target='_blank' title='see on PubMed'>".$current_cell."</a></td>");
            }
            else{
                if ($j == 0) {
                    $AllBmInteractionIds[] = $current_cell;
                }
                echo("<td>".$current_cell."</td>");                                    // display the current element in a new cell
            }
            next($row_elem);                                                           // taking the next element of the row
        }
        $row_index++ ;
        echo("</tr>");     
    }
    echo("</tbody></table>");                                                          // Closing the line, the body and the table
    echo("<script>
            var AllBmInteractionIds = ". json_encode($AllBmInteractionIds) .";
        </script>");
}

// Creates conditions in the same way for both search type using the format, condition (default '=' for basic search) and value(s)
function conditionWithFormat($format, $operator, $value) { // version sans les options enelvees du select
    /**
     * Creates a condition to add to the query for a specific format.
     * 
     * Args:
     * - format: one value in the list ['taxon_id', 'taxon_name', 'publication_id', 'uniprot_ac', 'uniprot_gene'
     *                                  'detection_method', 'interaction_type']
     * - value: the value given in the search bar or input fields of advanced search (string containing one or more values separed by ',')
     * 
     * Return:
     * - the part of the condition to add to the query
     */
    // remove trailing spaces if the format dont accept spaces
    if (!in_array($format, ["IF.detection_method", "IF.interaction_type", "UT_A.taxon_name", "UT_B.taxon_name"])) {
        $value = str_replace(' ', '', $value);
    }
    // Avoid case sensitivity for letters or return digits directly
    if (in_array($operator, [" = ", " != ", " like ", " not like "])) {
        if (in_array($operator, [" = ", " != "])) {
            if (in_array($format, ["UT_A.taxon_id", "UT_B.taxon_id", "IF.publication_id"])) {
                return "$format $operator '$value'";
            } else {
                return "upper($format) $operator upper('$value')";
            }
        } elseif ($operator == " like ") {
            return "$format ilike '%$value%'";
        } elseif ($operator == " not like ") {
            return "$format not ilike '%$value%'";
        }
        
    } else { // formats that allow multiple values
        $values = explode(',', $value);

        if ($operator == " like[] ") {
            $formattedConditions = implode(' OR ', array_map(function($v) use ($format) {
                return "$format ilike '%$v%'";
            }, array_map('trim', $values)));
            return "($formattedConditions)";

        } elseif ($operator == " not like[] ") {
            $formattedConditions = implode(' AND ', array_map(function($v) use ($format) {
                return "$format not ilike '%$v%'";
            }, array_map('trim', $values)));
            return "($formattedConditions)";
        }
    }
}


// __________________________________________________________________________________________ //
// ______________________________                             _______________________________ //
// ______________________________ BASIC SEARCH QUERY CREATION _______________________________ //
// __________________________________________________________________________________________ //

// Calls writeCommonQueryPart() and conditionWithFormat() with unique condition to create the complete basic search query
function searchTableCreationQuery($matching_types, $searchValue) {
    /**
     * Creates the query to display the result table using the given data from the searchbar
     * The condition will be different depending on the type of given variable.
     * 
     * Args:
     * - matching_types: a list of minimum size = 1 and containing all the format matching by the value found in database
     * - searchValue: the value that the user passed in the searchbar
     * 
     * Return:
     * - tableQuery: the final query to display the wanted table
     * 
     * SELECT
     * 
     * 
     */
    $common_query_part = writeCommonQueryPart();
    $common_query_part .= addBasicSearchQueryJoins();
    for ($i = 0; $i < sizeof($matching_types); $i++){    // go through all matching types to add the conditions
        if ($i == 0) {                                  // only for the first one, start with a 'WHERE'
            $query_condition = " WHERE ".conditionWithFormat($matching_types[$i], " = ", $searchValue);
        }
        else {                                          // for the other matching format, adding a 'OR' condition
            $query_condition = $query_condition." OR ".conditionWithFormat($matching_types[$i], " = ", $searchValue);
        }
    }
    $psql_query = $common_query_part.$query_condition;
    return $psql_query;
}

function addBasicSearchQueryJoins() {
    return "INNER JOIN uniprot_xref AS UX ON UX.interactor_id = IF.interactor_idA OR UX.interactor_id = IF.interactor_idB
    INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = UX.uniprot_ac
    INNER JOIN uniprot_taxonomy AS UT ON UT.taxon_id = UP.uniprot_taxon_id";
}

// __________________________________________________________________________________________ //
// _____________________________                                _____________________________ //
// _____________________________ ADVANCED SEARCH QUERY CREATION _____________________________ //
// __________________________________________________________________________________________ //

// Calls writeCommonQueryPart() and createQueryCondition (calling conditionWithFormat()) with unique or multiple 
// condition(s) to create the complete advanced search query
function createAdvancedSearchQuery($filterValues) {
    $advancedQuery = writeCommonQueryPart();
    $advancedQuery .= addAdvSearchQueryJoins();
    $advancedQuery .= createQueryCondition($filterValues);
    return $advancedQuery;
}

// Add a unique or multiple conditions at once depending on the filter containers values
function createQueryCondition($filterValues) {
    $allConditions = [];  // Stores conditions that are NOT part of an OR group
    $orGroup = [];        // Stores conditions that should be inside ( ... OR ... )
    $lastWasOr = false;

    for ($i = 0; $i < count($filterValues); $i++) {
        $filter = $filterValues[$i];
        $format = $filter['advSearchType'];
        $operator = $filter['advSearchCondition'];
        $value = $filter['advSearchValue'];

        $condition = conditionWithFormat($format, $operator, $value);

        $isOr = isset($filter['advSearchAndOr']) && strtoupper($filter['advSearchAndOr']) === ' OR ';

        if ($isOr) {
            if (empty($orGroup)) {
                // If it's the first OR, add the previous condition to the OR group
                if (!empty($allConditions)) {
                    $orGroup[] = array_pop($allConditions);
                }
            }
            $orGroup[] = $condition;
            $lastWasOr = true;
        } else {
            if ($lastWasOr) {
                // End of OR group
                $allConditions[] = "(" . implode(" OR ", $orGroup) . ")";
                $orGroup = [];
                $lastWasOr = false;
            }
            $allConditions[] = $condition;
        }
    }

    // Handle case where the last condition was part of an OR group
    if (!empty($orGroup)) {
        $allConditions[] = "(" . implode(" OR ", $orGroup) . ")";
    }

    return "WHERE " . implode(" AND ", $allConditions);
}

// Make joins on the database tables to display the full result
function addAdvSearchQueryJoins() {
    return "INNER JOIN uniprot_xref AS UX_A ON UX_A.interactor_id = IF.interactor_idA 
    INNER JOIN uniprot_xref AS UX_B ON UX_B.interactor_id = IF.interactor_idB 
    INNER JOIN uniprot_protein UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac 
    INNER JOIN uniprot_protein UP_B ON UP_B.uniprot_ac = UX_B.uniprot_ac 
    INNER JOIN uniprot_taxonomy AS UT_A ON UT_A.taxon_id = UP_A.uniprot_taxon_id 
    INNER JOIN uniprot_taxonomy AS UT_B ON UT_B.taxon_id = UP_B.uniprot_taxon_id ";  
}


// __________________________________________________________________________________________ //
// _____________________________                                _____________________________ //
// _____________________________ CUSTOM DOWNLOAD FILES CREATION _____________________________ //
// __________________________________________________________________________________________ //


// query on view_full_interaction_data
function query_full_interaction_data($bmIdsToExport) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $bmIdsToExport) . "'";
    $query = "SELECT * FROM view_full_interaction_data WHERE mnt_interaction_id in (".$listBmIds.");";
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result;
}

// query on mimicint_interface
function query_mimicint_data($mimicint_bmIds) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $mimicint_bmIds) . "'";
    $query = "SELECT MX.mnt_interaction_id, MI.* FROM mimicint_interface AS MI
            INNER JOIN mimicint_xref AS MX ON MX.mimicint_interaction_id = MI.mimicint_interaction_id
            WHERE MX.mnt_interaction_id in (".$listBmIds.")
            ORDER BY MX.mnt_interaction_id, MI.mimicint_interaction_id;";
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result ;
}

// query on mimicint_interface
function query_feature_data($features_bmIds) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $features_bmIds) . "'";
    $query = "SELECT * FROM interaction_feature 
            WHERE mnt_interaction_id in (".$listBmIds.")
            ORDER BY mnt_interaction_id;";
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result ;
}

// query on view_bact_prot_annotation
function query_protAnnot_data($protAnnot_bmIds) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $protAnnot_bmIds) . "'";
    $query = "SELECT PA.* 
            FROM view_bact_prot_annotation AS PA
            INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = PA.uniprot_ac
            INNER JOIN interaction_full AS IF ON UX.interactor_id = IF.interactor_ida
            WHERE IF.mnt_interaction_id in (".$listBmIds.")
            ORDER BY PA.uniprot_ac;";
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result ;
}

// query on view_bact_prot_annotation
function query_cellcomp_data($bmIds) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $bmIds) . "'";
    $query = "SELECT CC.uniprot_ac AS uniprot_ac, 
        CC.cc_value AS cellular_component_value, 
        CC.cc_id AS cellular_component_id, 
        CC.topology_value AS topology_value, 
        CC.topology_id AS topology_id, 
        CC.annotation_source AS annotation_source 
    FROM interaction_full AS IF 
    INNER JOIN cellular_components AS CC
        ON CC.uniprot_ac = IF.interactor_idA
        OR CC.uniprot_ac = IF.interactor_idB
    WHERE IF.mnt_interaction_id in (".$listBmIds.")  
    ORDER BY CC.uniprot_ac;";
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result ;
}

// query on view_bact_prot_annotation
function query_alphafold_data($bmIds) {
    $dbconn = pg_connect('host=db port=5432 dbname=bactmentha_db user=postgres password=postgres')
          or die('Unable to connect to the database : ' . pg_last_error());
    $listBmIds = "'" . implode("','", $bmIds) . "'";
    $query = "SELECT AF.* 
            FROM af3_predictions AS AF 
            WHERE AF.mnt_interaction_id in (".$listBmIds.") 
            ORDER BY AF.model;"; 
    $query_result = pg_query($query) or die("\nError in the query execution : ".pg_last_error());
    $close=pg_close($dbconn);
    return $query_result ;
}



function createQueryResultFile($filename, $query_result) {
    $download_path = '/tmp/bactmentha_download';
    $filepath = $download_path . "/" . $filename;
    $output = fopen($filepath, 'w');
    if (!$output) {
        die("Unable to open file for writing: " . $filepath);
    }
    $headers = array();
    for ($j = 0; $j < pg_num_fields($query_result); $j++) {
        $headers[] = pg_field_name($query_result, $j);
    }
    fputcsv($output, $headers);
    while ($row = pg_fetch_assoc($query_result)) {
        fputcsv($output, $row);
    }
    fclose($output);
    return $filename;
}

function downloadSearchCsvFiles($bmIdsToExport) {
    $size = count($bmIdsToExport);
    $mimicint_bmIds = [];
    $features_bmIds = [];
    $protAnnot_bmIds = [];
    $cellcomp_bmIds = [];
    $alphafold_bmIds = [];
    $files = [];

    foreach($bmIdsToExport as $bmId) {
        if (IsItInProteinAnnotations($bmId) == 't') { $protAnnot_bmIds[] = $bmId; }
        if (IsItInCellularComponents($bmId) == 't') { $cellcomp_bmIds[] = $bmId; }
        if (IsItInInteractionFeature($bmId) == 't') { $features_bmIds[] = $bmId; }
        if (IsItInMimicintInterface($bmId) == 't') { $mimicint_bmIds[] = $bmId; }
        if (IsItInAlfphafoldComplexes($bmId) == 't') { $alphafold_bmIds[] = $bmId; }
    };

    $randomString = bin2hex(random_bytes(4)); // Generates a random hexadecimal string

    $fullInteractionDataTable = query_full_interaction_data($bmIdsToExport);
    $files[] = createQueryResultFile("bactmentha_".$randomString."_full_interaction_data.csv", $fullInteractionDataTable);
    if (sizeof($mimicint_bmIds) >= 1) { 
        $mimicintInteractionDataTable = query_mimicint_data($mimicint_bmIds); 
        $files[] = createQueryResultFile("bactmentha_".$randomString."_mimicint_interfaces.csv", $mimicintInteractionDataTable);
    }
    if (sizeof($features_bmIds) >= 1) { 
        $featureInteractionDataTable = query_feature_data($features_bmIds);
        $files[] = createQueryResultFile("bactmentha_".$randomString."_binding_regions.csv", $featureInteractionDataTable);
    }
    if (sizeof($protAnnot_bmIds) >= 1) { 
        $protAnnotInteractionDataTable = query_protAnnot_data($protAnnot_bmIds);
        $files[] = createQueryResultFile("bactmentha_".$randomString."_bacterial_proteins_annotations.csv", $protAnnotInteractionDataTable);
    }
    if (sizeof($alphafold_bmIds) >= 1) { 
        $alaphafoldInteractionDataTable = query_alphafold_data($alphafold_bmIds);
        $files[] = createQueryResultFile("bactmentha_".$randomString."_af3_predictions.csv", $alaphafoldInteractionDataTable);
    }
    if (sizeof($cellcomp_bmIds) >= 1) { 
        $cellcompInteractionDataTable = query_cellcomp_data($cellcomp_bmIds);
        $files[] = createQueryResultFile("bactmentha_".$randomString."_cellular_components.csv", $cellcompInteractionDataTable);
    }
    return json_encode($files);
}

/* Clean the /tmp/bactmentha_download folder by erasing the old files */
function cleanupOldFiles() {
    $maxAgeInSeconds = 86400; // 86400 seconds = 24 hours
    $currentTime = time();
    $dir = opendir('/tmp/bactmentha_download');
    // Loop through each file in the directory
    while (($file = readdir($dir)) !== false) {
        $filePath = "/tmp/bactmentha_download/" . $file;
        // Skip if it's not a regular file
        if (!is_file($filePath)) {
            continue;
        }
        $fileModifiedTime = filemtime($filePath);
        $fileAgeInSeconds = $currentTime - $fileModifiedTime;
        if ($fileAgeInSeconds > $maxAgeInSeconds) {
            unlink($filePath);
        }
    }
    closedir($dir);
    echo('<script>console.log("erased the old files")</script>');
}

?>
