<?php

if (!function_exists('validation_first_error')) {
    function validation_first_error($errors): string
    {
        return reset($errors);
    }
}