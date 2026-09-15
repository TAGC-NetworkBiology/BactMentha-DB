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
              <div class="Guide_content_button unclicked_button_faq">Any question? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  We will update this page with the most frequently asked questions and answers.
                  If you don't find any answer to your question here, you can <a class='table_link' 
                  href='/Contact' target="_blank">contact us</a> !
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Where do BactMentha data come from? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  BactMentha data come from multiple databases. First protein-protein interaction data 
                  are taken from <a class='table_link' href='https://www.imexconsortium.org/' target="_blank">
                  IMEx Consortium</a> databases for each host taxon. Complementary informations for 
                  interacting proteins are taken from <a class='table_link' href='https://www.uniprot.org/' 
                  target="_blank">Uniprot</a>. Moreover, <a class='table_link' href='https://www.ncbi.nlm.nih.gov/' 
                  target="_blank">NCBI</a> blastp has been used against <a class='table_link' 
                  href='http://www.mgc.ac.cn/VFs/' target="_blank">Virulence Factor Database (VFDB)
                  </a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' target="_blank">
                  BastionHub</a> sequences to annotate the bacterial proteins. Information related to 
                  detection method, the interaction type and the PubMed ID are also retrieved. Finally, 
                  binding interfaces are inferred via the <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
                  target="_blank">mimicINT</a> workflow and interacting pair complex structure, when 
                  available, have been predicted using <a class='table_link' href='https://github.com/google-deepmind/alphafold3' 
                  target='_blank'>AlphaFold3</a> via <a class='table_link' href='https://github.com/GBLille/MassiveFold' 
                  target="_blank">MassiveFold</a>.
                  <br><br>
              </div> 
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What do PA, CC, BR, MI and AF mean? 
                Where does the information come from? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  <strong>PA</strong> or <strong>'Protein Annotation'</strong> is related to the bacterial 
                  annotations available in the BactMentha Database. Those information come from 
                  <a class='table_link' href='http://www.mgc.ac.cn/VFs/' target="_blank">Virulence 
                  Factor Database (VFDB)</a> and <a class='table_link' href='https://bastionhub.erc.monash.edu/' 
                  target="_blank">BastionHub</a>.
                  <strong>CC</strong> or <strong>'Cellular Component'</strong>  
                  refers to localization annotations of either the bacterial or the human protein, or 
                  both. The available annotations of subcellular localization are taken from <a class='table_link' 
                  href='https://www.uniprot.org/' target="_blank">Uniprot</a>. The annotation can be 
                  'exact' if it corresponds directly to the protein or 'canonical fallback' when the 
                  annotation is transferred to isoforms.
                  <strong>BR</strong> or <strong>'Binding Regions'</strong> 
                  refers to the known binding regions between two interactors (detected experimentally). 
                  This information is taken directly from the <a class='table_link' href='https://www.imexconsortium.org/' 
                  target="_blank">IMEx Consortium</a> databases.
                  <strong>MI</strong> or <strong>'mimicINT 
                  Interfaces'</strong> refers to the predicted binding interfaces between two interactors. 
                  These annotations are generated by the <a class='table_link' href='https://github.com/TAGC-NetworkBiology/mimicINT' 
                  target="_blank">mimicINT</a> workflow.
                  Finally, <strong>AF</strong> or <strong>'AlphaFold 
                  complexes'</strong> refers to only bacteria-human interactions, with bacterial proteins 
                  having PA annotations, for which complex structures have been predicted using <a class='table_link' 
                  href='https://github.com/google-deepmind/alphafold3' target='_blank'>AlphaFold3</a> 
                  via <a class='table_link' href='https://github.com/GBLille/MassiveFold' target="_blank">
                  MassiveFold</a>.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can I download annotations subtables 
                data? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  Yes, you can download them via the buttons located at the upper right corner of the 
                  browse page. The <strong>'Show/Hide columns'</strong> button allows you to mask or 
                  display columns on the current result table. The <strong>'CSV + annotations to CSV'</strong>
                  button allows you to download the current table (e.g., after filtering in the headers 
                  and selecting columns of your interest) and the annotation tables (bacterial protein 
                  annotations, cellular components, binding regions, mimicINT interfaces and AF3 predictions) 
                  for the corresponding interactions in the filtered table. The <strong>'CSV'</strong> 
                  button allows you to download only the current filtered table (without the additionnal 
                  annotation tables). Finally, the <strong>'Excel'</strong> button permits also to 
                  download the current table only in an Excel format file.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What is the unit for 'start' and 
                'end' in the binding regions and mimicINT interfaces tables? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  The unit corresponds to amino acid positions. For example, for this Binding Region 
                  table: <br><br>
                  <table class='display stripe child_table_BR' style="font-size:9px;"><tr><th>Identifier A</th><th>Feature A</th><th>Feature start A</th>
                  <th>Feature end A</th><th>Identifier B</th><th>Feature B</th><th>Feature start B</th><th>Feature end B</th></tr>
                  <tr><td>Q8X482</td><td>binding-associated region</td><td>221</td><td>314</td><td>O08816</td><td>binding-associated region</td>
                  <td>193</td><td>501</td></tr></table><br>
                  There is binding region information for the interaction between the bacterial protein 
                  (A columns) Q8X482 and the host protein (B columns) O08816. The experimentally detected 
                  binding region in protein Q8X482 spans from the amino acid position 221 to the amino 
                  acid position 314, and in the protein O08816 it spans from the position 193 to the 
                  position 501.<br> This applies to mimicINT Interface tables as well.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What is the difference between 
                the interaction types (physical, direct, etc.)? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  BactMentha adheres to the <a class='table_link' href='https://www.psidev.info/molecular-interactions' 
                  target="_blank">HUPO Proteomics Standards Initiative on Molecular Interactions</a> 
                  guidelines. You can find the Molecular Interaction ontology term definitions <a class='table_link' 
                  href='https://www.ebi.ac.uk/ols4/ontologies/mi/classes/http%253A%252F%252Fpurl.obolibrary.org%252Fobo%252FMI_0116?lang=en' 
                  target="_blank">here</a>.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">What is the MI score? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  The <strong>MI score</strong> provided by BactMentha is retrieved directly from IMEx 
                  Consortium data. Its value is calculated taking into account three factors : the detection 
                  method, the interaction type, and the number of publications that describe the given 
                  interaction. For more details, see the <a class='table_link' href='https://www.ebi.ac.uk/intact/documentation/user-guide#interaction_scoring' 
                  target="_blank">Intact Interaction Scoring documentation</a>. This score can be used 
                  to select “high-confidence” interactions. In a <a class='table_link' 
                  href='https://doi.org/10.1093/database/bau131'>paper published in Database in 2015</a> 
                  (PMID: 25652942) the authors state that "the optimal cutoff value for MI score is 0.485 
                  (which is close to the heuristic cutoff of 0.45 proposed by IntAct)".
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Can I create an advance search 
                query based on the WHO priority list? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  Yes, you can add a condition based on the WHO priority level or the Hazard group 
                  of the bacterial pathogen in the <a  class='table_link' href='/Search' target="_blank">
                  Advanced Search</a> form.
                  <br><br>
              </div>
          </div><br><br>

          <div class="Guide_title">
              <div class='guide_bar'></div>
              <div class="Guide_content_button unclicked_button_faq">Why some pathogens present in the 
                Hazard group 3 are not in the WHO priority groups? &#9660</div>
              <div class='guide_bar'></div>
              <div class="Guide_content"><br>
                  WHO priority groups aims to guide/promote research of new antibiotics for multidrug 
                  resistant bacteria, which are, in turn, listed as "priority pathogens". On the other 
                  side, pathogens are classified into an hazard group depending on their pathogenicity 
                  to human, risk of infections, spread potential and availability of effective vaccine 
                  or treatment. 
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