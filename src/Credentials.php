<?php

namespace Flender\OpenAI;

class Credentials
{
    private string $api_key;
    private string $service_name;
    private ?string $deployment_name_image;

    public function __construct(string $api_key, string $service_name, ?string $deployment_name_image = null)
    {
        $this->api_key = $api_key;
        $this->service_name = $service_name;
        $this->deployment_name_image = $deployment_name_image;
    }

    public function get_api_key(): string
    {
        return $this->api_key;
    }

    public function get_base_url(): string
    {
        return "https://{$this->service_name}.openai.azure.com/openai/";
    }

    public static function from_env(): Credentials
    {
        $api_key = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        $service_name = $_ENV['OPENAI_SERVICE_NAME'] ?? getenv('OPENAI_SERVICE_NAME');
        $deployment_name_image = $_ENV['OPENAI_DEPLOYMENT_NAME_IMAGE'] ?? getenv('OPENAI_DEPLOYMENT_NAME_IMAGE');

        /* if (!$api_key || !$service_name) {
            throw new \Exception("OPENAI_API_KEY and OPENAI_SERVICE_NAME must be set in the environment");
        } */

        return new Credentials($api_key, $service_name, $deployment_name_image);
    }

    public function get_deployement_name_image(): ?string
    {
        return $this->deployment_name_image;
    }
}