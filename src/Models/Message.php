<?php

namespace Flender\OpenAI\Models;

class Message {
    static function from(array $message): array {
        return [
            'id' => $message['id'],
            'role' => $message['role'],
            'content' => array_map(function ($content) {
                return MessageContent::from($content);
            }, $message['content']),
            'created_at' => $message['created_at']
        ];
    }

}
