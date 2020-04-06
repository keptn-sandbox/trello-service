<?php

$trelloAPIKey = getenv("TRELLO_API_KEY");
$trelloAPIToken = getenv("TRELLO_API_TOKEN");
$trelloBoardID = getenv("TRELLO_BOARD_ID");
$trelloListName = getenv("TRELLO_LIST_NAME");

if ($trelloAPIKey == null || $trelloAPIToken == null || $trelloBoardID == null || $trelloListName == null) {
  fwrite($logFile, "Missing mandatory input parameters: TRELLO_API_KEY and / or TRELLO_API_TOKEN and / or TRELLO_BOARD_ID and / or TRELLO_LIST_NAME\n");
  exit("Missing mandatory input parameters: TRELLO_API_KEY and / or TRELLO_API_TOKEN and / or TRELLO_BOARD_ID and / or TRELLO_LIST_NAME");
}

$logFile = fopen("logs/trelloService.log", "a") or die("Unable to open file!");

$entityBody = file_get_contents('php://input');
if ($entityBody == null) {
  fwrite($logFile,"\nMissing data input from Keptn. Exiting.");
  exit("Missing data input from Keptn. Exiting.");
}

// Write the raw input to the log file...
fwrite($logFile, "\nEntity Body: " . $entityBody);

//Decode the incoming JSON event
$cloudEvent = json_decode($entityBody);

$keptnResult = strtoupper($cloudEvent->{'data'}->{'result'});
$keptnProject = $cloudEvent->{'data'}->{'project'};
$keptnService = $cloudEvent->{'data'}->{'service'};
$keptnStage = $cloudEvent->{'data'}->{'stage'};

fwrite($logFile, "Keptn Result: " . $keptnResult . "\n");
fwrite($logFile, "Keptn Project: " . $keptnProject . "\n");
fwrite($logFile, "Keptn Service: " . $keptnService . "\n");
fwrite($logFile, "Keptn Stage: " . $keptnStage . "\n");


// Get lists from board

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.trello.com/1/boards/$trelloBoardID/lists?key=$trelloAPIKey&token=$trelloAPIToken",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET"
));

$response = curl_exec($curl);

curl_close($curl);
fwrite($logFile, "Get List Response: " . $response);

// Grab list ID where name = $trelloListName

$jsonData = json_decode($response, true);

$listID = "";

// Match the human readable list name to the list ID.
foreach ($jsonData as $listItem) {
  if ($listItem["name"] == $trelloListName) $listID = $listItem["id"];
}

fwrite($logFile, "List Name: " . $trelloListName . " has ID: " . $listID);

/*********************************
        Create Trello Card
**********************************/

$description = "";
$description .= "# Keptn Evaluation Completed. Result: " . $keptnResult . "\n";
$description .= "Project: **" . $keptnProject . "**\nService: **" . $keptnService . "**\nStage: **" . $keptnStage . "**";

// For loop through indicatorResults
$description .= "\n\n---\nSLI Results\n";

foreach ($cloudEvent->{'data'}->{'evaluationdetails'}->{'indicatorResults'} as &$value) {
  $description .= "Metric: **" . $value->{'value'}->{'metric'} . "**\n";
  $description .= "Status: **" . $value->{'status'} . "**\n";
  $description .= "Value: **" . $value->{'value'}->{'value'} . "**\n";

  $description .= "\n\n---\nTargets\n";
  foreach ($value->{'targets'} as $target) {
      $description .= "Criteria: **" . $target->{'criteria'} . "**\n";
      $description .= "Target Value: **" . $target->{'targetValue'} . "**\n";
      $description .= "Violated: **" . ($target->{'violated'} ? 'true' : 'false') . "**\n";
  }

  if ($value->{'value'}->{'message'} != "") {
    $description .= "Message: **" . $value->{'value'}->{'message'} . "**\n";
  }
}

$description .= "\n\n---\n\nKeptn Context: " . $cloudEvent->{'shkeptncontext'};

fwrite($logFile,"\nDescription: " . $description);

$trelloObj = new stdClass();
$trelloObj->key = $trelloAPIKey;
$trelloObj->token = $trelloAPIToken;
$trelloObj->idList = $listID;
$trelloObj->name = "Keptn Result: $keptnResult ($keptnProject / $keptnService / $keptnStage)";
$trelloObj->desc = $description;

$trelloJSON = json_encode($trelloObj);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.trello.com/1/cards",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $trelloJSON,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
fwrite($logFile, "\nCreate Item Response: $response");

fwrite($logFile, "\n------- END LOG ENTRY -----------");
fclose($logFile);
?>
