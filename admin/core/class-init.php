<?php

class init {
    public function __construct() {
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
}