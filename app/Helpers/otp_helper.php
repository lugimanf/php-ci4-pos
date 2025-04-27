<?php

if (!function_exists('generate_otp')) {
    function generate_otp(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}