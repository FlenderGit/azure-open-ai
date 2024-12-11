<?php

namespace Flender\OpenAI\Models;

class MessageContent {


    static function from(array $content): array {
        if ($content["type"] === "text") {
            
            // WARNING: For now, we can't add a "image_url" to a assistant so i send the image as a text href
            if (str_starts_with($content["text"]["value"], "https://dalleproduse.blob.core.windows.net/private/images/")) {
                return [
                    "type" => "image_file",
                    "src" => $content["text"]["value"]
                ];
            }

            return [
                "type" => "text",
                "text" => $content["text"]["value"]
            ];
        } else {
            return [
                "type" => "image_file",
                "src" => "api/file/" . $content["image_file"]["file_id"]
            ];
        }
    }

}
