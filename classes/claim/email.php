<?php
namespace local_oauth\claim;

class email implements claim{

    public function claim($user){
        $claims = [
            'email' => $user->email,
            // email_verified
        ];
        return $claims;
    }
}
