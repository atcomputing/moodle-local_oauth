<?php

namespace local_oauth\claim;

interface claim {
    public function claim(array $user);
}
