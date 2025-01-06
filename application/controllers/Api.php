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

        // NOTE: CHECKING FORM
        if (empty($phone) && empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number and password is required',
            ], RestController::HTTP_BAD_REQUEST);
            return;
        } else if (empty($phone)) {
            $this->response([
                'status' => false,
                'message' => 'Phone number is required',
            ], RestController::HTTP_BAD_REQUEST);
            return;
        } else if (empty($password)) {
            $this->response([
                'status' => false,
                'message' => 'Password is required',
            ], RestController::HTTP_BAD_REQUEST);

            return;
        } else {

            // NOTE: DUMMY
            // $passInfo = password_get_info($user['customer_password']);
            // $passHashh = password_hash($user['customer_password'], PASSWORD_DEFAULT);
            // $passInfo = password_get_info($passHashh);
            // "password": "aku123123",
            // "passwordHash": "$2y$10$7DkViUV0fLSYHQWDV.hQaulwjPJWxTxScmQNOJXdzOjSUv8iPEnJi"

            $user = $this->User_model_api->getUserByPhone($phone);
            $pelanggan = false;
            $customer_pass_default = false;

            // NOTE: CHECKING USER
            if (!$user) {
                $this->set_response([
                    'status' => false,
                    'message' => 'User not found',
                ], RestController::HTTP_NOT_FOUND);
                return;
            }

            // NOTE: CHECKING DEFAULT PASSWORD
            if ($password == $user['customer_password']) {
                $passIsHash = password_get_info($user['customer_password']);
                if ($passIsHash['algo'] == null) {
                    $customer_pass_default = true;
                    $this->set_response([
                        'status' => false,
                        'status_pass_default' => $customer_pass_default,
                        'message' => 'password still default, you must update',
                    ], RestController::HTTP_UNAUTHORIZED);
                    return;
                }
            }

            // NOTE: CHECKING PASSWORD
            if (!password_verify($password, $user['customer_password'])) {
                $this->set_response([
                    'status' => false,
                    'message' => 'Invalid password',
                ], RestController::HTTP_UNAUTHORIZED);
                return;
            }

            // NOTE: CHECKING CUSTOMER TYPE
            if ($user['customer_type_id'] > 0) $pelanggan = true;

            // NOTE: LOGIN SUCCESSFULL
            $this->set_response([
                'status' => true,
                'message' => 'Login successfull',
                'data' => [
                    'customer_id' => $user['customer_id'],
                    'customer_status' => $pelanggan,
                    'customer_pass_default' => $customer_pass_default,
                    'customer_name' => $user['customer_name'],
                    'customer_date_birth' => $user['customer_date_birth'],
                    'customer_phone' => $user['customer_phone'],
                    'customer_type_id' => $user['customer_type_id'],
                    'province_id' => $user['province_id'] == null ? "0" : $user['province_id'],
                    'city_id' => $user['city_id'] == null ? "0" : $user['city_id'],
                    'subdistrict_id' => $user['subdistrict_id'] == null ? "0" : $user['subdistrict_id'],
                ]
            ], RestController::HTTP_OK);
        }
    }

    // NOTE: UPDATE PASSWORD FIRST
    public function updatePasswordFirst_post()
    {
        $id =  $this->post('customer_id');
        $newPassword = $this->post('customer_password');

        // NOTE: CHECKING FIELD
        if (empty($id) && empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID and Password is required'
            ], RestController::HTTP_BAD_REQUEST);
            return;
        } else if (empty($id)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID is required'
            ], RestController::HTTP_BAD_REQUEST,);
            return;
        } else if (empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer Password is required'
            ], RestController::HTTP_BAD_REQUEST);
            return;
        }

        // NOTE: GET CUSTOMER
        $customer = $this->User_model_api->getUserById($id);

        // NOTE: CHECKING DATA CUSTOMER
        if (!$customer) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer not found',
                'data' => []
            ], RestController::HTTP_NOT_FOUND);
            return;
        }

        // NOTE: HASH PASSWORD
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // NOTE: UPDATE CUSTOMER
        $updateNewPass = $this->User_model_api->updatePassById($id, $hashedPassword);

        // NOTE: CHECKING UPDATE
        if ($updateNewPass) {
            $this->set_response(['status' => true, 'message' => 'Password updated successfully'], RestController::HTTP_OK);
        } else {
            $this->set_response(['status' => false, 'message' => 'Failed to update password'], RestController::HTTP_INTERNAL_ERROR);
        }
    }
}
