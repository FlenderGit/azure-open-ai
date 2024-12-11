<?php

require 'vendor/autoload.php';

use Flender\OpenAI\OpenAI;
use Flender\OpenAI\Credentials;

$credentials = Credentials::from_env();
$openai = new OpenAI($credentials);

// Send a message to the assistant
$thread_id = "my_thread_id";
$response = $openai->get_thread($thread_id)->send_message("Hello, I'm a message", $my_assistant_id, false);

// Get all messages from a thread
$messages = $openai->get_thread($thread_id)->get_messages();

// Get all threads
$threads = $openai->get_threads();

// How to have a diagnostic of files
// 1. Upload files
// 2. Create a thread with the file_ids
// 3. Run the thread
// 3. Delete files
// 4. Delete the thread
// 5. Return the diagnostic

// Upload a file
$file_ids = $openai->upload_files(...$_FILES);

// Create a thread with the file_ids
$message = [
    [
        "role" => "user",
        "content" => [
            [
                "type" => "text",
                "text" => "Can you diagnose these files?"
            ]
        ]
    ]
];
$response = $openai->new_thread($file_ids, $message, $metadata);

// Run the thread
$my_assistant_id = "my_assistant_id";
$json = $openai->get_thread($response['id'])->run($my_assistant_id, false);

// Delete files
$openai->delete_files($file_ids);

// Delete the thread
$openai->delete_thread($response['id']);

// Return the diagnostic
header('Content-Type: application/json');
echo json_encode($json);