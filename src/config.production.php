<?php

/**
 * PHPMaker configuration file (Production)
 */

return [

    // Debug
    "DEBUG" => false, // Enabled
    "REPORT_ALL_ERRORS" => false, // Treat PHP warnings and notices as errors
    "LOG_TO_FILE" => false, // Log error and SQL to file
    "DEBUG_BAR" => true, // Enabled by default

    // Maintenance mode
    "MAINTENANCE_MODE" => false,
    "MAINTENANCE_RETRY_AFTER" => 300, // Retry-After (seconds)
    "MAINTENANCE_TEMPLATE" => "Error.php", // Template

    // Connection info
    "Databases" => [
        "DB" => ["id" => "DB", "type" => "MYSQL", "qs" => "`", "qe" => "`", "host" => "localhost", "port" => 3306, "user" => "root", "password" => "", "dbname" => "db_ucarsip"]
    ],

    // SMTP server settings
    "SMTP" => [
        "SERVER" => "localhost", // SMTP server
        "SERVER_PORT" => 25, // SMTP server port
        "SERVER_USERNAME" => "", // SMTP server user name
        "SERVER_PASSWORD" => "", // SMTP server password
        "SECURE_OPTION" => "", // TLS
        "OPTIONS" => [], // Other options
    ],

    // PHPMailer
    "USE_PHPMAILER" => false, // Use PHPMailer

    // PHPMailer OAuthTokenProvider
    "PHPMAILER_OAUTH" => null,

    // Mailer DSN, e.g. Amazon SES, "ses+smtp://USERNAME:PASSWORD@default?region=REGION"
    "MAILER_DSN" => "",

    // Transport factory, e.g. \Symfony\Component\Mailer\Bridge\Amazon\Transport\SesTransportFactory
    "TRANSPORT_FACTORY" => "",

    // Authenticators for Symfony Mailer
    "MAILER_AUTHENTICATORS" => null,

    // JWT
    "JWT" => [
        "SECRET_KEY" => "9vltFKz97bgUHSbMzfgWGfyuvvnJqqDW2j4sSg4Zl8M=", // JWT secret key
        "ALGORITHM" => "HS512", // JWT algorithm
        "AUTH_HEADER" => "Authorization", // API authentication header (Note: The "Authorization" header is removed by IIS, use "X-Authorization" instead.)
        "NOT_BEFORE_TIME" => 0, // API access time before login
        "EXPIRE_TIME" => 600 // API expire time
    ],
];
