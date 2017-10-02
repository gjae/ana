<?php
define('DS', DIRECTORY_SEPARATOR);
//defined('DS') or die('Error');

//options parameter for user

//if you want to save the contact informations in csv then change the below params to true, by default it's false
// the csv file location is /php/contacts.csv
$save_in_csv = false;

$admin_email_to         = 'correo_contacto'; // admin email who will get the contact email alert
$admin_email_to_name    = "SYNERGY BUSINESS SOLUTIONS"; // Admin Name/Company name who will get the email alert
$admin_email_from       = 'correo_de_envio';  // admin email from which email address email will be sent
$admin_email_from_name  = 'www.consultingsbs.com'; //admin name from which email will be sent
$admin_send_subject     = 'Mensaje de alerta de contacto'; //email subject what the admin will get as contact email alert
$user_send_subject      = 'Gracias por contactarnos. Este es tu copia del correo'; //email subject what the user will get if the user agreed or select "copy me"

//end options parameter for user



$list = array();
$validation_message = array(
    'error' => false,
    'error_field' => array(),
    'message' => array()
);

$rules = array(
    'cbxname' => 'trim|required|alpha_spaces',
    'cbxemail' => 'trim|required|email',
    'cbxphone' => 'trim|numeric',
    'cbxmessage' => 'trim|required|alpha_numeric_spaces',
);

if ($_POST) {
    require_once(__DIR__.DS.'class.validation.php');
    $frm_val = new validation;

    foreach ($rules as $post_key => $rule) {
        $frm_val->validate($post_key, $rule);
    }

    $validation_info = $frm_val->validation_info();
    $validation_message['error'] = !$validation_info['validation'];

    foreach ($validation_info['error_list'] as $error_field => $message) {
        $validation_message['error_field'][] = $error_field;
        $validation_message['message'][$error_field] = $message;
    }

    $cbxname        = $frm_val->get_value('cbxname');
    //var_dump($cbxname);
    $cbxemail       = $frm_val->get_value('cbxemail');

       $cbxphone      = $frm_val->get_value('cbxphone');
    ///var_dump($cbxemail);
    $cbxsendme      = isset( $_POST['cbxsendme'] ) ? 'on' : '';

    ///var_dump($cbxsendme);
    $cbxmessage     = $frm_val->get_value('cbxmessage');
    //var_dump($cbxmessage);
    //exit();


    //if save in csv true
    if ($save_in_csv && $validation_info['validation']) {
        $list[] = $cbxname;
        $list[] = $cbxemail;
        $list[] = $cbxphone;
        $list[] = $cbxmessage;
        $fp = fopen('contacts.csv', 'a');
        fputcsv($fp, $list);
        fclose($fp);
    }
    //end if save is csv true
   // $validation_message['message'] = 'This is success Process';
    //send email

    if($validation_info['validation']){
        //now prepare for sending email

        //include the php emailer library
        require 'phpmailer/PHPMailerAutoload.php';

        //create an instance of phpmailer class
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->Username = 'correo@electronico.com';
        $mail->Password = 'clave';
        //some config if you need help based on your server configuration
       // $mail->Host = 'localhost';  // Specify main and backup SMTP servers

        // $mail->Username = 'user@example.com';                 // SMTP username
        // $mail->Password = 'secret';                           // SMTP password
        // $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        //$mail->Port = 25;                                    // TCP port to connect to

        //add admin from email
        $mail->From = $admin_email_from;
        //add admin from name
        $mail->FromName = $admin_email_from_name;
        //add admin to email and name
        $mail->addAddress($admin_email_to ,$admin_email_to_name);

        //add more if you need more to recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional

        //add if you need reply to
        //$mail->addReplyTo('info@example.com', 'Information');
        //add if you need cc
        //$mail->addCC('cc@example.com');

        //add if you need bcc
        // $mail->addBCC('bcc@example.com');

        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $admin_send_subject;
        $mail->Body    = $cbxmessage;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if($mail->send() === true) {
            $validation_message['successmessage'] = 'Mensaje enviado correctamente';
        } else {
            $validation_message['successmessage'] = 'Sorry, Mail could not be sent. Please contact server admin: '.$mail->ErrorInfo;
        }

        //send email to user if user agreed or selected "copy me"
        if($cbxsendme == 'on'){

            //some config if you need help based on your server configuration
            //$mail2->Host = 'localhost';  // Specify main and backup SMTP servers

            // $mail->Username = 'user@example.com';                 // SMTP username
            // $mail->Password = 'secret';                           // SMTP password
            // $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
           // $mail2->Port = 25;                                    // TCP port to connect to

            //add admin from email
            $mail->From = $admin_email_from;
            //add admin from name
            $mail->FromName = $admin_email_from_name;
            //now send to user
            //$mail->From = $admin_email_from;
           // $mail->FromName = $admin_email_from_name;
            //$mail->all_recipients = array();
            $mail->addAddress($cbxemail ,$cbxname);     // Add a recipient, user who fillted the contact form
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'Copia del correo:'.$admin_send_subject;
            $mail->Body    = $cbxmessage;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if($mail->send() === true) {
                $validation_message['successmessage'] = 'Copia del mensaje enviada satisfactoriamente.';


            } else {
                $validation_message['successmessage'] = 'Sorry, Mail could not be sent. Please contact server admin: '.$mail2->ErrorInfo;

            }
        }
    }
    else{

    }

    //end send email

    echo json_encode($validation_message);
    die(1);
}