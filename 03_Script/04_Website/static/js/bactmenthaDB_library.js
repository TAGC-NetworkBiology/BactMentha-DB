// JS lib for BactMentha Website
// coding : UTF-8
// Author : Lou BERGOGNE

// __________________________________________________________________________________________ //
// _____________________________________                _____________________________________ //
// _____________________________________ USED FUNCTIONS _____________________________________ //
// __________________________________________________________________________________________ //


// __________________________________________________________________________
// DISPLAY THE ANNOTATIONS BUTTONS FOR CHILD ROW TABLES (HOME AND DATA PAGES)

function displayAnnotationButtons(data) {
    /**
     * Find the right buttons to display depending on if some informations are found in some tables for
     * the given line.
     * 
     * Args:
     * - data: a string -> "XXX", where X can be 't' or 'f' depending on if the mnt_interaction_id
     *   of the line is related to some data in the tables : mimicint_interface, interaction_feature and 
     *   protein_annotation (for each interactor)
     * 
     * Return:
     * htmlText: some html to display three buttons in color or in grey
     */
    if (data[2] != 'f') {                // 'tt', 'tf', 'ft' or 'ff' depending on if the interactors are related to some data in protein_annotation
        var protAnnotBtn = "<a class='moreInfoBtnLinkYellow' title='Bacterial Protein Annotations'><img src='/static/img/buttons/annotations_button.png' class='child_row_button'></a>";
    }
    else {
        var protAnnotBtn = "<a class='moreInfoBtnLinkGrey_pa' title='Bacterial Protein Annotations'><img src='/static/img/buttons/grey_button.png' class='child_row_button'></a>";
    }
    if (data[1] != 'f') {                   // 't' if bmId related to some data in interaction_feature, else 'f'
        var intFeatBtn = "<a class='moreInfoBtnLinkGreen' title='Binding Regions'><img src='/static/img/buttons/features_button.png' class='child_row_button'></a>";
    }
    else {
        var intFeatBtn = "<a class='moreInfoBtnLinkGrey_br' title='Binding Regions'><img src='/static/img/buttons/grey_button.png' class='child_row_button'></a>";
    }
    if (data[0] != 'f') {                   // 't' if bmId related to some data in mimicint_interface, else 'f'
        var mimicintBtn = "<a class='moreInfoBtnLinkBlue' title='Mimicint Interfaces'><img src='/static/img/buttons/mimicint_button.png' class='child_row_button'></a>";
    }
    else {
        var mimicintBtn = "<a class='moreInfoBtnLinkGrey_mi' title='Mimicint Interfaces'><img src='/static/img/buttons/grey_button.png' class='child_row_button'></a>";
    }
    var htmlText = "<div class='child_row_div'>"+protAnnotBtn+" "+intFeatBtn+" "+mimicintBtn+"</div>";
    return htmlText;
}

// For mimicint interfaces
function format_blue(d, bmIdIndex) {
    /**
     * Formatting the child Row when the user click on the blue button corresponding
     * to the mimicint_interface informations. It uses Ajax to call some Php function
     * that execute the request.
     * 
     * Args:
     * - d: the original data object for the row
     * 
     * Return:
     * - div: the child row content
     */
    var div = $('<div/>')                                           // div creation to put the child table inside
        .addClass( 'loading' )                                        // Write loading while the query is executing
        .text( 'Loading...' );
    $.ajax({                                                        // ajax code to call php function with Jquery
        url: '../static/php/bactmenthaDB_library.php',                // library where to find the action to execute
        data: {bmId: d[bmIdIndex], action: 'dispMimicintChildRow'},           // saving bmId and action ito session parameters
        type: "GET",                                                  // with the GET method (could have been POST)
        dataType:'text',
        success: function(response) {                                 // response is the result of the code executed after action is saved
        div.html(response);                                         // when isset 'action', the corresponding function is executed and the
        div.removeClass( 'loading' );                               // result of it is returned in the div instead of 'loading' text
        }
    });
    return div;                                                     // return the div as the child row content
}

// For interaction_feature
function format_green(d, bmIdIndex) {
    /**
     * Formatting the child Row when the user click on the green button corresponding
     * to the interaction_feature informations. It uses Ajax to call some Php function
     * that execute the request.
     * 
     * Args:
     * - d: the original data object for the row
     * 
     * Return:
     * - div: the child row content
     */
    var div = $('<div/>')
        .addClass( 'loading' )
        .text( 'Loading...' );
    $.ajax({
        url: '../static/php/bactmenthaDB_library.php',
        data: {bmId: d[bmIdIndex], action: 'dispInteractionFeatureChildRow'},
        type: "GET",
        dataType:'text',
        success: function(response) {
        div.html(response);
        div.removeClass( 'loading' );
        }
    });
    return div;
}

