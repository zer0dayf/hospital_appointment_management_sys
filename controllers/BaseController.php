<?php

class BaseController
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    protected function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function getJsonInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
        }
        return $input;
    }

    protected function validateRequired($data, $fields)
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            $this->jsonResponse(['error' => 'Missing required fields: ' . implode(', ', $missing)], 400);
        }
    }

    protected function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } else if (is_string($data)) {
            $data = trim($data);
            $data = strip_tags($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    protected function validateEmail($email)
    {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => 'Invalid email format'], 400);
        }
    }

    protected function validatePhone($phone)
    {
        if (!empty($phone) && !preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
            $this->jsonResponse(['error' => 'Invalid phone format'], 400);
        }
    }
}
