<?php
namespace local_oauth\claim;

class address implements claim{

    public function claim($user){
        $claims = [
            'address' => [
                // 'formatted',
                'street_address' => $user->address,
                'locality' => $user->address,
                // 'region'
                // 'postal_code'
                'country' => $user->country,
            ]
        ];
        return $claims;
    }
}
