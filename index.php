<!-- Copyright © 2022. Orren Prunckun. All Rights Reserved | Version 2.0.0 -->

<?php
//Initialize token variable
$total_tokens = 0;

$total_tokens += $_POST['tokens'];
    //echo $total_tokens."<br>";

//Initial message - use this one time only
$system = "You are a helpful assistant.";

//From form input - use this pultiple times.
$question = $_POST['question'];
    //echo $question."<br>";

//If hidden form field contains the word "null", then use the following arrays. This will only work the first time the form is submitted.
if (strpos($_POST['request_body'], "null") !== false) {
  $request_body = array(
      "model" => "gpt-3.5-turbo",
      "messages" => array(
          array(
              "role" => "system",
              "content" => $system
          ),
          array(
              "role" => "user",
              "content" => $question
          ),
      )
  );
  
//If hidden form field DOES NOT contain the word "null", then use the following arrays. This will only work the AFTER the first time the form is submitted.
} else {

//convert hidden form field to an array   
  $request_body = json_decode($_POST['request_body'], true);
  $request_body['messages'][0]['content'] = $question;
}

//Now append the end of the "messages" array (above) with the next input from the next form submission.
$request_body['messages'][] = array(
  'role' => 'user',
  'content' => $question
);

//Print/echo the output of the arrays.
//print_r($request_body);
//echo "<br><br>";

//Send arrays to API
$OPENAI_API_KEY = "ADD YOUR API KEY HERE";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($request_body),
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer ".$OPENAI_API_KEY,
    "Content-Type: application/json"
  ),
));

//Get API response
$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

//If the API has an error, echo it
if ($err) {
    echo "cURL Error #:" . $err;

//If the API DOES NOT have an error, echo the response as JSON
} else {
    //echo $response."<br>"; 
  
//Decode the JSON resonse
$response_obj = json_decode($response);

//Calculate now many tokens the API took
$total_tokens += $response_obj->usage->total_tokens;
    //echo $total_tokens."<br>";
    //echo "Total price of request: $".(1000/$total_tokens)*0.0002."<br>";    

//Check if total tokens are greater than or equal to 4096
if ($total_tokens >= 4096) {
    echo "You are out of tokens! Reload the page to get more token.";
} else {

//Echo the content of the decodes JSON response
$content = $response_obj->choices[0]->message->content;
    echo $content."<br><br>";

//Now append the content of the decodes JSON response as an "assistant" message to the "messages" array
$request_body['messages'][] = array(
  'role' => 'assistant',
  'content' => $content
);

//End else for error
}

//Print/echo the output of the arrays.
//print_r($request_body);
//echo "<br><br>";

//End else for token count
}
?>
<form autocomplete="off" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <!-- <textarea name="request_body" rows="4" cols="130"><?php echo htmlspecialchars(json_encode($request_body), ENT_QUOTES, 'UTF-8'); ?></textarea><br> -->
    <input type="hidden" name="request_body" size="155" value="<?php echo htmlspecialchars(json_encode($request_body), ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="tokens" value="<?php echo $total_tokens; ?>">
    <input type="text" name="question" size="55" value=""><br>
    <button type="submit" name="submit" >Ask!</button>
    <br>
</form>