// For protein annotation
function format_yellow(d, bmIdIndex) {
    /**
     * Formatting the child Row when the user click on the green button corresponding
     * to the protein_annotation informations. It uses Ajax to call some Php function
     * that execute the request.
     * 
     * Args:
     * - d: the original data object for the row
     * 
     * Return:
     * - div: the child row content
     */
        var div = $('<div/>')
        .addClass( 'loading' )
        .text( 'Loading...' );
    $.ajax({
        url: '../static/php/bactmenthaDB_library.php',
        data: {bmId: d[bmIdIndex], action: 'dispProtAnnotationChildRow'},
        type: "GET",
        dataType:'text',
        success: function(response) {
        div.html(response);
        div.removeClass( 'loading' );
        }
    });
    return div;
}

// _____________________________________________________________________________
// DOWNLOAD THE INTERACTION DATA FROM THE DATABASE WITH CUSTOM DATATABLES BUTTON

// Recover the index of the mnt_interaction_id column
function getBmIdColumnIndex(tableId) {
    const mapTableBmIndex = {
        '#search_table' : 1,
        '#who_prio_table' : 11,
        '#query_table' : 11,
    };
    return mapTableBmIndex[tableId];
}

// Take all the mnt_interaction_ids of the Datatable that are displayed
function recoverDisplayedLinesBmIds(tableId, AllBmInteractionIds) {
    var displayedRowsIndexes = [];
    var displayedRowsBmIds = [];
    var table = $(tableId).DataTable(); // get the DataTable object
    var visibleRows = table.rows({ search: 'applied' }).nodes().to$(); // get all visible rows across all pages
    visibleRows.each(function(index, row) {
        var currentIndex = table.row(row).index(); // get the index of the row in the DataTable
        displayedRowsIndexes.push(currentIndex);
    });
    displayedRowsIndexes.forEach(index => {
        displayedRowsBmIds.push(AllBmInteractionIds[index]);
    });
    return displayedRowsBmIds;
}


// Calls the files creation with the displayed bm_id only to process the query
function downloadAllInteractionData(tableId) {
    const BmIdsToExport = recoverDisplayedLinesBmIds(tableId, AllBmInteractionIds);
    // Perform an AJAX request to send the filter values to the server
    $.ajax({
        type: "POST",
        url: window.location.href,
        data: { BmIdsToExport: BmIdsToExport}, // Pass the filter values and download token to the server
        success: function (response) {
            alert('Loading the data can take a few minutes. Thank you for your comprehension.');
            window.location.reload(true);
        },
        error: function (error) {
            console.error("Error sending mnt_interaction_ids to server: ", error);
        }
    });
}

