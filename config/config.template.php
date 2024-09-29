<?php
return [
    "dest_number" => "", // Your real phone number
    "twilio" => [
        "sid" => "",
        "token"  => "",
        "phone_number" => "",
    ],
    "ssh" => [
        "host" => "",
        "user" => "",
        "pub_key_file_name" => "",
        "priv_key_file_name" => "",
        "priv_key_passphrase" => "",
        "file_dest_folder_path" => "/var/www/html/file/",
    ],
    'site' => [
        // example.com/file/{UUID}
        "domain" => "example.com",
        "file_folder_path" => "/file/",
    ],
];

?>
