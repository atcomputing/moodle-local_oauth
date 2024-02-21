<?php
namespace local_oauth\claim;

class phone implements claim{

    public function claim($user){
        $claims = [
            'phone_number' => isset($user->phone1) ? $user->phone1: $user->phone2,
            // phone_number_verified
        ];
        return $claims;
    }
}
