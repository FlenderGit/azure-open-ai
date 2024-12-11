<?php

namespace Flender\OpenAI;

class UploaderHandler
{
    
    public function verify_file(array $file): VerifiedFile
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("File upload failed with error code {$file['error']}");
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_extension = [
            "txt",
            "c",
            "cpp",
            "csv",
            "docx",
            "html",
            "java",
            "json",
            "md",
            "pdf",
            "php",
            "pptx",
            "py",
            "rb",
            "tex",
            "css",
            "jpeg",
            "jpg",
            "js",
            "gif",
            "png",
            "tar",
            "ts",
            "xlsx",
            "xml",
            "zip",
        ];      // See https://learn.microsoft.com/en-us/azure/ai-services/openai/how-to/assistant#supported-file-types

        if (!in_array($extension, $allowed_extension)) {
            throw new \Exception("File type not allowed. Allowed types are: " . implode(", ", $allowed_extension));
        }

        $path = $file['tmp_name'];
        $name = $file['name'];
        $type = $file['type'];

        return new VerifiedFile($path, $name, $type);
    }
}

class VerifiedFile {
    public string $path;
    public string $name;
    public string $type;

    public function __construct(string $path, string $name, string $type)
    {
        $this->path = $path;
        $this->name = $name;
        $this->type = $type;
    }
}