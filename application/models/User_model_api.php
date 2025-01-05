<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_model_api extends CI_Model
{
    public function getUserByPhone($phone)
    {
        $result = $this->db->get_where('ap_customer', ['customer_phone' => $phone,])->row_array();
        return $result;
    }

    public function createUser($data)
    {
        $result = $this->db->insert('ap_customer', $data);
        return $result;
    }
}
