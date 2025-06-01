<?php

namespace PHPMaker2025\ucarsip;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Illuminate\Support\Collection;

class ConnectionFactory
{

    public static function create(string $dbid = "DB", ?EventManager $eventManager = null)
    {
        $info = Db($dbid);
        $event = new DatabaseConnectingEvent(arguments: $info);
        DispatchEvent($event, DatabaseConnectingEvent::NAME);
        $info = $event->getArguments();
        $dbtype = $info["type"] ?? "";
        if ($dbtype == "MYSQL") {
            $info["driver"] ??= "pdo_mysql";
            if (Config("MYSQL_CHARSET") != "" && !array_key_exists("charset", $info)) {
                $info["charset"] = Config("MYSQL_CHARSET");
            }
            if ($info["driver"] == "pdo_mysql") {
                $keys = [
                    \PDO::MYSQL_ATTR_SSL_CA,
                    \PDO::MYSQL_ATTR_SSL_CAPATH,
                    \PDO::MYSQL_ATTR_SSL_CERT,
                    \PDO::MYSQL_ATTR_SSL_CIPHER,
                    \PDO::MYSQL_ATTR_SSL_KEY
                ];
                if (
                    defined("PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT")
                    && Collection::make($info["driverOptions"] ?? [])->keys()->contains(fn ($v) => in_array($v, $keys))
                ) { // SSL
                    $info["driverOptions"][\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] ??= false;
                }
            } elseif ($info["driver"] == "mysqli") {
                if (Collection::make($info)->keys()->contains(fn ($v) => StartsString("ssl_", $v))) { // SSL
                    $info["driverOptions"]["flags"] = ($info["driverOptions"]["flags"] ?? 0) | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                }
            }
        } elseif ($dbtype == "POSTGRESQL") {
            $info["driver"] ??= "pdo_pgsql";
            if (Config("POSTGRESQL_CHARSET") != "" && !array_key_exists("charset", $info)) {
                $info["charset"] = Config("POSTGRESQL_CHARSET");
            }
        } elseif ($dbtype == "MSSQL") {
            $info["driver"] ??= "sqlsrv";
            $info["driverOptions"] ??= []; // See https://docs.microsoft.com/en-us/sql/connect/php/connection-options?view=sql-server-ver16
            // Use TransactionIsolation = SQLSRV_TXN_READ_UNCOMMITTED to avoid record locking
            // https://docs.microsoft.com/en-us/sql/t-sql/statements/set-transaction-isolation-level-transact-sql?view=sql-server-ver15
            $info["driverOptions"]["TrustServerCertificate"] = 1;
            if ($info["driver"] == "sqlsrv") {
                $info["driverOptions"]["TransactionIsolation"] = 1; // SQLSRV_TXN_READ_UNCOMMITTED
                $info["driverOptions"]["CharacterSet"] = "UTF-8";
            } elseif ($info["driver"] == "pdo_sqlsrv") {
                $info["driverOptions"]["TransactionIsolation"] = "READ_UNCOMMITTED"; // PDO::SQLSRV_TXN_READ_UNCOMMITTED
            }
        } elseif ($dbtype == "SQLITE") {
            $info["driver"] ??= "pdo_sqlite";
        } elseif ($dbtype == "ORACLE") {
            $info["driver"] = "oci8";
            if (Config("ORACLE_CHARSET") != "" && !array_key_exists("charset", $info)) {
                $info["charset"] = Config("ORACLE_CHARSET");
            }
        }

        // Decrypt user name and password
        if (array_key_exists("user", $info) && Config("ENCRYPT_USER_NAME_AND_PASSWORD")) {
            $info["user"] = PhpDecrypt($info["user"]);
        }
        if (array_key_exists("password", $info) && Config("ENCRYPT_USER_NAME_AND_PASSWORD")) {
            $info["password"] = PhpDecrypt($info["password"]);
        }

        // Configuration
        $config = new Configuration(); // \Doctrine\DBAL\Configuration
        $config->setMiddlewares(Container("connection.middlewares"));

        // $config->setResultCache(Container("result.cache"));

        // Connect
        if ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL" || $dbtype == "ORACLE") {
            $dbtimezone = @$info["timezone"] ?: Config("DB_TIME_ZONE");
            $conn = DriverManager::getConnection($info, $config, $eventManager);
            if ($dbtype == "ORACLE") {
                $oraVars = [
                    "NLS_TIME_FORMAT" => "HH24:MI:SS",
                    "NLS_DATE_FORMAT" => "YYYY-MM-DD HH24:MI:SS",
                    "NLS_TIMESTAMP_FORMAT" => "YYYY-MM-DD HH24:MI:SS",
                    "NLS_TIMESTAMP_TZ_FORMAT" => "YYYY-MM-DD HH24:MI:SS TZH:TZM",
                    "NLS_NUMERIC_CHARACTERS" => ".,",
                    "CURRENT_SCHEMA" => QuotedName($info["schema"], $dbid)
                ];
                if ($dbtimezone != "") {
                    $oraVars["TIME_ZONE"] = $dbtimezone;
                }
                $vars = [];
                foreach ($oraVars as $option => $value) {
                    if ($option === "CURRENT_SCHEMA") {
                        $vars[] = $option . " = " . $value;
                    } else {
                        $vars[] = $option . " = '" . $value . "'";
                    }
                }
                $conn->executeStatement("ALTER SESSION SET " . implode(" ", $vars));
            }
            if ($dbtype == "MYSQL") {
                if ($dbtimezone != "") {
                    $conn->executeStatement("SET time_zone = '" . $dbtimezone . "'");
                }
            }
            if ($dbtype == "POSTGRESQL") {
                if ($dbtimezone != "") {
                    $conn->executeStatement("SET TIME ZONE '" . $dbtimezone . "'");
                }
            }
            if ($dbtype == "POSTGRESQL") {
                // Set schema
                if (@$info["schema"] != "public" && @$info["schema"] != "") {
                    $schema = !str_contains($info["schema"], ",") ? QuotedName($info["schema"], $dbid) : $info["schema"];
                    $conn->executeStatement("SET search_path TO " . $schema);
                }
            }
        } elseif ($dbtype == "SQLITE") {
            $relpath = @$info["relpath"];
            $dbname = @$info["dbname"];
            if ($relpath == "") {
                $info["path"] = realpath($dbname);
            } elseif (StartsString("\\\\", $relpath) || ContainsString($relpath, ":")) { // Physical path
                $info["path"] = $relpath . $dbname;
            } else { // Relative to app root
                $info["path"] = ServerMapPath($relpath) . $dbname;
            }
            $conn = DriverManager::getConnection($info, $config, $eventManager);
            $conn->getNativeConnection()->sqliteCreateFunction("regexp", "preg_match", 2);
        } elseif ($dbtype == "MSSQL") {
            $conn = DriverManager::getConnection($info, $config, $eventManager);
            // $conn->executeStatement("SET DATEFORMAT ymd"); // Set date format
        }
        $platform = $conn->getDatabasePlatform();
        if ($platform instanceof MySQLPlatform) { // MySQL
            $platform->registerDoctrineTypeMapping("enum", "string"); // Map enum to string
            $platform->registerDoctrineTypeMapping("bytes", "bytes"); // Map bytes to bytes
            $platform->registerDoctrineTypeMapping("geometry", "geometry"); // Map geometry to geometry
        } else if ($platform instanceof PostgreSQLPlatform) { // PostgreSQL
            $platform->registerDoctrineTypeMapping("timetz", "timetz"); // Map timetz to timetz
            $platform->registerDoctrineTypeMapping("geometry", "geometry"); // Map geometry to geometry
            $platform->registerDoctrineTypeMapping("geography", "geography"); // Map geography to geography
        } else if ($platform instanceof SQLServerPlatform) { // Microsoft SQL Server
            $platform->registerDoctrineTypeMapping("geometry", "geometry"); // Map geometry to geometry
            $platform->registerDoctrineTypeMapping("geography", "geography"); // Map geography to geography
            $platform->registerDoctrineTypeMapping("hierarchyid", "hierarchyid"); // Map hierarchyid to hierarchyid
        }
        $event = new DatabaseConnectedEvent($conn, $info);
        DispatchEvent($event, DatabaseConnectedEvent::NAME);
        return $conn;
    }
}
