<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Api extends RestController
{
    public $User_model_api;
    public function __construct()
    {
        parent::__construct();

        // NOTE: INITIAL MODEL
        $this->load->model('User_model_api');
        $this->User_model_api = $this->User_model_api;
    }

    // NOTE: SIGN IN API
    public function signIn_post()
    {
        $phone = $this->post('phone');
        $password = $this->post('password');
        if (empty($phone) && empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number and password is required',
            ], RestController::HTTP_OK);
            return;
        } else if (empty($phone)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number is required',
            ], RestController::HTTP_OK);
            return;
        } else if (empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Password is required',
            ], RestController::HTTP_OK);

            return;
        } else {
            $user = $this->User_model_api->getUserByPhone($phone);
            if (!$user) {
                $this->set_response([
                    'status' => false,
                    'message' => 'User not found',
                ], RestController::HTTP_NOT_FOUND);
            }

            if (!password_verify($password, $user['customer_password'])) {
                $passwordIsHash = password_get_info($user['customer_password']);
                if ($passwordIsHash['algo']) {
                    $this->response([
                        'status' => false,
                        'message' => 'password still default, you must update',
                    ], RestController::HTTP_UNAUTHORIZED);
                    return;
                }
                $this->response([
                    'status' => false,
                    'message' => 'Invalid password',
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
                    'provinceId' => $user['province_id'],
                    'cityId' => $user['city_id'],
                    'subdistrictId' => $user['subdistrict_id'],
                ]
            ], RestController::HTTP_OK);
        }
    }
}
