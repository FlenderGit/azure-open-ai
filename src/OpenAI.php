<?php declare(strict_types=1);

namespace Flender\OpenAI;

class OpenAI
{

    private Credentials $credentials;
    private OpenAIRequestBuilder $requestBuilder;

    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
        $this->requestBuilder = new OpenAIRequestBuilder($credentials);
    }

    public function new_thread(array $file_ids = [], array $messages = [], array $metadata = []): array
    {
        $data = [
            'messages' => $messages,
        ];

        if(!empty($meta)) {
            $data['metadata'] = $meta;
        }

        if ($file_ids) {
            $data['tool_resources']['code_interpreter']['file_ids'] = $file_ids;
        }

        return $this->requestBuilder->post_to_json("threads", $data);
    }

    public function get_threads(): array
    {
        return $this->requestBuilder->get_json("threads");
    }

    public function get_thread(string $id): Thread
    {
        return new Thread($id, $this->requestBuilder);
    }

    public function get_file(string $id): File
    {
        return new File($id, $this->requestBuilder);
    }

    public function get_files(?string $wanted = null): array
    {
        $files = $this->requestBuilder->get_json("files");

        if (!isset($files['data'])) {
            return [];
        }

        if ($wanted) {
            return array_reduce($files["data"], function ($acc, $file) use ($wanted) {
                if ($file['purpose'] === $wanted) {
                    $acc[] = $file;
                }
                return $acc;
            }, []);
        }

        return $files;
    }

    /**
     * Function add_files
     * ------------------
     * Add one or multiple files to a thread.
     * @throws \Exception if any of the files is invalid
     * @param array[] $files
     */
    public function upload_files(array ...$files)
    {
        $file_verifier = new UploaderHandler();
        $file_ids = [];
        foreach ($files as $file) {
            $verified_file = $file_verifier->verify_file($file);
            $uploaded_file = $this->safe_add_file($verified_file);
            $file_ids[] = $uploaded_file['id'];
        }
        return $file_ids;
    }

    /**
     * Function add_file
     * -----------------
     * Add a file to a thread. This function accepts one file at a time.
     * If you want to add multiple files, use add_files instead.
     * @throws \Exception if any of the files is invalid
     * @param array $file
     */
    public function upload_file(array $file)
    {
        $file_verifier = new UploaderHandler();
        $verified_file = $file_verifier->verify_file($file);
        return $this->safe_add_file($verified_file);
    }


    /**
     * Function safe_add_file
     * ----------------------
     * Add a file to a thread. This function accepts a VerifiedFile object
     * to be sure to pass a valid file.
     * @param \Flender\OpenAI\VerifiedFile $file
     * @return array
     */
    private function safe_add_file(VerifiedFile $file): array
    {
        return $this->requestBuilder->post_to_json("files", [
            'file' => curl_file_create($file->path, $file->type, $file->name),
            'purpose' => 'assistants'
        ], false);
    }

    public function delete_file(string $id): array
    {
        $response = $this->requestBuilder->delete("files/$id");
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        return $response;
    }

    public function delete_files(array $ids): array
    {
        $responses = [];
        foreach ($ids as $id) {
            $responses[] = $this->delete_file($id);
        }
        return $responses;
    }

    public function delete_thread(string $id): array
    {
        $response = $this->requestBuilder->delete("threads/$id");
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        return $response;
    }

    public function generate_image(string $prompt): array
    {

        $deployement_name = $this->credentials->get_deployement_name_image();

        if (empty($deployement_name)) {
            throw new \Exception("No deployment name found for image generation");
        }

        $response = $this->requestBuilder->post_to_json("deployments/$deployement_name/images/generations", [
            'prompt' => $prompt,
            'size' => "1024x1024",
            'quality' => "standard",
            'n' => 1,
            'style' => "vivid"
        ]);

        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }

        $out = array_map(function ($image) {
            return [
                'url' => $image['url'],
                'revised_prompt' => $image['revised_prompt']
            ];
        }, $response['data']);

        return $out;
    }


}