// Download the files that were created using php
function downloadFiles(files) {
    var confirmDownload = confirm(files.length + ' CSV files are going to be downloaded. Do you want to continue?');
    if (confirmDownload) {
        alert('Your download has started. Please check your download folder.');
        files.forEach(function (file) {
            var link = document.createElement('a');
            link.href = '/tmp/bactmentha_download/' + file;
            link.download = file;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
    $.ajax({
        type: "GET",
        url: window.location.href, // Current PHP file
        data: { unsetBmIdsToExport: true}, // Specific parameter to trigger the unset
        success: function(response) {
            window.location.reload(true);
        },
        error: function(error) {
            console.error("Error unsetting download token on server: ", error);
        }
    });
}


// ______________________________________________________________
// ACTIONS ON THE FILTER CONTAINERS IN ADVANCED SERCH (ADV SEARCH/BROWSE PAGE)

// Helper function to create a new filter_container
function createFilterContainer() {
    var templateContainer = document.createElement('div');
    templateContainer.className = 'filter_container';

    var contentContainer = document.createElement('div');
    contentContainer.className = 'filter_content_container';

    // AND / OR Select
    var selectAndOr = document.createElement('select');
    selectAndOr.className = 'filter_element';
    selectAndOr.name = 'adv_search_AndOR';
    var options1 = [' AND ', ' OR '];
    options1.forEach(function (optionValue) {
        var option = document.createElement('option');
        option.value = optionValue;
        option.text = optionValue;
        selectAndOr.appendChild(option);
    });
    contentContainer.appendChild(selectAndOr);

    // First Select for type / format
    var select1 = document.createElement('select');
    select1.className = 'filter_element selectType';
    select1.name = 'adv_search_type';
    var options1 = ['UP_A.uniprot_ac', 'UP_A.uniprot_gene', 'UT_A.taxon_id', 'UT_A.taxon_name', 'UP_B.uniprot_ac',
                    'UP_B.uniprot_gene', 'UT_B.taxon_id', 'UT_B.taxon_name', 'IF.publication_id', 'IF.detection_method', 
                    'IF.interaction_type', 'UT_A.who_priority', 'UT_A.hazard_group'];
    options1.forEach(function (optionValue) {
        var option = document.createElement('option');
        option.value = optionValue;
        option.text = getOptionTextSelectType(option.value)
        select1.appendChild(option);
    });
    contentContainer.appendChild(select1);

    // Second Select for operator
    var select2 = document.createElement('select');
    select2.className = 'filter_element selectCondition';
    select2.name = 'adv_search_condition';
    var options2 = [' = ', ' != ', ' like[] ', ' not like[] ']
    options2.forEach(function (optionValue) {
        var option = document.createElement('option');
        option.value = optionValue;
        option.text = getOptionTextSelectOperator(option.value)
        select2.appendChild(option);
    });
    contentContainer.appendChild(select2);

    // Input Element for condition value
    var input = document.createElement('input');
    input.className = 'filter_element adv_search_value';
    input.name = 'adv_search_value';
    input.type = 'text';
    input.style.flex = '1';
    input.placeholder = 'e. g. : uniprot_ac  |  is equal to  |  P0ABE7';
    contentContainer.appendChild(input);

    // Bin Button to erase the container
    var binButton = document.createElement('button');
    binButton.type = 'button';
    binButton.className = 'filter_element bin_button';
    binButton.innerHTML = "<img src='/static/img/buttons/trash-icon.png'>";
    binButton.onclick = function () {
        removeFilterContainer(binButton);
    };
    contentContainer.appendChild(binButton);

    templateContainer.appendChild(contentContainer);

    return templateContainer;
}

// Add new filter container in Advanced Search
function addFilterContainer() {
    var newContainer = createFilterContainer();
    var advancedSearchContainer = document.querySelector('.advanced_search_container');
    advancedSearchContainer.appendChild(newContainer);
}

// Remove an existing filter container in Advanced Search
function removeFilterContainer(button) {
    // Accéder au parent (filter_container) à partir du bouton cliqué
    var filterContainer = button.closest('.filter_container');

    // Supprimer le parent (filter_container)
    if (filterContainer) {
        filterContainer.remove();
    }
}

// Update the content of the input placeholder on change of the operator or type of value
function updatePlaceholder(container) {
    var type = container.find('.selectType').val();
    var condition = container.find('.selectCondition').val();
    var placeholder = retrievePlaceholder(type, condition);
    // Update the placeholder in the specific input field
    container.find('.adv_search_value').attr('placeholder', placeholder);
}

// Get the values of the Advanced Search filters dans pass them to the server
function getAllFilterValues() {
    var filterValues = [];
    // Iterate over each filter_container
    $('.filter_container').each(function () {
        var advSearchAndOr = $(this).find('select[name="adv_search_AndOR"]').length > 0 ?
                         $(this).find('select[name="adv_search_AndOR"]').val() :
                         null;
        var advSearchType = $(this).find('select[name="adv_search_type"]').val();
        var advSearchCondition = $(this).find('select[name="adv_search_condition"]').val();
        var advSearchValue = $(this).find('input[name="adv_search_value"]').val();
        // Create an object with the values
        var filterObject = {
            advSearchAndOr: advSearchAndOr,
            advSearchType: advSearchType,
            advSearchCondition: advSearchCondition,
            advSearchValue: advSearchValue
        };
        // Push the object into the filterValues array
        filterValues.push(filterObject);
    });
    // Perform an AJAX request to send the filter values to the server
    $.ajax({
        type: "GET",
        url: "/Search?timestamp=" + new Date().getTime(), // Replace with the actual path to your PHP script
        data: { filterValues: filterValues }, // Pass the filter values to the server
        success: function (response) {
            // Now filterValues array contains objects with values for each filter_container
            console.log("Filter values sent to server successfully");
            // Reload the page after the AJAX request completes
            window.location.reload(true);
        },
        error: function (error) {
            console.error("Error sending filter values to server: ", error);
        }
    });
}

// Reset the Advanced Search form when user click 'Reset' when the form is displayed
function resetAdvancedSearch() {
    window.location.reload(true);
}


// ______________________________________________________________
// DISPLAYING THE FILTER CONTAINERS IN ADVANCED SERCH (HOME PAGE)

// Get the text to display in the type select options
function getOptionTextSelectType(value) {
    const attributeMappings = {
        'UP_A.uniprot_ac': '(bacterial) Uniprot AC',
        'UP_A.uniprot_gene': '(bacterial) Uniprot Gene symbol',
        'UT_A.taxon_id': '(bacterial) Taxon ID',
        'UT_A.taxon_name': '(bacterial) Taxon Name',
        'UP_B.uniprot_ac': '(host) Uniprot AC',
        'UP_B.uniprot_gene': '(host) Uniprot Gene symbol',
        'UT_B.taxon_id': '(host) Taxon ID',
        'UT_B.taxon_name': '(host) Taxon Name',
        'IF.publication_id': 'Publication ID (PubMed ID)',
        'IF.detection_method': 'Detection Method',
        'IF.interaction_type': 'Interaction Type',
        'UT_A.who_priority': 'Pathogen WHO Priority Level',
        'UT_A.hazard_group': 'Pathogen Hazard Group'
    };
    return attributeMappings[value];
}

// Get the text to display the condition select options
function getOptionTextSelectOperator(value) {
    const attributeMappings = {
        ' = ': ' is equal to ',
        ' != ': ' is different from ',
        ' like[] ': ' contains ',
        ' not like[] ': ' does not contain ',
    };
    return attributeMappings[value];
}

// Get the format to display in the input placeholder using the value of the select type
function getFormatPlaceholder(value) {
    return value.split(".")[1];
}

// Get the unique or multiple exemple(s) for the input placeholder
function retrievePlaceholderExemple(type, condition) {
    if (condition == ' = ' || condition == ' like ' || condition == ' != ' || condition == ' not like ') {
        return getUniqueExemplePlaceholder(type)
    } else { // list of values expected
        return getMultipleExemplesPlaceholder(type)
    }
}

// Get the content to display in the input placeholder knowing the selected type and condition
function retrievePlaceholder(type, condition) {
    format = getFormatPlaceholder(type);
    if (condition == ' like[] ') {
        return "e. g. : " + format + " | contains | " + retrievePlaceholderExemple(type, condition);
    } else if (condition == ' not like[] ') {
        return "e. g. : " + format + " | does not contain | " + retrievePlaceholderExemple(type, condition);
    } else if (condition == ' = ') { 
        return "e. g. : " + format + " | " +  " is equal to " + " | " + retrievePlaceholderExemple(type, condition);;
    } else if (condition == ' != ') { 
        return "e. g. : " + format + " | " +  " is diffrent from " + " | " + retrievePlaceholderExemple(type, condition);;
    }
}

// Get the exemple of the specified type for a condition allowing just one value
function getUniqueExemplePlaceholder(type) {
    const uniqueExemplePlaceholder = {
        'UT_B.taxon_name' : 'Homo sapiens',
        'UT_A.taxon_name' : 'Escherichia coli',
        'UT_B.taxon_id' : '9606',
        'UT_A.taxon_id' : '562',
        'UP_B.uniprot_ac' : 'P04843',
        'UP_A.uniprot_ac' : 'P0ABE7',
        'UP_B.uniprot_gene' : 'RPN1',
        'UP_A.uniprot_gene' : 'cybC',
        'IF.publication_id' : '31611645',
        'IF.detection_method' : 'pull down',
        'IF.interaction_type' : 'proximity',
        'UT_A.who_priority': 'critical',
        'UT_A.hazard_group': '3'
    };
    return uniqueExemplePlaceholder[type];
}

// Get the exemples of the specified type for a condition allowing multiple values
function getMultipleExemplesPlaceholder(type) {
    const MultipleExemplesPlaceholder = {
        'UT_B.taxon_name' : 'Homo sapiens, Mus musculus, Rattus norvegicus',
        'UT_A.taxon_name' : 'Escherichia coli, Yiersinia pestis, Bacillus anthracis',
        'UT_B.taxon_id' : '9606, 10090, 10116',
        'UT_A.taxon_id' : '562, 632, 1392',
        'UP_B.uniprot_ac' : 'P04843, Q8BLU0, D4A133',
        'UP_A.uniprot_ac' : 'P0ABE7, Q8CZU2, A0A6L8PTK5',
        'UP_B.uniprot_gene' : 'RPN1, Flrt2, Atp6v1a',
        'UP_A.uniprot_gene' : 'cybC, yapH, GBAA_4976',
        'IF.publication_id' : '31611645, 20711500, 32165585',
        'IF.detection_method' : 'pull down, two hybrid, enzymatic study',
        'IF.interaction_type' : 'proximity, colocalization, direct interaction',
        'UT_A.who_priority': 'medium, high, critical',
        'UT_A.hazard_group': '2, 3'
    };
    return MultipleExemplesPlaceholder[type];
}

// Redirect to Advanced Search page when Advanced search button clicked on home page
function redirectToAdvancedSearch() {
    window.location.href = '/Search';
}
