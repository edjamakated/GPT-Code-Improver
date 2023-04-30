<?php
class GPT4Response
{
    private const DEFAULT_API_KEY = 'yourKeyHere';

    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $ch;
    private $system_instruction;
    private $assistant_instruction;
    private $model;

    public function __construct($api_key = null, $system_instruction = null, $assistant_instruction = null, $model = null)
    {
        $this->api_key = $api_key ?? self::DEFAULT_API_KEY;
        $this->system_instruction = $system_instruction ?? "You are a specially designed GPT that improves code in any programming language. Your objective is to make the code better, more modern, and faster. Respond with the entire code updated without any other comments or remarks.";
        $this->assistant_instruction = $assistant_instruction ?? "Find ways to improve this code and output ONLY updated code. Output an updated version of the following input: ";
        $this->model = $model ?? 'gpt-3.5-turbo';
        $this->initializeCurl();
    }

    private function initializeCurl()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $this->api_url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
    }

    public function getResponse($code = '')
    {
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->system_instruction],
                ['role' => 'assistant', 'content' => $this->assistant_instruction],
                ['role' => 'user', 'content' => $code]
            ],
            'temperature' => 0.06,
            'max_tokens' => 2560,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];

        $json_data = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error encoding request data: ' . json_last_error_msg());
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $json_data);
        $response = curl_exec($this->ch);

        if ($response === false) {
            throw new Exception('Error executing curl request: ' . curl_error($this->ch), curl_errno($this->ch));
        }
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            throw new Exception('Error retrieving response from GPT. HTTP code: ' . $http_code);
        }
        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error decoding response data: ' . json_last_error_msg());
        }
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: No response from GPT-4']);
            exit;
        }
        
        header('Content-Type: application/json');
        return json_encode(['response' => $response_data['choices'][0]['message']['content']]);
        }
        
        public function __destruct()
        {
            curl_close($this->ch); // Close the curl handle when the object is destroyed
        }
        }
        // Read JSON input from the POST request
        $json_input = file_get_contents('php://input');
        $input_data = json_decode($json_input, true);
        
        if (isset($input_data['code'])) {
            $code = $input_data['code'];
            header('Content-Type: application/json');
            $gpt4 = new GPT4Response();
            try {
                $gpt4_response = $gpt4->getResponse($code);
                echo $gpt4_response;
            } catch (Exception $e) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
            }
        }
?>
