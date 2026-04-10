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
    <title>F.A.Q.-BactMentha</title> 
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
        <li><a href="/Documentation">Documentation</a></li>
        <li><a class='current_page'>F.A.Q.</a></li>
        <li><a href="/Contact">Contact</a></li>
      </ul>
    </li>     
  </ul>
</nav>
<!--=================================================================== MAIN ====================================================================-->
<main>
        <h2 class="home_news_title1">Welcome on Bactmentha F.A.Q. !</h2>
        <div class='home_title_bar'></div><br><br>

        <div class='user_guide'>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Any question ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  We will update this page with the most frequently asked questions and answers.<br>
                  If you don't find any answer to your question here, you can <a class='table_link' href='/Contact' 
                  target="_blank">contact us</a> !
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Where do BactMentha data come from ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  BactMentha data come from multiple databases. First protein-protein interaction data are taken from <a class='table_link' 
                  href='https://www.imexconsortium.org/' target="_blank">IMEx Consortium</a> for each host taxon. A lot of complementary
                  informations concerning the interacting proteins are taken from <a class='table_link' href='https://www.uniprot.org/' 
                  target="_blank">Uniprot</a>. Moreover, <a class='table_link' href='https://www.ncbi.nlm.nih.gov/' target="_blank">NCBI</a> 
                  blastp has been used against <a class='table_link' href='http://www.mgc.ac.cn/VFs/' target="_blank">Virulence Factor Database 
                  (VFDB)</a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' target="_blank">BastionHub</a> content to annotate 
                  the bacterial proteins. Some informations such has the detection method, the interaction type and the PubMed ID are also retrieved. 
                  Finally, binding interfaces are inferred in addition to experimentally detected binding regions thanks to the <a class='table_link' 
                  href='https://github.com/TAGC-NetworkBiology/mimicINT' target="_blank">mimicINT</a> tool.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can I search giving a list of IDs ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  You can use the <a class='table_link' href='/Search' target="_blank">Advanced Search</a> to create custom 
                  search query, using a list of values separed by a comma. To do that, you can use the operators 'like (..., ..., ...)' or 
                  'not like (..., ..., ...)'. For example, you can create a custom query by first selecting <a class='table_link'>(bacterial) 
                  Uniprot AC</a>, then select <a class='table_link'>like (..., ..., ...)</a>, and finally type in the search bar the <a class='table_link' 
                  href='https://www.uniprot.org/' target="_blank">Uniprot</a> identifiers like this: <a class='table_link'>P0ABE7, Q8CZU2, A0A6L8PTK5</a>.
                  This will allows you to search for interactions that involve bacterial proteins corresponding to these Uniprot accession numbers.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What does PR, BR and MI means ? Where do the informations come from &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  <strong>PA</strong> or <strong>'Protein Annotation'</strong> concern the bacterial annotation available in BactMentha Database. 
                  Those informations come from <a class='table_link' href='http://www.mgc.ac.cn/VFs/' target="_blank">Virulence Factor Database 
                  (VFDB)</a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' target="_blank">BastionHub</a>.<br>
                  <strong>BR</strong> or <strong>'Binding Regions'</strong> concern the known binding regions between two interactors (detected
                  experimentaly). Those informations are taken directly from the publications.<br>
                  <strong>MI</strong> or <strong>'mimicINT Interface'</strong> concern the predicted binding interfaces between two interactors. 
                  Those informations are produced by the prediction tool <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
                  target="_blank">mimicINT</a>.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can I download PA, BR and MI annotations ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  When the table is displayed after your search, you can see some buttons at the upper right corner. <br>
                  The <strong>'Show/Hide columns'</strong> button allows you to mask or display some columns on the current resulting table.
                  The second, button, <strong>'CSV + annotations to CSV'</strong> allows you to download the current table (after filtering 
                  in the headers for exemple) and the annotations tables (bacterial protein annotations, binding regions and mimicINT interfaces)
                  for the corresponding interactions in the filtered table. The <strong>'CSV'</strong> button allows you to download only the current 
                  filtered table (without the additionnal annotations tables). Finally, the <strong>'Excel'</strong> button permits also to download 
                  only the current table in an Excel format.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What is the unit for 'start' and 'end' in mimicINT interfaces and binding regions 
                  tables ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  The units are in base pairs. For exemple for with this Binding Region table: <br><br>
                  <table class='display stripe child_table_green'><tr><th>Identifier A</th><th>Feature A</th><th>Feature start A</th>
                  <th>Feature end A</th><th>Identifier B</th><th>Feature B</th><th>Feature start B</th><th>Feature end B</th></tr>
                  <tr><td>Q8X482</td><td>binding-associated region</td><td>221</td><td>314</td><td>O08816</td><td>binding-associated region</td>
                  <td>193</td><td>501</td></tr></table><br>
                  We can see that there is an interaction between the bacterial protein (always in A columns) <a class='table_link'>Q8X482</a> and the 
                  host protein (always in B columns) <a class='table_link'>O08816</a>.<br>The detected binding region seem to bury the protein Q8X482 
                  from the amino acid position <a class='table_link'>221</a> to the amino acid position <a class='table_link'>314</a>, and the protein 
                  O08816 from the position <a class='table_link'>193</a> to the position <a class='table_link'>501</a>.<br><br> The units used for mimicINT
                  Interfaces tables are the same, at the only difference that the interfaces are inferred instead of being found in the literature.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What is the difference between the interaction types (physical/direct 
                  interactions, ...) ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  BactMentha follows the <a class='table_link' href='https://www.psidev.info/molecular-interactions' target="_blank">HUPO Proteomics 
                  Standards Initiative on Molecular Interactions</a>. You can find the tree view and definitions of the different terms <a class='table_link' 
                  href='https://www.ebi.ac.uk/ols4/ontologies/mi/classes/http%253A%252F%252Fpurl.obolibrary.org%252Fobo%252FMI_0116?lang=en' 
                  target="_blank">here</a>.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Which confidence score is significant in those interactions ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  The confidence score used on BatcMentha is not a significance score but an heuristic score based on three factors : the detection method, 
                  the interaction type, and the number of publication that catalog this interaction. For more information see 
                  the <a class='table_link' href='https://www.ebi.ac.uk/intact/documentation/user-guide#interaction_scoring' target="_blank">Intact 
                  Interaction Scoring documentation</a>.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can we know which part of the protein interacts (domain / slim...) ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  The percentage of BactMentha interactions for which a Binding Region has been described in the litterature for at least one of the two 
                  interactors is about 1.3% in February 2024. Adding the mimicINT interfaces inference, the number of interactions for which we have the 
                  positions of either a binding region or an interface is around 6.13% of BactMentha interations. That represent an icrease of more than 470%. 
                  Unfortunaly, except for those interactions that enables you to refer to some database such as <a  class='table_link' href='http://pfam.xfam.org/' 
                  target="_blank">Pfam</a>, <a  class='table_link' href='http://elm.eu.org/searchdb.html' target="_blank">ELM</a> or <a  class='table_link' 
                  href='https://www.ebi.ac.uk/interpro/' target="_blank">InterPro</a> (based on the given positions or interfaces), it is not possible to give 
                  more details on the way the interactions occur than the ones given in the table based on curation of the literature.  
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can I create an advanced search based on WHO priority ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  Yes, you can add a condition based on the WHO priority level or the Hazard group of the bacterial pathogen in 
                  the <a  class='table_link' href='/Search' target="_blank">Advanced Search</a> form.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Why are some pathogens in hazard group 3 not concidered as who priority ? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  WHO priority groups aims to guide/promote research of new antibiotics for multidrug resistant bacteria so it
                  consists in a list of antibiotic-resistant "priority pathogens". On the other side, pathogens are classified
                  into an hazard group depending on their level of risk of infections to humans and the availability of a 
                  vaccine or treatment.
                  <br><br>
              </div>
          </div><br><br>

        </div>

    <!-- Scroll-to-top button -->
    <div class="scroll-to-top">
        <a href="#top">
            &#9650; <!-- Up arrow symbol ▲ -->
        </a>
    </div>


    <script>
      $(document).ready(function() {
          $('.Guide_content_button').on('click', function(){
              $(this).parent().find('.Guide_content').toggle();
              if ($(this).hasClass('clicked_button_faq')) {
                  $(this).removeClass('clicked_button_faq');
                  $(this).addClass('unclicked_button_faq');
              } else if ($(this).hasClass('unclicked_button_faq')) {
                  $(this).removeClass('unclicked_button_faq');
                  $(this).addClass('clicked_button_faq');
              };
          });

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