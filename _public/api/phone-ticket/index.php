---
layout: php
---

<?php

try {
    include_once "env.php";

    class PhoneTicketHelper {
        static function sendEmail($html) {
            try {
                global $env;

                $to = $env['modal_form_email_to'];
                $subject = "Заполнена форма";
                $message = $html;
                $headers = implode("\r\n", [
                    'MIME-Version: 1.0',
                    'Content-type:text/html;charset=UTF-8',
                    'From: Kung Consulting <no-reply@kungconsulting.com>',
                ]);

                $is_send = mail($to, $subject, $message, $headers);

                echo $is_send
                    ? "<p>Форма отправлена на почту</p>"
                    : "<p>НЕ ОТПРАВЛЕНА НА ПОЧТУ</p>";
            }
            catch(Throwable $exception) {
                echo "<pre>";
                print_r($exception);
                echo "</pre>";
            }
        }

        static function getHtml($data = []) {
            $td_style = "border: 1px solid black; padding: 8px;";
        
            ob_start();

            ?>

            <table style="border-collapse: collapse;">
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>Имя:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['name'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>Телефон:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['phone'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>E-mail:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['email'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>" colspan="2">
                            <b>Сообщение:</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>" colspan="2">
                            <pre><?= $data['message'] ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>Имя формы:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['location'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>Дата и время отправки:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['datetime'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>IP:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['ip'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>HTTP REFERER:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['http_referer'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="<?= $td_style ?>">
                            <b>User agent:</b>
                        </td>
                        <td style="<?= $td_style ?>">
                            <?= $data['agent'] ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php

            $html = ob_get_clean();

            return $html;
        }

        static function getHtmlForTelegram($data = []) {
            ob_start();

            ?>

<b>Имя</b>:

<code><?= $data['name'] ?></code>

<b>Телефон</b>:

<code><?= $data['phone'] ?></code>

<b>E-mail</b>:

<code><?= $data['email'] ?></code>

<b>Сообщение</b>:

<code><?= $data['message']  ?></code>

<b>Имя формы</b>:

<code><?= $data['location']  ?></code>

<b>Дата и время отправки</b>:

<code><?= $data['datetime']  ?></code>

<b>IP</b>:

<code><?= $data['ip']  ?></code>

<b>HTTP REFERER</b>:

<code><?= $data['http_referer']  ?></code>

<b>User agent</b>:

<code><?= $data['agent']  ?></code>

            <?php

            $html = ob_get_clean();
            return $html;
        }

        static function getData() {
            $data = [
                'datetime' => date('Y-m-d H:i:s'),
                'name' => htmlspecialchars(isset($_POST['user_name']) ? $_POST['user_name'] : "не указано"),
                'phone' => htmlspecialchars(isset($_POST['user_phone']) ? $_POST['user_phone'] : "не указано"),
                'email' => htmlspecialchars(isset($_POST['user_email']) ? $_POST['user_email'] : "не указано"),
                'message' => htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : "не указано"),
                'org' => htmlspecialchars(isset($_POST['org']) ? $_POST['org'] : "не указано"),
                'location' => htmlspecialchars(isset($_POST['location']) ? $_POST['location'] : "не указано"),
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
                'agent' => $_SERVER['HTTP_USER_AGENT'],
            ];
            return $data;
        }

        static function sendMessageToTelegramChat($html) {
            try {
                global $env;

                $token = $env['telegram_bot_token'];
                $chat_id = $env['telegram_chat_id'];

                $url = "https://api.telegram.org/bot{$token}/sendMessage";

                $data = [
                    'chat_id' => $chat_id,
                    'text' => $html,
                    'parse_mode' => 'HTML',
                ];

                $options = [
                    'http' => [
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($data),
                    ],
                ];

                $context  = stream_context_create($options);
                $result = file_get_contents($url, false, $context);

                echo $result === FALSE
                    ? "<p>НЕ ОТПРАВЛЕНА ФОРМА В ТЕЛЕГРАМ!</p>"
                    : "<p>Форма отправлена в телеграм</p>";
            }
            catch(Throwable $exception) {
                echo "<pre>";
                print_r($exception);
                echo "</pre>";
            }
        }

        static function saveToDatabase($data) {
            try {
                $HOME = strlen($_SERVER['DOCUMENT_ROOT']) != 0 ? $_SERVER['DOCUMENT_ROOT'] : "";

                $pdo = new PDO("sqlite:$HOME/../{{ site.data.env.database_name }}.sqlite");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "CREATE TABLE IF NOT EXISTS KC_DOC_FormData (
                            datetime DATETIME,
                            name TEXT,
                            phone TEXT,
                            email TEXT,
                            message TEXT,
                            org TEXT,
                            ip TEXT,
                            location TEXT,
                            http_referer TEXT,
                            agent TEXT
                        )
                        ";

                $sth = $pdo->prepare($sql);
                $sth->execute();

                $sql = "INSERT INTO KC_DOC_FormData
                            (
                                datetime,
                                name,
                                phone,
                                email,
                                message,
                                org,
                                location,
                                ip,
                                http_referer,
                                agent
                            )
                        VALUES
                            (
                                :datetime,
                                :name,
                                :phone,
                                :email,
                                :message,
                                :org,
                                :location,
                                :ip,
                                :http_referer,
                                :agent
                            )
                        ";

                $sth = $pdo->prepare($sql);
                $sth->execute([
                    ':datetime' => $data['datetime'],
                    ':name' => $data['name'],
                    ':phone' => $data['phone'],
                    ':email' => $data['email'],
                    ':message' => $data['message'],
                    ':org' => $data['org'],
                    ':location' => $data['location'],
                    ':ip' => $data['ip'],
                    ':http_referer' => $data['http_referer'],
                    ':agent' => $data['agent'],
                ]);
            }
            catch(Throwable $exception) {
                echo "<pre>";
                print_r($exception);
                echo "</pre>";
            }
        }

        static function redirect() {

            ?>

            <script>
                function sleep(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }

                (async function() {
                    await sleep(1000);
                    window.location.replace(document.referrer || '/');
                })();
            </script>

            <?php

        }
    }

    $data = PhoneTicketHelper::getData();

    PhoneTicketHelper::saveToDatabase($data);

    $html = PhoneTicketHelper::getHtml($data);
    PhoneTicketHelper::sendEmail($html);

    $html = PhoneTicketHelper::getHtmlForTelegram($data);
    PhoneTicketHelper::sendMessageToTelegramChat($html);

    PhoneTicketHelper::redirect();
}
catch(Throwable $exception) {
    echo "<pre>";
    print_r($exception);
    echo "</pre>";
}
