<?php

namespace Flender\OpenAI;

use Flender\OpenAI\Models\Message;

class Thread
{
    public string $id;
    private OpenAIRequestBuilder $requestBuilder;

    public function __construct(string $id, OpenAIRequestBuilder $requestBuilder)
    {
        $this->id = $id;
        $this->requestBuilder = $requestBuilder;
    }

    public static function from(array $thread): array {

        $file_ids = [];
        if (isset($thread['tool_resources']['code_interpreter']['file_ids'])) {
            $file_ids = $thread['tool_resources']['code_interpreter']['file_ids'];
        }

        return [
            'id' => $thread['id'],
            'title' => $thread['metadata']['title'],
            'assistant_id' => $thread['metadata']['assistant_id'],
            'created_at' => $thread['created_at'],
            'file_ids' => $file_ids
        ];
    }

    public function get_data(): array
    {
        $response = $this->requestBuilder->get_json("threads/{$this->id}");

        if (isset($response["error"])) {
            throw new \Exception($response["error"]["message"]);
        }

        return $response;
    }

    /**
     * Function get_messages
     * ---------------------
     * Get all messages from a thread.
     * Maybe this function should would return a Message object instead of an array.
     * @return array
     */
    public function get_messages(?string $last_message_id = null): array
    {
        $data = [
            "limit" => 20
        ];
        if ($last_message_id) {
            $data['after'] = $last_message_id;
        }
        $messages = $this->requestBuilder->get_json("threads/{$this->id}/messages", $data);

        if (isset($messages["error"])) {
            throw new \Exception($messages["error"]["message"]);
        }

        $formatted_messages = array_reverse( array_map(function ($message) {
            return Message::from($message);
        }, $messages["data"]) );

        return [
            'messages' => $formatted_messages,
            'has_more' => $messages["has_more"]
        ];
    }

    /**
     * Function send_message
     * ---------------------
     * Send a message to a thread.
     * @param string $message
     * @return mixed
     */
    public function send_message(string $message, string $assistant_id, bool $is_streamed = false)
    {
        $this->add_message($message, 'user');
        return $this->run($assistant_id, $is_streamed);
    }

    public function add_message(string $message, string $role = 'user')
    {
        $this->requestBuilder->post_to_json("threads/{$this->id}/messages", [
            'role' => $role,
            'content' => $message
        ]);
    }

    public function run(string $assistant_id, bool $is_streamed)
    {

        $route = "threads/{$this->id}/runs";
        $data = [
            'assistant_id' => $assistant_id,
            'model' => 'gpt-4o',
            'stream' => true,
            'response_format' => 'auto'
        ];

        $out = null;
        if ($is_streamed) {
            $lst_event = [
                'event: thread.run.step.completed',
                'event: thread.message.delta',
                'event: thread.message.completed'
            ];
            $this->requestBuilder->stream($route, $data, $lst_event);
        } else {
            $out = $this->requestBuilder->stream_until_event($route, $data, 'event: thread.message.completed');
        }

        return $out;

    }

    public function set_files(array $files): bool
    {
        $response = $this->requestBuilder->post_to_json("threads/{$this->id}", [
            "tool_resources" => [
                "code_interpreter" => [
                    "file_ids" => $files
                ]
            ]
        ]);
        return $response['tool_resources']['code_interpreter']['file_ids'] === $files;
    }

    public function update()
    {
        throw new \Exception("Not implemented yet");
    }

}