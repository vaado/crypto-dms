<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

class FileManager
{

    protected $private_key;

    protected $service;

    protected $file_name;

    CONST STORE_FILE = 'post';

    CONST RETURN_FILE = 'get';

    CONST LOGIN = 'login';

    CONST DELETE = 'delete';

    public function __construct()
    {
        $headers = apache_request_headers();
        $request_headers = json_decode($headers['contents']);
        $this->private_key = $request_headers->private_key;
        $this->service = $request_headers->service;
        $this->file_name = $request_headers->file_name;
    }

    public function resolveRequest($request)
    {
        switch ($this->service) {
            case self::STORE_FILE:
                $this->storeFile($request);
                break;
            case self::RETURN_FILE:
                $this->returnFile();
                break;
            case  self::LOGIN:
                $this->login();
                break;
            case  self::DELETE:
                $this->delete();
                break;
            default:
                break;
        }
        if ($this->service == self::STORE_FILE) {
            $this->storeFile($request);
        }
    }

    private function returnFile()
    {
        try {
            $ciphertext = file_get_contents('files/'.$this->file_name);
            $key = Key::loadFromAsciiSafeString($this->private_key);
            echo Crypto::decrypt($ciphertext, $key, true);
            exit;
        } catch (Exception $exception) {
            echo $exception->getMessage();
        } catch (TypeError $typeError) {
            echo $typeError->getMessage();
        }
    }

    private function storeFile($request)
    {
        try {
            $file_data = $request['file'];
            $file_name_hash = $this->generateFileName();
            $key = Key::loadFromAsciiSafeString($this->private_key);
            $ciphertext = Crypto::encrypt($file_data, $key, true);
            file_put_contents('files/'.$file_name_hash, $ciphertext);
            header('Content-Type: application/json');
            echo json_encode(['file_name_hash' => $file_name_hash]);
            exit;
        } catch (Exception $exception) {
            echo $exception->getMessage();
        } catch (TypeError $typeError) {
            echo $typeError->getMessage();
        }
    }

    private function generateFileName()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 24; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function login()
    {
        header('Content-Type: application/json');
        echo json_encode(['logged_id' => true]);
    }
}