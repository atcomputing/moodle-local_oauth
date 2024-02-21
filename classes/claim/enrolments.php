<?php
namespace local_oauth\claim;

class enrolments implements claim{

    public function claim($user){
          $enrolments = [];
          $courses = enrol_get_users_courses($user->id,true,'shortname', null);
          foreach ($courses as $course) {
              $enrolments[] = $course->shortname;
          }
        $claims = [
            'enrolments' => $enrolments
        ];
        return $claims;
    }
}
