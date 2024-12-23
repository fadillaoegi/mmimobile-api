<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Api extends RestController
{
    public $User_model;
    public function __construct()
    {
        parent::__construct();

        // NOTE: INITIAL MODEL
        $this->load->model('User_model');
        $this->User_model = $this->User_model;
    }
    public function login_post()
    {
        $phone = $this->post('phone');
        $password = $this->post('password');
        if (empty($phone) && empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number and password is required',
            ], RestController::HTTP_OK);
            return;
        } elseif (empty($phone)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number is required',
            ], RestController::HTTP_OK);
            return;
        } elseif (empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Password is required',
            ], RestController::HTTP_OK);

            return;
        } else {
            $user = $this->User_model->getUserByPhone($phone);
            if (!$user) {
                $this->response([
                    'status' => false,
                    'message' => 'User not found',
                ], RestController::HTTP_NOT_FOUND);
            }

            // if (!password_verify($password, $user['customer_password'])) {

            if ($password != $user['customer_password']) {
                $this->response([
                    'status' => false,
                    'message' => 'Invalid password',
                    'data' => $user['customer_password'],
                    'dataP' => $password,
                ], RestController::HTTP_UNAUTHORIZED);
                return;
            }

            // NOTE: LOGIN SUCCESSFULL
            $this->response([
                'status' => true,
                'message' => 'Login successfull',
                'data' => [
                    'id' => $user['customer_id'],
                    'name' => $user['customer_name'],
                    'phone' => $user['customer_phone'],
                ]
            ], RestController::HTTP_OK);
        }
    }
}
