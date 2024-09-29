<?php
require_once '../src/vendor/autoload.php';
use Twilio\Rest\Client;
use Ramsey\Uuid\Uuid;

function getConfig() {
    $config = require '../config/config.php';
    return $config;
}

function sendText($messageBody) {
    $config = getConfig();
    $sid = $config['twilio']['sid'];
    $token = $config['twilio']['token'];
    $twilioNumber = $config['twilio']['phone_number'];
    $destNumber = $config['dest_number'];

    $twilio = new Client($sid, $token);

    $message = $twilio->messages->create(
        $destNumber, // to
        [
            "body" => $messageBody,
            "from" => $twilioNumber,
        ]
    );

    echo '"' . $messageBody . '" sent to ' . $destNumber;
}

function uploadFile($file) {
    $tempFilePath = $file['tmp_name'];
    if(!is_uploaded_file($tempFilePath)) {
        throw new Exception('File was not uploaded via HTTP POST');
    }

    $config = getConfig();

    // TODO: replace this SCP logic with a POST request, containing the file, sent to the destination server
    $sshSession = ssh2_connect($config['ssh']['host']);
    if (!$sshSession) {
        throw new Exception('SSH connection failed');
    }

    $sshUser = $config['ssh']['user'];
    $pubFile = '/keys/' . $config['ssh']['pub_key_file_name'];
    $privFile = '/keys/' . $config['ssh']['priv_key_file_name'];
    $privPassphrase = $config['ssh']['priv_key_passphrase'];

    $authSuccess = ssh2_auth_pubkey_file(
        $sshSession, 
        $sshUser, 
        $pubFile, 
        $privFile, 
        $privPassphrase,
    );

    if (!$authSuccess) {
        throw new Exception('SSH Authentication Failed');
    }

    $fileName = Uuid::uuid4();
    $fileDestPath = $config['ssh']['file_dest_folder_path'] . $fileName;

    $uploadSuccess = ssh2_scp_send(
        $sshSession, 
        $tempFilePath, 
        $fileDestPath, 
        0644
    );

    if (!$uploadSuccess) {
        throw new Exception('File upload failed');
    }

    ssh2_exec($sshSession, 'exit');

    $siteDomain = $config['site']['domain'];
    $fileFolderPath = $config['site']['file_folder_path'];

    $fileLink = $siteDomain . $fileFolderPath . $fileName;

    return $fileLink;
}

if (!empty($_FILES['file']['tmp_name'])) {
    try {
        $fileLink = uploadFile($_FILES['file']);
        sendText($fileLink);
        echo nl2br("\n\n<a href='" . $fileLink . "'>" . $fileLink . "</a>");
    } catch (Exception $e) {
        http_response_code(500);
        echo 'ERROR:' . $e->getMessage();
    }
} else if (!empty($_POST['url'])) {
    // TODO: dont send text if URL is malformed
    sendText($_POST['url']);
} else {
    http_response_code(400);
    echo 'Request did not contain a URL or file';
}

// TODO:
// Respond with the link URL 
// Maybe then display it and/or have a clickable faceplate button that opens the last sent link in a new tab?
