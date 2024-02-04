<?php

namespace Core;

class Response
{
    public array $data = [];
    public int $status = 200;
    public function __construct($data, $status = 200)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function send()
    {
        http_response_code($this->status);
        echo json_encode($this->data);
    }
}