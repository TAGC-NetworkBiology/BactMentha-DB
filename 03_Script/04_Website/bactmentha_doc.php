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
    <title>Documentation-BactMentha</title> 
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
    <li><a href="/Download">Download</a></li>
    <li><a href="/Statistics">Statistics</a></li>
    <li><a href="/About" class='current_page'>About &#9660</a>
      <ul>
        <li><a class="no_hover">About &#9660</a></li>
        <li><a class='current_page' href="/Documentation">Documentation</a></li>
        <li><a href="/FAQ">F.A.Q.</a></li>
        <li><a href="/Contact">Contact</a></li>
      </ul>
    </li>     
  </ul>
</nav>
<!--=================================================================== MAIN ====================================================================-->
    <main>
        <h2 class="home_news_title1">Welcome on Bactmentha Documentation !</h2>
        <div class='home_title_bar'></div><br><br><br>

        <div class='doc_main_titles'>I. Project Overview</div>
        <br><br>

            <!-- <div class='doc_second_titles'>Database Overview:</div><br> -->
            <div class='doc_text_content'>BactMentha is a database that provides informations about bacteria-host 
              protein-protein interactions. The specificity of BactMentha database is that it includes interaction 
              interfaces data retrieved using MimicINT tool in addition to experimentally detected binding regions, 
              thus increasing the proportion of known (or predicted) interaction regions compared to other databases.
            </div><br>

            <!-- <div class='doc_second_titles'>Background and Context:</div><br> -->
            <div class='doc_text_content'>Commensal and/or pathogenic bacteria secrete and inject effector proteins into 
              host cells to target various signaling pathways to ensure their survival. Currently, a little over ten 
              thousand interactions between bacterial and human proteins have been cataloged in interaction databases 
              (i.e., IntAct, MINT, BioGrid). As part of a European project (<a class='table_link', 
              href='https://www.healthydietforhealthylife.eu/index.php/joint-actions/hdhl-intimic' target='_blank'>https://www.healthydietforhealthylife.eu/index.php/joint-actions/hdhl-intimic</a>), we generated several 
              thousand new interactions between human proteins and proteins secreted by commensal intestinal Proteobacteria, 
              by combining experimental and computational approaches. We recently implemented a workflow to gather and process 
              protein-protein interaction data, integrating these new interactions with already known interactions into a 
              new database named BactMentha. Furthermore, the workflow uses a method we developed (mimicINT) to predict 
              the interfaces involved in the interactions within the database, thus providing molecular information that could 
              be useful for understanding how bacteria disrupt host cell networks.
            </div><br>

            <!-- <div class='doc_second_titles'>Project Objectives:</div><br> -->
            <div class='doc_text_content'>By providing detailed information about interaction motifs, BactMentha database 
              aims to enable researchers to better understand the underlying mechanisms of bacterial infections and predict 
              new interactions based on known motifs. This can guide experiments to validate these interactions and offer 
              insights for developing new therapeutic strategies targeting these critical bacterial interactions.
            </div><br><br><br>

        <div class='doc_main_titles'>II. Data Provenance</div>
        <br><br>

            <div class='doc_text_content'>Interactions between human proteins and bacterial proteins were retrieved by the 
              BactMentha workflow. This workflow gathers all interactions involving the host (human/mouse/rat) from the 
              databases of the International Molecular Exchange Consortium or <a class='table_link' 
              href='https://www.imexconsortium.org/' target="_blank">IMEx Consortium</a>, which includes <a class='table_link' 
              href='https://www.ebi.ac.uk/intact/documentation/user-guide#interaction_scoring' target="_blank">Intact</a>, 
              <a class='table_link' href='https://mint.bio.uniroma2.it/' target="_blank">MINT</a>, and <a class='table_link' 
              href='https://www.uniprot.org/' target="_blank">Uniprot</a>. Additionally, the taxonomy of all bacteria is 
              retrieved via a query on the National Center for Biotechnology Information or <a class='table_link' 
              href='https://www.ncbi.nlm.nih.gov/' target="_blank">NCBI</a> website. The interactions involving human proteins 
              are then filtered to retain only those in which proteins from bacterial taxa are involved. The interactions are 
              further filtered to remove redundancy for each human-pathogen protein pair (e.g., same interaction referenced in
              distinct databases). <br><br>
              Additional information is added to the interactions, such as proteins sizes and descriptions, and when 
              available, protein interaction regions. The BactMentha workflow then performs BLASTp (Basic Local Alignment 
              Search Tool from NCBI) searches against the <a class='table_link' href='http://www.mgc.ac.cn/VFs/' 
              target="_blank">Virulence Factor Database (VFDB)</a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' 
              target="_blank">BastionHub</a> databases to annotate bacterial proteins as virulence factors or effectors. 
              A sequence similarity threshold of 30% and an alignment coverage of 75% are used to infer annotations for BactMentha 
              bacterial proteins from these two databases. <br><br>
              The <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
              target="_blank">mimicINT</a> software, managed by the <a class='table_link' href='https://doi.org/10.12688/f1000research.29032.1' 
              target="_blank">Snakemake</a> workflow manager, is launched by the BactMentha workflow to identify potential 
              interaction interfaces between human and pathogen proteins. First, mimicINT detects the presence of interaction 
              elements, such as domains (using <a class='table_link' href='https://doi.org/10.1093/bioinformatics/btu031' 
              target="_blank">InterProScan</a>) and SLiMs (using the SLiMProb tool from <a class='table_link' 
              href='https://link.springer.com/protocol/10.1007/978-1-4939-2285-7_6' target="_blank">SLiMSuite</a> and the 
              <a  class='table_link' href='http://elm.eu.org/searchdb.html' target="_blank">Eukaryotic Linear Motif (ELM)</a> database), 
              that resemble those of the host in pathogen proteins, and collects the domains present in human proteins from the 
              <a  class='table_link' href='https://www.ebi.ac.uk/interpro/' target="_blank">InterPro</a> database. Next, 
              the software retrieves known interaction models between two domains from the <a  class='table_link' 
              href='https://sbnb.irbbarcelona.org/node/96' target="_blank">3did database</a> and between a domain and a motif 
              from ELM, and infers interactions (and their interfaces) between human and pathogen proteins based on these models. 
              The sequences of pathogen proteins involved in interactions with human proteins are provided to the software, 
              which detects the presence of domains and/or motifs. Known interaction models between two domains or a domain 
              and a motif then allow it to predict the interaction interfaces between domains present in human proteins 
              and domains or motifs present in bacterial proteins.
            </div><br><br><br>

        <div class='doc_main_titles'>III. Key Features and Resources</div>
        <br><br>

            <div class='doc_second_titles'>Core Features:</div><br>
            <div class='doc_text_content'>BactMentha website enables the user to navigate through the host-bacteria 
              protein-protein interactions providing additionnal informations such as experimental and inferred binding 
              data (for bacteria-human proteins interactions). The addition of mimicINT interfaces data have permitted to 
              increase the proportion of interactions between bacteria and human proteins with binding data from 5.6% of the 
              interactions with experimental binding data (in which less than 1.5% with data on both host and pathogen proteins 
              sides) to 14.2% of interactions with binding data (11.7% for human proteins side and 13.3% on the bacteria
              proteins side). BactMentha also provides an <a class='table_link' href='/Search' target="_blank">
              Advanced Search</a> form to filter the database, as well as the possibility to download the resulting tables 
              (including dynamic filtrations on the columns !). Finally, the complete database dataset and archives are 
              available on the <a class='table_link' href='/Download' target="_blank">Download</a> page.
            </div><br>

          <!-- USER GUIDE -->

            <div class='doc_second_titles'>Navigation and Interface (User guide):</div><br>
            <div class='doc_text_content'>
                <section class='user_guide' id="onglet">
                  <ul id="ul">
                    <li><a id="ong_home" href="#onglet" class="active">Home</a></li>
                    <li><a id="ong_advSearch" href="#onglet">Advanced Search</a></li>
                    <li><a id="ong_browse" href="#onglet">Browse database</a></li>
                    <li><a id="ong_download" href="#onglet">Download</a></li>
                    <li><a id="ong_stats" href="#onglet">Statistics</a></li>
                  </ul>
                  <div id="content"></div>
                </section>
            </div><br><br><br>

        <div class='doc_main_titles'>IV. Access and Usage</div>
        <br><br>

            <!-- <div class='doc_second_titles'>Registration and Login:</div><br> -->
            <div class='doc_text_content'>No registration or login is necessary to access BactMentha database.
            </div>

            <!-- <div class='doc_second_titles'>Data Usage:</div><br> -->
            <div class='doc_text_content'>
            <p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><span property="dct:title">BactMentha</span> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">Creative Commons Attribution 4.0 International<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt=""><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt=""></a></p> 
            </div><br><br><br>

        <div class='doc_main_titles'>V. Contributions, Funding and Collaborations</div>
        <br><br>

            <div class='doc_second_titles'>Authors contributions:</div><br>
            <div class='doc_text_content'>
            Lou Bergogne: Data Curation, Formal Analysis, Investigation, Methodology, Software, Visualization, Writing – 
            Original Draft Preparation; Mégane Boujeant: Data Curation, Investigation, Methodology, Software. Marta Iannuccelli: 
            Data Curation, Investigation; Christine Brun: Funding Acquisition, Supervision, Writing – Review & Editing. Luana Licata:  
            Data Curation, Funding Acquisition, Investigation, Supervision, Writing – Review & Editing; Andreas Zanzoni: Conceptualization, 
            Data Curation, Formal Analysis, Funding Acquisition, Investigation, Methodology, Project Administration, Supervision, Writing – 
            Original Draft Preparation, Writing – Review & Editing. 
            </div><br>
            <div class='doc_second_titles'>Funding:</div><br>
            <div class='doc_text_content'>
            This work received support from the JPI HDHL-INTIMIC action co-funded by the Agence Nationale de la Recherche 
            (ANR-17-HDIM-0001) and from the French government under the France 2030 investment plan, as part of the Initiative 
            d'Excellence d'Aix-Marseille Université - A*MIDEX (AMX-21-PEP-043). 
            </div><br><br><br>

        <div class='doc_main_titles'>V. Useful links</div>
        <br><br>
            <div class='doc_text_content'>
                BastionHub:
                <label><a class='table_link' href='https://bastionhub.erc.monash.edu/' target='_blank'>https://bastionhub.erc.monash.edu/</a></label><br>
                ELM database:
                <label><a class='table_link' href='http://elm.eu.org/' target='_blank'>http://elm.eu.org/</a></label><br>
                IMEx Consortium:
                <label><a class='table_link' href='https://www.imexconsortium.org/' target='_blank'>https://www.imexconsortium.org/</a></label><br>
                InterPro database:
                <label><a class='table_link' href='https://www.ebi.ac.uk/interpro/' target='_blank'>https://www.ebi.ac.uk/interpro/</a></label><br>
                Mimicint:
                <label><a class='table_link' href='https://mimicintweb.tagc.univ-amu.fr/' target='_blank'>https://mimicintweb.tagc.univ-amu.fr/</a></label><br>
                The Approved List of biological agents (from HSE):
                <label><a class='table_link' href='https://www.hse.gov.uk/pubns/misc208.pdf' target='_blank'>https://www.hse.gov.uk/pubns/misc208.pdf</a></label><br>
                VFDB:
                <label><a class='table_link' href='https://www.mgc.ac.cn/VFs/' target='_blank'>https://www.mgc.ac.cn/VFs/</a></label><br>
                WHO priority groups definition:
                <label><a class='table_link' href='https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed' target='_blank'>https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed</a></label><br>
            </div><br><br><br>

        <div class='doc_main_titles'>VI. Glossary</div>
        <br><br>
            <div class='doc_text_content'>
                <strong>ANR:</strong> Agence Nationale de la Recherche <br>
                <strong>AMU:</strong> Aix-Marseille Université <br>
                <strong>API:</strong> Application Programming Interface <br>
                <strong>BLAST:</strong> Basic Local Alignment Search Tool <br>
                <strong>BR:</strong> Binding regions (Experimentally detected regions of interactions) <br>
                <strong>CSV:</strong> Comma-Separated Values <br>
                <strong>EBI:</strong> European Bioinformatics Institute <br>
                <strong>ELM:</strong> Eukaryotic Linear Motif <br>
                <strong>HSE:</strong> Health and Safety Executive <br>
                <strong>HUPO:</strong> Human Proteome Organization <br>
                <strong>IMEx Consortium:</strong> International Molecular Exchange Consortium <br>
                <strong>INSERM:</strong> Institut National de la Santé et de la Recherche Médicale <br>
                <strong>JPI:</strong> Joint Programming Initiative <br>
                <strong>MESR:</strong> Ministère de l'Enseignement Supérieur et de la Recherche <br>
                <strong>MI:</strong> mimicINT interfaces (inferred regions of interactions) <br>
                <strong>NCBI:</strong> National Center for Biotechnology Information <br>
                <strong>OS:</strong> Ontology Search <br>
                <strong>PA:</strong> Protein Annotation (referring to bacterial proteins being annotated as virulence factors or effectors) <br>
                <strong>PPI:</strong> Protein-protein interaction <br>
                <strong>SLiM:</strong> Short Linear Motif <br>
                <strong>TAGC:</strong> Theories and Approaches of Genomic Complexity <br>
                <strong>VFDB:</strong> Virulence Factor DataBase <br>
                <strong>WHO:</strong> World Health Organization <br>
            </div><br><br><br>
          


    <!-- Scroll-to-top button -->
    <div class="scroll-to-top">
        <a href="#top">&#9650;</a><!-- Up arrow symbol ▲ -->
    </div>


    <script>
      $(document).ready(function() {
        ong1 = document.getElementById('ong_home');
        ong2 = document.getElementById('ong_advSearch');
        ong3 = document.getElementById('ong_browse');
        ong4 = document.getElementById('ong_download');
        ong5 = document.getElementById('ong_stats');
        content = document.getElementById('content');

        // default content is home page content
        content.innerHTML = "<table> \
                <tr><td class='guide_img_cell'><img src='/static/img/doc/home_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Navigation bar:</strong><br>\
                    Use it to navigate into the website pages.<br><br>\
                  <strong>2: Search bar:</strong><br>\
                    Enter a uniprot_ac, a taxon_id, a publication_id or a uniprot_gene to display relevant data from BactMentha.\
                    You can also redirect to the Advanced Search page for a more efficient search.<br><br>\
                  <strong>3: Taxa buttons:</strong><br>\
                    Shortcuts to go on the <i>'Browse'</i> page and display the taxon corresponding table of interactions.<br><br>\
                  <strong>4: Pages buttons:</strong><br>\
                    Shortcuts for some pages of the Website.<br><br>\
                  <strong>5: News section:</strong><br>\
                    Here, you can find informations about the last and next updates, about the studied taxa or\
                    the number of interactions identified in the BactMentha database.<br><br>\
                </td></tr>\
              </table>";

        function nonactive(){
          ong1.className = "";
          ong2.className = "";
          ong3.className = "";
          ong4.className = "";
          ong5.className = "";
        }

        function active(onglet){
          nonactive(); // nettoyage
          onglet.className="active"; // je deviens active
        }

        ong1.addEventListener("click",function(){
          content.innerHTML = "<table> \
                <tr><td class='guide_img_cell'><img src='/static/img/doc/home_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Navigation bar:</strong><br>\
                    Use it to navigate into the website pages.<br><br>\
                  <strong>2: Search bar:</strong><br>\
                    Enter a uniprot_ac, a taxon_id, a publication_id or a uniprot_gene to display relevant data from BactMentha.\
                    You can also redirect to the Advanced Search page for a more efficient search.<br><br>\
                  <strong>3: Taxa buttons:</strong><br>\
                    Shortcuts to go on the <i>'Browse'</i> page and display the taxon corresponding table of interactions.<br><br>\
                  <strong>4: Pages buttons:</strong><br>\
                    Shortcuts for some pages of the Website.<br><br>\
                  <strong>5: News section:</strong><br>\
                    Here, you can find informations about the last and next updates, about the studied taxa or\
                    the number of interactions identified in the BactMentha database.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })

        ong2.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/adv_search_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Rules and examples:</strong><br>\
                    On the left side, you can find the specificities of the advanced Search queries such as authorized formats, the\
                    different conditions or the way the disinct conditions will be joined in the resulting query to the\
                    database. You can find some example of use on the right side.<br><br>\
                  <strong>2: Condition:</strong><br>\
                    You can construct a condition using a feature in a list, associated to any condition in the list, and type\
                    some comparision element in the input field following the rules displayed in section 1.<br><br>\
                  <strong>3: Add condition:</strong><br>\
                    By clicking on <i>'Add condition'</i>, you can add a new condition to your query.<br><br>\
                  <strong>4: AND / OR:</strong><br>\
                    The conditions are joined by AND or OR clauses. Be careful on the organization of your AND and OR \
                    clauses. As explain in the rules in section 1 (left side), OR clauses are associated to the last \
                    AND clause (or first condition).<br><br>\
                  <strong>5: Bin button:</strong><br>\
                    The bin button can be used to remove a condition from the advanced search form.<br><br>\
                  <strong>6: Reset button:</strong><br>\
                    The <i>'Reset'</i> button is used to clear the advanced search form.<br><br>\
                  <strong>7: Search button:</strong><br>\
                    When you are satisfied of your query, use the <i>'Search'</i> button to send it the the database and\
                    get the resulting table.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })

        ong3.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/browse_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Table selection:</strong><br>\
                    Here are some buttons to display some specific BactMentha datasets based on the host taxa (first row\
                    of green buttons), the bacterial taxa classification (second row of orange buttons), or the available\
                    interactions annotations in BactMentha (last two buttons on third row).<br><br>\
                  <strong>2: Global table search:</strong><br>\
                    You can search for an element in all the table columns using this search bar.<br><br>\
                  <strong>3: Show/Hide Columns:</strong><br>\
                    By clicking on <i>'Show/Hide Columns'</i>, you can display the buttons to select the columns to show or hide from the table.<br><br>\
                  <strong>4: Individual column filters:</strong><br>\
                    Here you can dynamically filter in the columns one by one using some text input to search in the column values.\
                    Or you may check one or more checkbox to select only the rows of the table for which some related additionnal informations \
                    are found in the database. You can combine multiple filters.<br><br>\
                  <strong>5: Child row buttons:</strong><br>\
                    You can display some additionnal information (if available) concerning:<br>\
                     • some protein annotations (bacterial annotation only) from <a href='http://www.mgc.ac.cn/VFs/' target='_blank'>VFDB</a> and/or \
                    <a href='https://bastionhub.erc.monash.edu/index.jsp' target='_blank'>BastionHub</a> (using the yellow button in first position),<br>\
                     • the experimental binding regions that were identified for the corresponding interaction (using the green button in second position),<br>\
                     • the mimicINT predicted interfaces of interaction (using the blue button in third position).<br><br>\
                  <strong>6: Download buttons:</strong><br>\
                    You can use the <i>'CSV'</i> or <i>'Excel'</i> buttons to export the resulting table in the corresponding format. \
                    You can also download the child tables in separated files using the <i>'CSV + annotations to CSV' button</i>.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })

        ong4.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/download_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Date:</strong><br>\
                    Dates on which the corresponding archive files were generated. the <i>'latest'</i> tag correspond to the actual\
                    version of the database (archive files are generated during each database update).<br><br>\
                  <strong>2: Download:</strong><br>\
                    Click on the <i>'complete'</i> zip file to download all the archive files corresponding to a version\
                    of the database or click on the specified zip files to download only some parts of the database (specific\
                    taxa or specific data types).<br>\
                     • files ending by <strong>databasetables.zip</strong> correspond to the actual database tables that are\
                     queried to display the results on BactMentha.<br>\
                     • files ending by <strong>rawdata.zip</strong> correspond to the reference files used to create the\
                     database tables.<br>\
                     • files ending by <strong>dump.zip</strong> contain the POSTGRESQL instruction to recreate the complete database.\
                    <br><br>\
                  <strong>3: Details:</strong><br>\
                    Show or hide the list of archive files by cilcking the <i>'Show'</i> <i>'Hide'</i> buttons.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })

        ong5.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/stats_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Graphical elements:</strong><br>\
                    Some graphical elements concerning the number of identified interactions per studied taxon or about\
                    the different types of annotations are displayed by default on the page.<br><br>\
                  <strong>2: Table selection:</strong><br>\
                    Here you can select a type of statistics table to display and the wanted host taxon or taxa. The displayed \
                    table will have some of the functionnalities described in the <i>'Browse'</i> guide: some buttons to show \
                    or hide the table columns, export buttons, and dynamic search among columns or the complete table.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })

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