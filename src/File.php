<?php

namespace Flender\OpenAI;

class File {


    private string $id;
    private OpenAIRequestBuilder $requestBuilder;

    public function __construct(string $id, OpenAIRequestBuilder $requestBuilder)
    {
        $this->id = $id;
        $this->requestBuilder = $requestBuilder;
    }

    public function get_info()
    {
        return $this->requestBuilder->fetch("GET", "files/{$this->id}");
    }

    public function get_contents()
    {
        return $this->requestBuilder->fetch("GET", "files/{$this->id}/content");
    }


}