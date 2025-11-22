<?php

namespace core_ai\aiactions\responses;

use core_ai\aiactions\responses\response_generate_image;

class response_edit_image extends response_generate_image {

    /**
     * Constructor.
     *
     * @param bool $success The success status of the action.
     * @param int $errorcode Error code. Must exist if success is false.
     * @param string $errormessage Error message. Must exist if success is false
     */
    public function __construct(
        bool $success,
        int $errorcode = 0,
        string $errormessage = '',
    ) {
        response_base::__construct(
            success: $success,
            actionname: 'edit_image',
            errorcode: $errorcode,
            errormessage: $errormessage,
        );
    }

}