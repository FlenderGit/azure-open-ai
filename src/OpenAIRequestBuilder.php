<?php

namespace Flender\OpenAI;

use Exception;

class OpenAIRequestBuilder
{

    private Credentials $credentials;

    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    public function fetch(string $method, string $route, array $data = [], ?string $content_type = null)
    {
        $ch = curl_init();

        if ($method === 'GET') {
            $url = $this->get_url($route, $data);
        } else {
            $url = $this->get_url($route);
        }

        $headers = ['api-key: ' . $this->credentials->get_api_key()];
        if ($content_type) {
            $headers[] = 'Content-Type: ' . $content_type;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        if ($method === 'POST') {
            if ($content_type === 'application/json') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        $output = curl_exec($ch);

        if (curl_errno($ch) || $output === false) {
            $error = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            throw new Exception("ERRNO ERROR: $error, CODE: $code");
        }

        curl_close($ch);

        /* var_dump(curl_getinfo($ch));
        var_dump($output); */

        return $output;

    }

    private function set_curl_options(&$ch)
    {
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'api-key: ' . $this->credentials->get_api_key(),
                'Content-Type: application/json',
            ],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);
    }

    public function get(string $route, array $data = [])
    {
        return $this->fetch('GET', $route, $data);
    }


    private function json(string $method, string $route, array $data = [], ?string $content_type = null): array
    {
        $response = $this->fetch($method, $route, $data, $content_type);

        $json = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . $response);
        }
        return $json;
    }

    public function get_json(string $route, array $data = [])
    {
        return $this->json('GET', $route, $data);
    }

    /* public function post_json(string $route, array $data = [])
    {
        return $this->json('POST', $route, $data);
    } */

    public function post_to_json(string $route, array $data = [], $is_json = true)
    {
        if ($is_json) {
            return $this->json('POST', $route, $data, 'application/json');
        }
        return $this->json('POST', $route, $data, null);
    }

    public function post(string $route, array $data = [], $is_json = true)
    {
        if ($is_json) {
            return $this->fetch('POST', $route, $data, 'application/json');
        }
        return $this->fetch('POST', $route, $data, null);
    }

    public function delete(string $route, array $data = [])
    {
        return $this->json('DELETE', $route, $data);
    }

    private function get_url(string $route, array $data = []): string
    {
        $default = ["api-version" => "2024-07-01-preview"];
        $data = array_merge($default, $data);
        return $this->credentials->get_base_url() . $route . '?' . http_build_query($data);
    }

    private function abs_stream(string $url, array $data = [], $write_function = null)
    {
        $ch = curl_init();
        $this->set_curl_options($ch);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_WRITEFUNCTION => function ($ch, $str) use ($write_function) {
                $first_line = strpos($str, "\n");
                $event = substr($str, 0, $first_line);
                $data = substr($str, $first_line);
                $write_function($event, $data);
                return strlen($str);
            }
        ]);

        $output = curl_exec($ch);

        if (curl_errno($ch) || $output === false) {
            throw new Exception("ERRNO ERROR: " . curl_error($ch));
        }

        return $output;
    }

    public function stream(string $route, array $data = [], array $events_name)
    {
        $url = $this->get_url($route);

        header("Connection: keep-alive");
        header("Cache-Control: no-cache");
        header("Content-Type: text/event-stream");

        // Log in file
        $log = fopen("log.txt", "w");

        ob_start();
        $write_function = function (string $event, string $data) use ($events_name, &$log) {
            fwrite($log, "$event$data");
            if (in_array($event, $events_name)) {
                echo "$event$data";
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        };

        $this->abs_stream($url, $data, $write_function);
    }

    public function stream_until_event(string $route, array $data = [], string $event_name)
    {
        $url = $this->get_url($route);

        $message = "";
        $write_function = function (string $event, string $data) use ($event_name, &$message) {
            if ($event === $event_name) {
                $message = substr($data, 6);
            }
        };

        $this->abs_stream($url, $data, $write_function);

        return $message;
    }

}