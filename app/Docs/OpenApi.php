<?php

namespace App\Docs;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Dev Learn API Documentation",
 * description="API documentation for the Dev Learn platform, managing courses, enrollments, users, and videos.",
 * @OA\Contact(
 * email="yasantpro@gmail.com"
 * ),
 * @OA\License(
 * name="Apache 2.0",
 * url="http://www.apache.org/licenses/LICENSE-2.0.html"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Dev Learn API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT"
 * )
 */
class OpenApi {}
