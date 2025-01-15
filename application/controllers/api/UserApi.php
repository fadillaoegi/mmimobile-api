<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class UserApi extends RestController
{
    public $User_model_api;
    public function __construct()
    {
        parent::__construct();

        // NOTE: INITIAL MODEL
        $this->load->model('api/User_model_api');
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
                ], RestController::HTTP_OK);
                return;
            }

            // NOTE: CHECKING DEFAULT PASSWORD
            if ($password == $user['customer_password']) {
                $passIsHash = password_get_info($user['customer_password']);
                // NOTE: CHECKING CUSTOMER TYPE
                if ($user['customer_type_id'] < 2) $pelanggan = true;
                if ($passIsHash['algo'] == null) {
                    $customer_pass_default = true;
                    $this->set_response([
                        'status' => true,
                        'status_pass_default' => $customer_pass_default,
                        'message' => 'password still default, you must update',
                        "data" => [
                            'customer_status' => $pelanggan,
                            'customer_id' => $user['customer_id'],
                            'customer_type_id' => $user['customer_type_id'],
                            'customer_name' => $user['customer_name'],
                        ]
                    ], RestController::HTTP_OK);
                    return;
                }
            }

            // NOTE: CHECKING PASSWORD
            if (!password_verify($password, $user['customer_password'])) {
                $this->set_response([
                    'status' => true,
                    'message' => 'Invalid password',
                ], RestController::HTTP_OK);
                return;
            }

            // NOTE: CHECKING CUSTOMER TYPE
            if ($user['customer_type_id'] < 2) $pelanggan = true;

            // NOTE: LOGIN SUCCESSFULL
            $this->set_response([
                'status' => true,
                'status_pass_default' => $customer_pass_default,
                'message' => 'Sign in successfull',
                'data' => [
                    'customer_status' => $pelanggan,
                    'customer_pass_default' => $customer_pass_default,
                    'province_id' => $user['province_id'] == null ? "0" : $user['province_id'],
                    'city_id' => $user['city_id'] == null ? "0" : $user['city_id'],
                    'subdistrict_id' => $user['subdistrict_id'] == null ? "0" : $user['subdistrict_id'],
                    'customer_id' => $user['customer_id'],
                    'customer_type_id' => $user['customer_type_id'],
                    'customer_date_birth' => $user['customer_date_birth'],
                    'customer_name' => $user['customer_name'],
                    'customer_phone' => $user['customer_phone'],
                    'customer_email' => $user['customer_email'],
                    'customer_address' => $user['customer_address'],
                ]
            ], RestController::HTTP_OK);
        }
    }

    // NOTE: RESET PASSWORD 
    public function resetPassword_post()
    {
        $id =  $this->post('customer_id');
        $newPassword = $this->post('customer_password');

        // NOTE: CHECKING FIELD
        if (empty($id) && empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID and Password is required'
            ], RestController::HTTP_OK);
            return;
        } else if (empty($id)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID is required'
            ], RestController::HTTP_OK,);
            return;
        } else if (empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer Password is required'
            ], RestController::HTTP_OK);
            return;
        }

        // NOTE: GET CUSTOMER
        $customer = $this->User_model_api->getUserById($id);

        // NOTE: CHECKING DATA CUSTOMER_ID
        if (!$customer) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer_id not found',
            ], RestController::HTTP_OK);
            return;
        }

        // NOTE: HASH PASSWORD
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // NOTE: UPDATE CUSTOMER
        $updateNewPass = $this->User_model_api->updatePassById($id, $hashedPassword);

        // NOTE: CHECKING UPDATE
        if ($updateNewPass) {
            $this->set_response([
                'status' => true,
                'message' => 'Password updated successfully',
                'data' => [
                    'province_id' => $customer['province_id'] == null ? "0" : $customer['province_id'],
                    'city_id' => $customer['city_id'] == null ? "0" : $customer['city_id'],
                    'subdistrict_id' => $customer['subdistrict_id'] == null ? "0" : $customer['subdistrict_id'],
                    'customer_id' => $customer['customer_id'],
                    'customer_type_id' => $customer['customer_type_id'],
                    'customer_date_birth' => $customer['customer_date_birth'],
                    'customer_name' => $customer['customer_name'],
                    'customer_phone' => $customer['customer_phone'],
                    'customer_email' => $customer['customer_email'],
                    'customer_address' => $customer['customer_address'],
                ],
            ], RestController::HTTP_OK);
        } else {
            $this->set_response([
                'status' => false,
                'message' => 'Failed to update password'
            ], RestController::HTTP_OK,);
        }
    }

    // NOTE: UPDATE PASSWORD
    public function updatePassword_post()
    {
        $id =  $this->post('customer_id');
        $oldPassword = $this->post('customer_password_old');
        $newPassword = $this->post('customer_password');

        // NOTE: CHECKING FIELD
        if (empty($id) && empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID and Password is required'
            ], RestController::HTTP_OK);
            return;
        } else if (empty($id)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer ID is required'
            ], RestController::HTTP_OK,);
            return;
        } else if (empty($newPassword)) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer Password is required'
            ], RestController::HTTP_OK);
            return;
        }

        // NOTE: GET CUSTOMER
        $customer = $this->User_model_api->getUserById($id);

        // NOTE: CHECKING DATA CUSTOMER_ID
        if (!$customer) {
            $this->set_response([
                'status' => false,
                'message' => 'Customer_id not found',
            ], RestController::HTTP_OK);
            return;
        }

        if (!password_verify($oldPassword, $customer['customer_password'])) {
            $this->set_response([
                'status' => true,
                'message' => 'Invalid old password',
            ], RestController::HTTP_OK);
            return;
        }

        // NOTE: HASH PASSWORD
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // NOTE: UPDATE CUSTOMER
        $updateNewPass = $this->User_model_api->updatePassById($id, $hashedPassword);

        // NOTE: CHECKING UPDATE
        if ($updateNewPass) {
            $this->set_response([
                'status' => true,
                'message' => 'Password updated successfully',
                'data' => [
                    'province_id' => $customer['province_id'] == null ? "0" : $customer['province_id'],
                    'city_id' => $customer['city_id'] == null ? "0" : $customer['city_id'],
                    'subdistrict_id' => $customer['subdistrict_id'] == null ? "0" : $customer['subdistrict_id'],
                    'customer_id' => $customer['customer_id'],
                    'customer_type_id' => $customer['customer_type_id'],
                    'customer_date_birth' => $customer['customer_date_birth'],
                    'customer_name' => $customer['customer_name'],
                    'customer_phone' => $customer['customer_phone'],
                    'customer_email' => $customer['customer_email'],
                    'customer_address' => $customer['customer_address'],
                ],
            ], RestController::HTTP_OK);
        } else {
            $this->set_response([
                'status' => false,
                'message' => 'Failed to update password'
            ], RestController::HTTP_OK,);
        }
    }

    // NOTE: UPDATE PHONE
    // NOTE: UPDATE PROFILE

}
