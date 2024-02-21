<?php
namespace local_oauth\claim;

class profile implements claim{

    public function claim($user){
        global $CFG, $PAGE;
        $user_picture = new \user_picture($user);
        $claims = [
            // 'name'
            'family_name' => $user->lastname,
            'given_name' => $user->firstname,
            'middle_name' => $user->middlename,
            'nickname' => $user->alternatename,
            'preferred_username' => $user->username,
            'profile' =>  $CFG->wwwroot."/user/profile.php?id=".$user->id,
            'picture' =>  $user->picture ? $user_picture->get_url($PAGE)->raw_out(): null,
            // 'website'
            // 'gender'
            // 'birthdate'
            // NOTE moodle timezone is 99 if you set timezone is same as server
            'zoneinfo' => $user->timezone == '99' ? date_default_timezone_get() : $user['timezone'],
            // 'locale'
            'updated_at' => $user->timemodified
        ];
        return $claims;
    }
}
