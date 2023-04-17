<?php

// Google reCAPTCHA API keys settings
$secretKey = '6LcNWnQjAAAAAFMSRR2HweugzY_ETl7EmfjKpLA3';

// Email settings
$recipientEmail = 'info@salvatorecasalino.it';

// Assign default values
$postData = $valErr = $statusMsg = '';
$status = 'error';

// If the form is submitted
if (isset($_POST['submit_frm'])) {
    // Retrieve value from the form input fields
    $postData = $_POST;
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Validate input fields
    if (empty($name)) {
        $valErr .= '<br>Il campo "Nome completo" non puà essere vuoto.<br />';
    }
    if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $valErr .= 'Inserisci una mail corretta.<br />';
    }
    if (empty($message)) {
        $valErr .= 'Inserisci un messaggio.<br />';
    }

    // Check whether submitted input data is valid
    if (empty($valErr)) {
        // Validate reCAPTCHA response
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {

            // Google reCAPTCHA verification API Request
            $api_url = 'https://www.google.com/recaptcha/api/siteverify';
            $resq_data = array(
                'secret' => $secretKey,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            );

            $curlConfig = array(
            CURLOPT_URL => $api_url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $resq_data
            );

            $ch = curl_init();
            curl_setopt_array($ch, $curlConfig);
            $response = curl_exec($ch);
            curl_close($ch);

            // Decode JSON data of API response in array
            $responseData = json_decode($response);

            // If the reCAPTCHA API response is valid
            if ($responseData->success) {
                // Send email notification to the site admin
                $to = $recipientEmail;
                $subject = 'Apparabita - Nuova mail';
                $htmlContent = "
                <h4>Nuovo messaggio da Apparabita</h4>
                <p><b>Nome: </b>" . $name . "</p>
                <p><b>Email: </b>" . $email . "</p>
                <p><b>Messaggio: </b>" . $message . "</p>
                ";

                // Always set content-type when sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                // Sender info header
                $headers .= 'From:' . $name . ' <' . $email . '>' . "\r\n";
                $status = 'success';
                $statusMsg = 'Grazie! Il tuo messaggio è stato inviato.';
                $postData = '';
                mail($to, $subject, $htmlContent, $headers);
            } else {
                $statusMsg = 'The reCAPTCHA verification failed, please try again.';
            }
        } else {
            $statusMsg = 'Si è verificato un\'errore! Riprova più tardi.';
        }
    } else {
        $valErr = !empty($valErr) ? '<br/>' .
            trim($valErr, '<br/>') : '';
        $statusMsg = $valErr;
    }
}

?>