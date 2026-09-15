<?php
    // ____________________
    // START OF THE SESSION
    if (empty($_SESSION)) {session_start(); }   // if first page (without refresh)
    error_reporting(E_ERROR | E_PARSE);         // to avoid some warning messages

    if(isset($_GET['reset'])) {                // if the reset button is pressed
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
    <title>Contact-BactMentha</title> 
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
        <li><a href="/FAQ">F.A.Q.</a></li>
        <li><a class='current_page' href="/Contact">Contact</a></li>
      </ul>
    </li>     
  </ul>
</nav>
<!--=================================================================== MAIN ====================================================================-->
    <main class="contact_main">
<!------------------------------------------------------------------ FIRST FORM ------------------------------------------------------------------->
      <div style='display:none;'> <!-- this form isn't displayed as it can't be tested in local -->

        <!-- Title of the form -->
        <h2 class="home_news_title1">For any question or comment, feel free to contact us :</h2>
        <div class='contact_title_bar'></div><br><br>

        <!-- The form itself -->
        <form method="POST" class="contact_form">
          <!-- Name input -->
          <label class="contact_form_element">Your Name</label><br>
          <input class="contact_form_element" type="text" id="name" name="name" placeholder="Jane Doe" required /><br><br>
          <!-- Email input -->
          <label class="contact_form_element">Your Email Address</label><br>
          <input class="contact_form_element" type="email" id="email" name="email" placeholder="jane.doe@gmail.com" required /><br><br>
          <!-- Subject input -->
          <label class="contact_form_element">Your mail subject</label><br>
          <input class="contact_form_element" type="text" id="subject" name="subject" placeholder="Question about..." required /><br><br>
          <!-- Message text area -->
          <label class="contact_form_element">Your Message</label><br>
          <textarea name="message" id="message" cols="100" rows="10" placeholder="Your Message here..."  required ></textarea><br><br>
          <!-- Submit the form -->
          <input type='submit' name='send' value='Send' class='bm_button'><br>
        </form><br>
        <!-- Reset the page -->
        <form method="POST" class="contact_form">
          <input type='submit' name='reset' value='Reset' class='bm_button'><br>
        </form><br>
      </div> <!-- End of the div not displayed -->

<!------------------------------------------------------------------ SECOND FORM ------------------------------------------------------------------>
        <!-- Title of the contact page -->
        <h2 class="home_news_title1">Contact Us</h2>
        <div class='contact_title_bar'></div><br><br><br><br>

        <!-- First line wrapper -->
        <div class="contact_page_wrapper">

            <!-- People involved div -->
            <div class="contact_page_subdiv_people_involved">
                <div class="contact_centered_subtitles">Main People involved:</div><br>
                  
                <div class="centered_text_content"><br>
                    BERGOGNE Lou<br><br><br>
                    BOUJEANT Mégane<br><br><br>
                    IANNUCCELLI Marta<br><br><br>
                    LICATA Luana<br><br><br>
                    ZANZONI Andreas
                </div><br><br>
            </div>

            <!-- Contact for curation or technical issues -->
            <div class="contact_page_subdiv_contact_issue">
                <div class="contact_centered_subtitles">For any curation issue, please contact :</div><br><br>
                  
                <div class="centered_text_content">
                    ZANZONI Andreas:<br>
                    <label class="email" data-user="andreas.zanzoni" data-domain="univ-amu.fr"></label>
                    <img src='/static/img/buttons/copy-icon.svg' class='CopyEmailImg' title='Copy email'>
                    <br><br><br>
                    LICATA Luana:<br>
                    <label class="email" data-user="luana.licata" data-domain="uniroma2.it"></label>
                    <img src='/static/img/buttons/copy-icon.svg' class='CopyEmailImg' title='Copy email'>
                    <br>
                </div><br><br><br>

                <div class="contact_centered_subtitles">For any technical issue, please contact :</div><br><br>
                  
                <div class="centered_text_content">
                    Lou BERGOGNE:<br>
                    <label class="email" data-user="lou.bergogne" data-domain="univ-amu.fr"></label>
                    <img src='/static/img/buttons/copy-icon.svg' class='CopyEmailImg' title='Copy email'>
                    <br>
                </div><br><br>
            </div>

            <!-- Tools and ressources -->
            <div class="contact_page_subdiv_tools">
                <div class="contact_centered_subtitles">Tools and resources used by BactMentha :</div><br>
                AlphaFold:
                <label><a class='table_link' href='https://github.com/google-deepmind/alphafold3' target='_blank'>https://github.com/google-deepmind/alphafold3</a></label><br><br>
                BastionHub:
                <label><a class='table_link' href='https://bastionhub.erc.monash.edu/' target='_blank'>https://bastionhub.erc.monash.edu/</a></label><br><br>
                ELM database:
                <label><a class='table_link' href='http://elm.eu.org/' target='_blank'>http://elm.eu.org/</a></label><br><br>
                IMEx Consortium:
                <label><a class='table_link' href='https://www.imexconsortium.org/' target='_blank'>https://www.imexconsortium.org/</a></label><br><br>
                InterPro database:
                <label><a class='table_link' href='https://www.ebi.ac.uk/interpro/' target='_blank'>https://www.ebi.ac.uk/interpro/</a></label><br><br>
                MassiveFold:
                <label><a class='table_link' href='https://github.com/GBLille/MassiveFold' target='_blank'>https://github.com/GBLille/MassiveFold</a></label><br><br>
                Mimicint:
                <label><a class='table_link' href='https://mimicintweb.tagc.univ-amu.fr/' target='_blank'>https://mimicintweb.tagc.univ-amu.fr/</a></label><br><br>
                The Approved List of biological agents (from HSE):
                <label><a class='table_link' href='https://www.hse.gov.uk/pubns/misc208.pdf' target='_blank'>https://www.hse.gov.uk/pubns/misc208.pdf</a></label><br><br>
                Uniprot:
                <label><a class='table_link' href='https://www.uniprot.org/' target='_blank'>https://www.uniprot.org/</a></label><br><br>
                VFDB:
                <label><a class='table_link' href='https://www.mgc.ac.cn/VFs/' target='_blank'>https://www.mgc.ac.cn/VFs/</a></label><br><br>
                WHO priority groups definition:
                <label><a class='table_link' href='https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed' target='_blank'>https://www.who.int/en/news-room/detail/27-02-2017-who-publishes-list-of-bacteria-for-which-new-antibiotics-are-urgently-needed</a></label><br>
                <br><br>
            </div>

        </div> <!-- End of the first contact_page_wrapper_div -->

        <br>

        <!-- How to cite bactmentha -->
        <!--<div class="contact_page_subdiv_citation">
            <div class="contact_centered_subtitles">How to cite BactMentha ?</div><br>
              
            <div class="centered_text_content">
                If you are using BactMentha in your work, please cite ...
            </div><br><br>
        </div>-->

        <br><br><br><br><br>

        <!-- Scroll-to-top button (Up arrow symbol ▲) -->
        <div class="scroll-to-top">
            <a href="#top">
                &#9650;
            </a>
        </div>

<!-------------------------------------------------------------- PHP FOR FIRST FORM --------------------------------------------------------------->
<?php // this has not be tested (because impossible in local)

    if (isset($_GET['send'])) {
      // verify if all required fields are completed
      if (!isset($_GET['name']) ||
          !isset($_GET['email']) ||
          !isset($_GET['subject']) ||
          !isset($_GET['message'])) {
            echo "There seem to be some problem with your form, please complete all the fields before sending.";
          }
      else { // if all fields are completed
        $name = $_GET['name']; // required
        $email = $_GET['email']; // required
        $subject = $_GET['subject']; // required
        $message = $_GET['message']; // required
      }
    }

    // if all fields are completed
    if (isset($email)) {

        // REPLACE THIS 2 LINES AS YOU DESIRE
        $email_to = "lou.bergogne@univ-amu.fr";
        $email_subject = "BactMentha-submission: ".$subject ;

        function problem($error)
        {
            echo "Oh looks like there is some problem with your form data: <br><br>";
            echo $error . "<br><br>";
            echo "Please fix those to proceed.<br><br>";
            die();
        }

        $error_message = "";
        $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';

        if (!preg_match($email_exp, $email)) {
            $error_message .= 'Email address does not seem valid.<br>';
        }

        $string_exp = "/^[A-Za-z .'-]+$/";

        if (!preg_match($string_exp, $name)) {
            $error_message .= 'Name does not seem valid.<br>';
        }

        if (strlen($message) < 2) {
            $error_message .= 'Message should not be less than 2 characters<br>';
        }

        if (strlen($error_message) > 0) {
            problem($error_message);
        }

        $email_message = "Form details following:\n\n";

        function clean_string($string)
        {
            $bad = array("content-type", "bcc:", "to:", "cc:", "href");
            return str_replace($bad, "", $string);
        }

        $email_message .= "Name: " . clean_string($name) . "\n";
        $email_message .= "Email: " . clean_string($email) . "\n";
        $email_message .= "Message: " . clean_string($message) . "\n";

        // create email headers
        $headers = 'From: ' . $email . "\r\n" .
            'Reply-To: ' . $email . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        @mail($email_to, $email_subject, $email_message, $headers);
    

        // Thanks message after success

        echo "Thanks for contacting us, we will get back to you as soon as possible.<br><br>";
    }

?>
<!------------------------------------------------------------------- JS SCRIPT ------------------------------------------------------------------->
<script>

document.querySelectorAll(".email").forEach(function(email) {
    email.textContent = email.dataset.user + "@" + email.dataset.domain;
});

function CopyToClipboard(element) {
    // Find the nearest preceding <label> sibling and get its text
    const email = $(element).prev('label').text().trim();
    // Create a new textarea element
    const textarea = $('<textarea/>', {
        style: 'position: absolute; top: -9999px;',
    });
    // Append the textarea to the body
    $('body').append(textarea);
    // Set the textarea value to the email text
    textarea.val(email).select();
    // Copy the text to the clipboard
    document.execCommand('copy');
    // Remove the textarea
    textarea.remove();
}

    $(document).ready(function() {

        // Copy the email when clicking on the image
        $(".CopyEmailImg").on('click', function() {
          CopyToClipboard(this);
        });

        // Hide the scroll-to-top button initially
        $('.scroll-to-top').hide();

        // Show/hide the scroll-to-top button based on the user's scrolling position
        $(window).scroll(function () {            // When the user scroll
            if ($(this).scrollTop() > 100) {      // compare to a threshold (adjust as needed)
                $('.scroll-to-top').fadeIn();     // Make the arrow appear
            } else {
                $('.scroll-to-top').fadeOut();    // Make the arrow appear
            }
        });

        // Smooth scroll to the top of the page when the "scroll-to-top" button is clicked
        $('.scroll-to-top').on('click', function (event) {
            event.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 'slow');
        });                                       // end of scroll-to-top onclick function

    });                                           // end of the document ready function

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