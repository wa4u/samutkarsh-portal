<?php

return [

    /*
    | The academic year new public registrations are filed under, and the year
    | the Result Gateway looks up. Bump per cycle via the ADMISSION_YEAR env var.
    */
    'academic_year' => (int) env('ADMISSION_YEAR', date('Y')),

    /*
    | Master switch for the public registration form. When false, the form is
    | closed (useful between cycles) but the Result Gateway stays open.
    */
    'registration_open' => (bool) env('ADMISSION_REGISTRATION_OPEN', true),
];
