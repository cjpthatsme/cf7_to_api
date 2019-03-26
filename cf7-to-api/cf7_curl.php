<?php
/*
Plugin Name: CF7 to API
Plugin URI: 
Description: Curl submission data to an external API. (Edit plugin code to configure)
Author: Chris Page
Version: 1.0
Author URI: 
*/

add_action('wpcf7_before_send_mail', 'curl_to_api');

function curl_to_api($contactForm)
{
  // Our curl url and define any needed custom headers
  $curl_url = '[EXTERNAL URL]';
  $curl_headers = array(
    "authorization: ",
    "content-type: application/json",
    "cache-control: no-cache"
  );

  // Should we write to a log file
  $debug = false;


  if (!isset($contactForm->posted_data) && class_exists('WPCF7_Submission')) {
    // If we got this far lets start building a log
    $log .= "\n" . date('M,d,Y h:i:s A') . "\nPosted data set and class exists!\n";

    // Grab the summission class
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
      // If the submission exists lets log that it does
      $log .= "Submission exists!\n";

      // Now lets prepare our data
      $formData = prepData($submission->get_posted_data());
      // Append the prepped data to our log
      $log .= print_r($formData, true) . "\n";

      // Grab your brooms lets do some curling
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $curl_url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute post
      $server_response = curl_exec($ch);

      // Close connection
      curl_close($ch);

      // Add the response to our log
      $log .= $server_response;
    }
  }

  // Now if debugging is turned on, lets write to the log file
  if ($debug) {
    $file = fopen(plugin_dir_path(__FILE__) . 'log.txt', "a+");
    fwrite($file, $log);
    fclose($file);
  }
}


function prepData($data)
{
  // This function can be customized to work with your needs
  // I am simply mapping and encoding JSON
  $arr = array(
    'first_name' => $data['cust-first-name'],
    'last_name' => $data['cust-last-name'],
    'phone' => $data['cust-phone']
  );
  return (json_encode($arr));
}
