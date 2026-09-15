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

            <div class='doc_text_content'>BactMentha, a unified resource that integrates, through a fully automated workflow, 
              bacterial-host PPIs for three organisms (human, mouse and rat) from <a class='table_link' 
              href='https://www.imexconsortium.org/' target="_blank">IMEx consortium</a> databases, with annotations 
              from: the <a class='table_link' href='https://bastionhub.erc.monash.edu/' 
              target="_blank">BastionHub</a> resource, the <a class='table_link' href='http://www.mgc.ac.cn/VFs/' 
              target="_blank">Virulence Factor DataBase (VFDB)</a>, and clinically relevant bacterial strain 
              information from the World Health Organisation (WHO) and the UK Health and Safety Executive (HSE), through the 
              Pathogens Portal. BactMentha provides easy access to the available experimentally identified binding regions of 
              the interaction partners, which are reported as biological features in IMEx-annotated interaction data. These 
              experimental annotations are complemented with putative interaction interfaces predicted using the <a class='table_link' 
              href='https://github.com/TAGC-NetworkBiology/mimicINT' target="_blank"><i>mimic</i>INT</a> workflow and <a class='table_link' 
              href='https://github.com/google-deepmind/alphafold3' target='_blank'>AlphaFold</a>-predicted complex structure.
            </div><br><br>

        <div class='doc_main_titles'>II. Data Gathering</div>
        <br><br>

            <div class='doc_text_content'>Protein interaction data gathering and annotation rely on a computational pipeline, 
              the BactMentha workflow, which consists of three main steps: (i) the bacteria-host protein interaction data 
              collection; (ii) the functional and clinical/safety annotation of bacterial proteins and strains, respectively; 
              and (iii) the protein interaction interface prediction. In the first step, the BactMentha workflow retrieves 
              the available PPIs for three host organisms (human, mouse and rat) from the <a class='table_link' 
              href='https://www.imexconsortium.org/' target="_blank">International Molecular Exchange 
              (IMEx) Consortium</a> databases via the PSICQUIC web service. Only PPIs associated with a valid <a class='table_link'
              href='https://pubmed.ncbi.nlm.nih.gov/' target="_blank">PubMed</a> identifier are kept. Next, for each host 
              organism, the PPI data is filtered to retain only the interactions with bacterial proteins by checking the 
              taxonomy identifier of the host interacting protein. The resulting dataset is further parsed to extract relevant 
              fields including, when available, experimentally identified binding regions and mutation data impacting protein 
              interactions. In the second step, the pipeline assigns functional annotations to the bacterial proteins in the 
              BactMentha dataset by performing BLAST pairwise sequence comparisons against sequences automatically taken 
              from <a class='table_link' href='https://bastionhub.erc.monash.edu/' target="_blank">BastionHub</a>, a database 
              of translocated effectors, and in the <a class='table_link' href='http://www.mgc.ac.cn/VFs/' 
              target="_blank">Virulence Factor DataBase (VFDB)</a>. The functional association is retained only if the two 
              proteins share at least 30% sequence identity over at least 75% of alignment coverage. In the third step, 
              the BactMentha workflow integrates the mimicINT pipeline to predict putative interaction interfaces by exploiting 
              known domain-domain and SLIM-domain interaction templates. The prediction procedure is run only on the set of 
              bacteria-human interactions using default parameters and first checks whether any of the bacterial proteins 
              contain at least one domain or SLIM for which an interaction template is available. Secondly, if the interacting 
              human proteins contain the cognate domain, it infers the interaction interface. Finally, bacterial strains in 
              the dataset are annotated with available priority categories (i.e., critical, high and medium) according to the 
              2024 World Health Organization (WHO) priority pathogens list (<a class='table_link' 
              href='https://www.who.int/publications/i/item/9789240093461' target='_blank'>https://www.who.int/publications/i/item/9789240093461</a>), 
              and with hazard group classification from the 2023 Health and Safety Executive (HSE) Approved List of Biological 
              Agents (<a class='table_link' href='https://www.hse.gov.uk/pubns/misc208.pdf' target='_blank'>https://www.hse.gov.uk/pubns/misc208.pdf</a>).
            </div><br><br>

        <!-- <div class='doc_main_titles'>III. Key Features and Resources</div>
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
            </div><br> -->

          <!-- USER GUIDE -->

        <div class='doc_main_titles'>III. User guide</div><br>
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
            </div><br><br>

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
            Data Curation, Investigation; Aurélie Bergon: Software. Jaime Fernandez-Macgregor: Formal analysis; Christine Brun: 
            Funding Acquisition, Supervision, Writing – Review & Editing. Luana Licata: Data Curation, Investigation, Supervision, 
            Writing – Review & Editing; Andreas Zanzoni: Conceptualization, Data Curation, Formal Analysis, Funding Acquisition, 
            Investigation, Methodology, Project Administration, Supervision, Writing – Original Draft Preparation, Writing – 
            Review & Editing.  
            </div><br>
            <div class='doc_second_titles'>Acknowledgements:</div><br>
            <div class='doc_text_content'>
            This work was granted access to the HPC and storage resources of GENCI at IDRIS under grant 2026-AD010315212R2, 
            using the CSL and A100 partitions of the Jean Zay supercomputer.  
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
                AlphaFold:
                <label><a class='table_link' href='https://github.com/google-deepmind/alphafold3' target='_blank'>https://github.com/google-deepmind/alphafold3</a></label><br>
                BastionHub:
                <label><a class='table_link' href='https://bastionhub.erc.monash.edu/' target='_blank'>https://bastionhub.erc.monash.edu/</a></label><br>
                ELM database:
                <label><a class='table_link' href='http://elm.eu.org/' target='_blank'>http://elm.eu.org/</a></label><br>
                IMEx Consortium:
                <label><a class='table_link' href='https://www.imexconsortium.org/' target='_blank'>https://www.imexconsortium.org/</a></label><br>
                InterPro database:
                <label><a class='table_link' href='https://www.ebi.ac.uk/interpro/' target='_blank'>https://www.ebi.ac.uk/interpro/</a></label><br>
                MassiveFold:
                <label><a class='table_link' href='https://github.com/GBLille/MassiveFold' target='_blank'>https://github.com/GBLille/MassiveFold</a></label><br>
                Mimicint:
                <label><a class='table_link' href='https://mimicintweb.tagc.univ-amu.fr/' target='_blank'>https://mimicintweb.tagc.univ-amu.fr/</a></label><br>
                The Approved List of biological agents (from HSE):
                <label><a class='table_link' href='https://www.hse.gov.uk/pubns/misc208.pdf' target='_blank'>https://www.hse.gov.uk/pubns/misc208.pdf</a></label><br>
                Uniprot:
                <label><a class='table_link' href='https://www.uniprot.org/' target='_blank'>https://www.uniprot.org/</a></label><br>
                VFDB:
                <label><a class='table_link' href='https://www.mgc.ac.cn/VFs/' target='_blank'>https://www.mgc.ac.cn/VFs/</a></label><br>
                WHO priority groups definition:
                <label><a class='table_link' href='https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed' target='_blank'>https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed</a></label><br>
            </div><br><br><br>

        <div class='doc_main_titles'>VI. Glossary</div>
        <br><br>
            <div class='doc_text_content'>
                <strong>AF:</strong> AlphaFold Complexes (AlphaFold predicted structure complexes) <br>
                <strong>ANR:</strong> Agence Nationale de la Recherche <br>
                <strong>AMU:</strong> Aix-Marseille Université <br>
                <strong>API:</strong> Application Programming Interface <br>
                <strong>BLAST:</strong> Basic Local Alignment Search Tool <br>
                <strong>BR:</strong> Binding regions (Experimentally detected regions of interactions) <br>
                <strong>CC:</strong> Cellular Components (Subcellular localization taken from Uniprot) <br>
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
                    Use it to access the different sections of the BactMentha website.<br><br>\
                  <strong>2: Search bar:</strong><br>\
                    BactMentha quick search bar that accepts uniprot accession numbers, uniprot gene symbols, \
                    NCBI taxon identifiers or PubMed. You can also use the Advanced Search page more complex searches.<br><br>\
                  <strong>3: Taxa buttons:</strong><br>\
                    Shortcuts to prefiltered datasets for the three hosts present in BactMentha.<br><br>\
                  <strong>4: Pages buttons:</strong><br>\
                    Shortcuts to the Browse database section, Documentation page and the datasets Download page. <br><br>\
                  <strong>5: News section:</strong><br>\
                    It provides information about data updates and BactMentha content statistics.<br><br>\
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
        // Home page User Guide
        ong1.addEventListener("click",function(){
          content.innerHTML = "<table> \
                <tr><td class='guide_img_cell'><img src='/static/img/doc/home_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Navigation bar:</strong><br>\
                    Use it to access the different sections of the BactMentha website.<br><br>\
                  <strong>2: Search bar:</strong><br>\
                    BactMentha quick search bar that accepts uniprot accession numbers, uniprot gene symbols, \
                    NCBI taxon identifiers or PubMed. You can also use the Advanced Search page more complex searches.<br><br>\
                  <strong>3: Taxa buttons:</strong><br>\
                    Shortcuts to prefiltered datasets for the three hosts present in BactMentha.<br><br>\
                  <strong>4: Pages buttons:</strong><br>\
                    Shortcuts to the Browse database section, Documentation page and the datasets Download page. <br><br>\
                  <strong>5: News section:</strong><br>\
                    It provides information about data updates and BactMentha content statistics.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })
        // Advanced Search page User Guide
        ong2.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/adv_search_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Rules and examples:</strong><br>\
                    On the left side, you can find the details of the Advanced Search queries such as authorized formats, \
                    available conditions, and how these conditions are combined in the resulting database query. On the \
                    right side, you can find some usage examples.<br><br>\
                  <strong>2: Condition:</strong><br>\
                    You can construct a condition by selecting a feature from the list, associating it with any condition \
                    in the list, and entering a comparison value in the input field according to the rules described in \
                    Section 1.<br><br>\
                  <strong>3: Add condition:</strong><br>\
                    By clicking on <i>'Add condition'</i>, you can add a new condition to your query.<br><br>\
                  <strong>4: AND / OR:</strong><br>\
                    The conditions are combined using AND and OR operators. Pay attention to how these operators are \
                    organized. As explained in the rules in Section 1 (left side), 'OR' clauses are associated with the \
                    preceding 'AND' clause (or the first condition).<br><br>\
                  <strong>5: Bin button:</strong><br>\
                    The bin button can be used to remove a condition from the advanced search form.<br><br>\
                  <strong>6: Reset button:</strong><br>\
                    The <i>'Reset'</i> button is used to clear the advanced search form.<br><br>\
                  <strong>7: Search button:</strong><br>\
                    When you are satisfied with your query, use the 'Search' button to send it to the database and \
                    retrieve the resulting table.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })
        // Browse page User Guide
        ong3.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/browse_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Table selection:</strong><br>\
                    These buttons allow you to display specific BactMentha datasets based on host taxa \
                    (first row, green buttons), bacterial taxonomic classification (second row, orange buttons), \
                    or available interaction annotations in BactMentha (last two buttons in the third row).<br><br>\
                  <strong>2: Global table search:</strong><br>\
                    You can search for an element accross the table columns using this search bar.<br><br>\
                  <strong>3: Show/Hide Columns:</strong><br>\
                    Click <i>“Show/Hide Columns”</i> to display controls for selecting which columns to show or hide in the table.<br><br>\
                  <strong>4: Individual column filters:</strong><br>\
                    Use this section to dynamically filter individual columns by entering text to search within column values. \
                    You can also select one or more checkboxes to display only rows with additional related information \
                    available in the database. Multiple filters can be combined.<br><br>\
                  <strong>5: Child row buttons:</strong><br>\
                    These buttons allow you to display additional information (when available), including:<br>\
                     • protein annotations (bacteria only) from <a href='http://www.mgc.ac.cn/VFs/' target='_blank'>VFDB</a> \
                       and/or <a href='https://bastionhub.erc.monash.edu/index.jsp' target='_blank'>BastionHub</a> \
                       (orange button, first position),<br>\
                     • cellular components (for one or both interactors) from UniProt subcellular localization \
                       (yellow button, second position),<br>\
                     • experimentally identified binding regions for the corresponding interaction (green button, third position),<br>\
                     • <i>mimic</i>INT-predicted interaction interfaces (dark turquoise button, fourth position),<br>\
                     • AlphaFold predicted structure complexes data (blue button, last position).<br> \
                  A grey button means the data is not available for the corresponding interaction.<br><br>\
                  <strong>6: MI Score:</strong><br>\
                    The <strong>MI score</strong> provided by BactMentha is retrieved directly from IMEx Consortium data. \
                    Its value is calculated taking into account three factors : the detection method, the interaction type, \
                    and the number of publications that describe the given interaction. For more details, see the <a class='table_link' \
                    href='https://www.ebi.ac.uk/intact/documentation/user-guide#interaction_scoring' target='_blank'>Intact Interaction \
                    Scoring documentation</a>. This score can be used to select “high-confidence” interactions. In a <a class='table_link' \
                    href='https://doi.org/10.1093/database/bau131'>paper \ published in Database in 2015</a> (PMID: 25652942) the authors \
                    state that 'the optimal cutoff value for MI score is 0.485 (which is close to the heuristic cutoff of 0.45 proposed \
                    by IntAct)'.<br><br> \
                  <strong>7: Cellular components subtable:</strong><br>\
                    The cellular component value and the associated id are taken from UniProt (mapped to Gene Ontology (GO) terms) and \
                    inform on the proteins subcellular localizations in the cell, The topology value, available only for membrane-spanning \
                    proteins, describes further the topological class or type of the given membrane protein.<br><br>\
                  <strong>8: Download buttons:</strong><br>\
                    Use the <i>'CSV'</i> or <i>'Excel'</i> buttons to export the resulting table in the corresponding format. \
                    You can also download the child tables in 5 additionnal separate files using the <i>'CSV + annotations to CSV' button</i>.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })
        // Download page User Guide
        ong4.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/download_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Date:</strong><br>\
                    Indicates the dates on which the database files were update. It is the date of the last \
                    download of the IMEx files. The “latest” tag corresponds to the current database version \
                    (archive files are generated at each database update).<br><br>\
                  <strong>2: Download:</strong><br>\
                    Click on the 'complete' zip file to download all the archive files corresponding to a version \
                    of the database or click on the specified zip files to download only parts of the database \
                    (specific taxa or data types).<br>\
                     • The actual database tables queried to display results in BactMentha.<br>\
                     • The reference files used to generate the database tables.<br>\
                     • The dump file containing the PostgreSQL instructions needed to recreate the complete database.\
                    <br><br>\
                  <strong>3: Details:</strong><br>\
                    Click the <i>“Show”</i> and <i>“Hide”</i> buttons to display or hide the list of archive files \
                    under the complete archive.<br><br>\
                </td></tr>\
              </table>";
          active(this);
        })
        // Statistics page User Guide
        ong5.addEventListener("click",function(){
          content.innerHTML = "<table>\
                <tr><td class='guide_img_cell'><img src='/static/img/doc/stats_page.png' class='guide_img'></td>\
                <td class='guide_text_cell'>\
                  <strong>1: Plots:</strong><br>\
                    They show the number of identified interactions per studied taxon, as well as the different \
                    types of annotations, are displayed by default on the page. <br><br>\
                  <strong>2: Table selection:</strong><br>\
                    Here you can select a type of statistics table to display, as well as the desired host taxon \
                    or taxa. The displayed table includes some of the functionalities described in the “Browse” \
                    guide, such as buttons to show or hide table columns, export buttons, and dynamic search \
                    within individual columns or across the entire table.<br><br>\
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