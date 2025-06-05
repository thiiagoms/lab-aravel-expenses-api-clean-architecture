<?php

declare(strict_types=1);

namespace Src\Interfaces\Http;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API Documentation for Invoices management system',
    title: 'Invoices API Documentation',
    contact: new OA\Contact(name: 'Thiiagoms', email: 'thiiagoms@proton.me'),
    license: new OA\License(
        name: 'Apache 2.0',
        url: 'https://www.apache.org/licenses/LICENSE-2.0.html'
    )
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(
    url: 'http://localhost:8000/api/documentation',
    description: 'API Documentation Server'
)]
#[OA\Tag(
    name: 'Projects',
    description: 'API Endpoints of Projects'
)]
abstract class Controller {}
