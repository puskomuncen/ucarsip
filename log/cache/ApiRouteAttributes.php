<?php return \Symfony\Component\VarExporter\Internal\Hydrator::hydrate(
    $o = [
        (\Symfony\Component\VarExporter\Internal\Registry::$factories['Symfony\\Component\\Routing\\RouteCollection'] ?? \Symfony\Component\VarExporter\Internal\Registry::f('Symfony\\Component\\Routing\\RouteCollection'))(),
        clone (($p = &\Symfony\Component\VarExporter\Internal\Registry::$prototypes)['Symfony\\Component\\Routing\\Route'] ?? \Symfony\Component\VarExporter\Internal\Registry::p('Symfony\\Component\\Routing\\Route')),
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
        clone $p['Symfony\\Component\\Routing\\Route'],
    ],
    null,
    [
        'Symfony\\Component\\Routing\\RouteCollection' => [
            'routes' => [
                [
                    'api.login' => $o[1],
                    'api.list' => $o[2],
                    'api.view' => $o[3],
                    'api.add' => $o[4],
                    'api.edit' => $o[5],
                    'api.delete' => $o[6],
                    'api.register' => $o[7],
                    'api.file' => $o[8],
                    'api.export' => $o[9],
                    'api.upload' => $o[10],
                    'api.jupload' => $o[11],
                    'api.session' => $o[12],
                    'api.lookup' => $o[13],
                    'api.chart' => $o[14],
                    'api.permissions' => $o[15],
                    'api.push' => $o[16],
                    'api.twofa' => $o[17],
                    'api.metadata' => $o[18],
                    'api.chat' => $o[19],
                ],
            ],
        ],
    ],
    $o[0],
    [
        -1 => [
            'path' => '/api/login',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\JwtMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:login',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -2 => [
            'path' => '/api/list/{table}[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:list',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -3 => [
            'path' => '/api/view/{table}[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:view',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -4 => [
            'path' => '/api/add/{table}[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:add',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -5 => [
            'path' => '/api/edit/{table}[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:edit',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -6 => [
            'path' => '/api/delete/{table}[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:delete',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'DELETE',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -7 => [
            'path' => '/api/register',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:register',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -8 => [
            'path' => '/api/file/{table}/{param}[/{key:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:file',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -9 => [
            'path' => '/api/export[/{param}[/{table}[/{key:.*}]]]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:export',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -10 => [
            'path' => '/api/upload',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:upload',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -11 => [
            'path' => '/api/jupload',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:jupload',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -12 => [
            'path' => '/api/session',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:session',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -13 => [
            'path' => '/api/lookup[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:lookup',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -14 => [
            'path' => '/api/chart[/{params:.*}]',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:exportchart',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -15 => [
            'path' => '/api/permissions/{level}',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:permissions',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -16 => [
            'path' => '/api/push/{action}',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:push',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -17 => [
            'path' => '/api/twofa/{action}/{user}[/{type}[/{parm}]]',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:twofa',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
                'POST',
                'OPTIONS',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -18 => [
            'path' => '/api/metadata',
            'host' => '',
            'defaults' => [
                'middlewares' => 'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:metadata',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
            ],
            'condition' => '',
            'compiled' => null,
        ],
        -19 => [
            'path' => '/api/chat/{value:[01]}',
            'host' => '',
            'defaults' => [
                'middlewares' => [
                    'PHPMaker2025\\ucarsip\\ApiPermissionMiddleware',
                    'PHPMaker2025\\ucarsip\\JwtMiddleware',
                ],
                '_controller' => 'PHPMaker2025\\ucarsip\\ApiController:chat',
            ],
            'requirements' => [],
            'options' => [
                'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
            ],
            'schemes' => [],
            'methods' => [
                'GET',
            ],
            'condition' => '',
            'compiled' => null,
        ],
    ]
);