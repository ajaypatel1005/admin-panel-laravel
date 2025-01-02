<?php

namespace App\Http\Controllers\Helpers;

use Response;

class ResponseHelper
{
    public static $responseCodes = [

        // SUCCESS
        'success' => 200,
        'created' => 201,
        'accepted' => 202,
        'no_content' => 204,

        // ERROR
        'invalid_request' => 400,
        'request_timeout' => 400,
        'error' => 422,
        'not_found' => 400,
        'duplicate' => 400,
        'invalid_file' => 400,
        'invalid_data' => 400,
        'missing_required_field' => 400,
        'disallowed' => 403,
    ];

    public static $responseMessage = [
        200 => 'Success',
        201 => 'Created',
        202 => 'Accepted',
        504 => 'Request Timeout',
        400 => 'Bad Request',
        403 => 'Access Forbidden',
        422 => 'Unprocessable Entity',
        700 => 'Error',
        701 => 'Not Found',
        702 => 'Duplicate',
        703 => 'Invalid File',
        704 => 'Invalid Data',
        705 => 'Missing Required Fields',
        706 => 'Access Disallowed',
    ];

    /**
     * @param $code
     * @param array $data
     * @param string $message
     * @param string $version
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function responseMessage($code, $data = array(), $message = '', $version = '1.0.0')
    {
        if (! is_numeric($code)) {
            $code = self::$responseCodes[$code];
            if ($message === '') {
                $message = self::$responseMessage[$code];
            }
        } else if ($message === '') {
            if (array_key_exists($code, self::$responseMessage)) {
                $message = self::$responseMessage[$code];
            } else {
                $message = 'Unknown Status';
            }
        }

        $request = request();

        $response = response()->json(['msg' => $message, 'version' => $version, 'data' => $data], $code);
        $response->header('Content-Type', 'text/json');

        return $response;
    }

    /**
     * @param array $errors
     * @param int $code
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function errorResponse($errors = [], $code = 422)
    {
        $request = request();

        $response = response()->json(['errors' => $errors], $code);
        $response->header('Content-Type', 'text/json');

        return $response;
    }
}
