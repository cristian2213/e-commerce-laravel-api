<?php

namespace App\Utils;


trait RolesUtil
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
