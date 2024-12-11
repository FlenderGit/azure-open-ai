# PHP - API Azure OpenAI

## Description
API Azure OpenAI is a small wrapper in PHP to use the [Azure OpenAI API](https://learn.microsoft.com/en-us/azure/ai-services/openai).
For now, there is only few fonctionnalities added, but you can chat with the AI and handle threads and generate images.

## 📶 Download
How can easily download the librarie using composer.
```bash
composer require flender/azure-openai
```

## Usage
```php
$credentials = Credentials::from_env();
$openai = new OpenAI($credentials);

// Send a message into a thread
$thread_id = "my-thread-id";
$my_assistant_id = "my-assistant-id";
$response = $openai->get_thread($thread_id)->send_message("Hello, I'm a message", $my_assistant_id, false);

// Get message of a thread
$messages = $openai->get_thread($thread_id)->get_messages();

// Add a file in the server
$file_id = $openai->upload_file($_FILES["my-file"]);

// Generate a picture
$my_prompt = "A castle at dusk";
$response = $openai->generate_imag($my_prompt);
```
For a more concrete exemple, you can check the [exemple.php](exemple.php).

## TODO
- [x] This README
- [ ] Refactor the OpenAIRequestBuilder all methods... 
