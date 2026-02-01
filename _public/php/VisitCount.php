---
layout: php
---

<?php

class VisitCount {
    static function logVisit() {
        $data = [
            'datetime' => date('Y-m-d_H:i:s'),
            'uri' => $_SERVER['REQUEST_URI'],
            'http_referer' => $_SERVER['HTTP_REFERER'],
            'ip' => implode(
                ",",
                array_filter(
                    [
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_CLIENT_IP'],
                        $_SERVER['HTTP_X_FORWARDED_FOR'],
                    ],
                ),
            ),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ];

        $pdo = VisitCount::getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // CREATE TABLE

        $sql = "CREATE TABLE IF NOT EXISTS KC_DOC_Visit (
                    datetime DATETIME,
                    ip TEXT,
                    uri TEXT,
                    http_referer TEXT,
                    user_agent TEXT
                )
                ";

        $sth = $pdo->prepare($sql);
        $sth->execute();

        // INSERT INTO TABLE

        $sql = "INSERT INTO
                    KC_DOC_Visit
                    (datetime, ip, uri, http_referer, user_agent)
                VALUES
                    (:datetime, :ip, :uri, :http_referer, :user_agent)
                ";

        $sth = $pdo->prepare($sql);
        $sth->execute([
            ':datetime' => $data['datetime'],
            ':ip' => $data['ip'],
            ':uri' => $data['uri'],
            ':http_referer' => $data['http_referer'],
            ':user_agent' => $data['user_agent'],
        ]);
    }

    static function updateVisitCount() {
        $uri = $_SERVER['REQUEST_URI'] ?? '-';
        $count = 1;
        $datetime = date('Y-m-d_H:i:s');

        $HOME = strlen($_SERVER['DOCUMENT_ROOT']) != 0 ? $_SERVER['DOCUMENT_ROOT'] : "";

        $pdo = VisitCount::getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "CREATE TABLE IF NOT EXISTS KC_LST_VisitCount (
                    uri TEXT,
                    count INT,
                    created_at DATETIME,
                    updated_at DATETIME
                )
                ";

        $sth = $pdo->prepare($sql);
        $sth->execute();

        $sql = "SELECT
                    *
                FROM
                    KC_LST_VisitCount
                WHERE
                    uri = :uri
                ";

        $sth = $pdo->prepare($sql);
        $sth->execute([
            'uri' => $uri,
        ]);
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        $isFound = $result != null;

        if ($isFound) {
            $count = $result['count'] + 1;

            $sql = "UPDATE
                        KC_LST_VisitCount
                    SET
                        count = :count,
                        updated_at = :datetime
                    WHERE
                        uri = :uri
                    ";

            $sth = $pdo->prepare($sql);
            $sth->execute([
                'uri' => $uri,
                'count' => $count,
                'datetime' => $datetime,
            ]);
        }

        if (!$isFound) {
            $sql = "INSERT INTO
                        KC_LST_VisitCount
                        (uri, count, created_at, updated_at)
                    VALUES
                        (:uri, :count, :datetime, :datetime)
                    ";

            $sth = $pdo->prepare($sql);
            $sth->execute([
                'uri' => $uri,
                'count' => $count,
                'datetime' => $datetime,
            ]);
        }
    }

    static function getPDO () {
        $HOME = strlen($_SERVER['DOCUMENT_ROOT']) != 0 ? $_SERVER['DOCUMENT_ROOT'] : "";

        return new PDO("sqlite:$HOME/../{{ site.data.env.database_name }}.sqlite");
    }
}

VisitCount::logVisit();
VisitCount::updateVisitCount();
