<?php

namespace App\Utils;


trait RolesUtils
{

    /**
     * getRolesAllowed
     *
     * @return Array - Roles array
     */
    public static function getRolesAllowed()
    {
        return ['admin', 'user', 'dealer'];
    }
}
