<?php
/*
 * Plugin Name: Formulaire de contact
 * Description: Plugin to use form contact.
 * Author: Afrakla Abdelaziz
 * Author URI: http://localhost/wordpress/contact-nous/
 * 
 */
 function formContactUs(){
     $content = '';
     $content .='<label for="fname">Firstname</label>';
     $content .='<input type="text" id="fname">';
     $content .='<label for="lname">Lastname</label>';
     $content .='<input type="text" id="lname">';
     $content .='<label for="email">Email</label>';
     $content .='<input type="email" id="email">';
     $content .='<label for="message">Message</label>';
     $content .='<textarea id="message"></textarea>';
     $content .='<input type="submit" value="Envoyer">';
     return $content;
 }

 	
 echo do_shortcode("[formContactUs]"); 
?>


