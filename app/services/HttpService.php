<?php

namespace App\services;

use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes;

class HttpService implements Httpstatuscodes
{
    public Httpstatus $httpStatus;

    public function __construct()
    {
        $this->httpStatus = new Httpstatus();
    }

    public function manageResponseError($error, $customMsg = null, $statusCode = null)
    {
        $responseMsg = $customMsg ? $customMsg : $this->httpStatus->getReasonPhrase(self::HTTP_INTERNAL_SERVER_ERROR);

        $responseStatusCode = $statusCode ? $statusCode : self::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'statusCode' => $responseStatusCode,
            'message' => $responseMsg,
        ], $responseStatusCode);
    }
}
