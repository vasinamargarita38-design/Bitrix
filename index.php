<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$yandexToken = 'y0__wgBEJKD8dcDGNGxQiDV6O3VF4sVTdJykmQTfby48uIDG3V_d63C';
$yandexRoot  = 'disk:/';
$alertMessage = '';
$alertClass   = 'success';

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if ($request->isPost() && check_bitrix_sessid()) {
    $action = $request->getPost('act');

    if ($action === 'c' && isset($_FILES['yfile'])) {
        $file = $_FILES['yfile'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $remotePath = $yandexRoot . $file['name'];
            $getUrl = 'https://cloud-api.yandex.net/v1/disk/resources/upload?path='
                     . urlencode($remotePath) . '&overwrite=true';

            $http = new \Bitrix\Main\Web\HttpClient(['timeout' => 30, 'skipCertCheck' => true]);
            $http->setHeader('Authorization', 'OAuth ' . $yandexToken);
            $resp = $http->get($getUrl);

            if ($http->getStatus() === 200) {
                $data = \Bitrix\Main\Web\Json::decode($resp);
                $uploadUrl = $data['href'] ?? null;

                if ($uploadUrl) {
                    $content = file_get_contents($file['tmp_name']);
                    $ctx = stream_context_create([
                        'http' => [
                            'method'        => 'PUT',
                            'header'        =>
                                "Authorization: OAuth $yandexToken\r\n" .
                                "Content-Type: application/octet-stream\r\n" .
                                "Content-Length: " . strlen($content) . "\r\n",
                            'content'       => $content,
                            'ignore_errors' => true,
                            'timeout'       => 60
                        ],
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                    ]);
                    file_get_contents($uploadUrl, false, $ctx);
                    $statusLine = $http_response_header[0] ?? '';

                    if (strpos($statusLine, '201') !== false || strpos($statusLine, '202') !== false) {
                        $alertMessage = 'Файл успешно загружен.';
                    } else {
                        $alertMessage = 'Ошибка загрузки. Ответ сервера: ' . $statusLine;
                        $alertClass = 'danger';
                    }
                } else {
                    $alertMessage = 'Не удалось получить URL загрузки.';
                    $alertClass = 'danger';
                }
            } else {
                $alertMessage = 'Ошибка получения ссылки загрузки. Код: ' . $http->getStatus();
                $alertClass = 'danger';
            }
        }
    }

    if ($action === 'u') {
        $oldPath = $request->getPost('item_path');
        $newName = $request->getPost('new_name');
        if (!empty($oldPath) && !empty($newName)) {
            $destinationPath = dirname($oldPath) . '/' . $newName;
            $moveUrl = 'https://cloud-api.yandex.net/v1/disk/resources/move'
                      . '?from=' . urlencode($oldPath)
                      . '&path=' . urlencode($destinationPath)
                      . '&overwrite=true';

            $http = new \Bitrix\Main\Web\HttpClient(['timeout' => 30, 'skipCertCheck' => true]);
            $http->setHeader('Authorization', 'OAuth ' . $yandexToken);
            $http->post($moveUrl, null);

            if (in_array($http->getStatus(), [201, 202])) {
                $alertMessage = 'Файл успешно переименован.';
            } else {
                $alertMessage = 'Ошибка переименования. Код: ' . $http->getStatus();
                $alertClass = 'danger';
            }
        }
    }

    if ($action === 'd') {
        $deletePath = $request->getPost('item_path');
        if (!empty($deletePath)) {
            $delUrl = 'https://cloud-api.yandex.net/v1/disk/resources?path='
                     . urlencode($deletePath) . '&permanently=true';

            $http = new \Bitrix\Main\Web\HttpClient(['timeout' => 30, 'skipCertCheck' => true]);
            $http->setHeader('Authorization', 'OAuth ' . $yandexToken);
            $http->query('DELETE', $delUrl);

            if ($http->getStatus() === 204 || $http->getStatus() === 202) {
                $alertMessage = 'Файл удалён с Яндекс.Диска.';
            } else {
                $alertMessage = 'Ошибка удаления. Код: ' . $http->getStatus();
                $alertClass = 'danger';
            }
        }
    }
}

$items = [];
$listUrl = 'https://cloud-api.yandex.net/v1/disk/resources?path='
          . urlencode($yandexRoot) . '&limit=100';

$http = new \Bitrix\Main\Web\HttpClient(['timeout' => 30, 'skipCertCheck' => true]);
$http->setHeader('Authorization', 'OAuth ' . $yandexToken);
$listResp = $http->get($listUrl);

if ($http->getStatus() === 200) {
    $parsed = \Bitrix\Main\Web\Json::decode($listResp);
    $items = $parsed['_embedded']['items'] ?? [];
} 
?>

<div style="padding: 20px; max-width: 1100px; margin: 20px auto; font-family: Arial, sans-serif; color: #000; background: #fff; border: 1px solid #dee2e6; border-radius: 6px;">
    <h3 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Панель CRUD: Яндекс.Диск API</h3>

    <?php if (!empty($alertMessage)): ?>
        <div style="padding: 12px; margin-bottom: 20px; border-radius: 4px; background: <?= ($alertClass === 'success') ? '#d4edda' : '#f8d7da' ?>; color: <?= ($alertClass === 'success') ? '#155724' : '#721c24' ?>;">
            <?= htmlspecialcharsbx($alertMessage) ?>
        </div>
    <?php endif; ?>

    <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; margin-bottom: 25px;">
        <h5 style="margin-top: 0;"><b>Загрузить новый файл на Диск:</b></h5>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="act" value="c">
            <input type="file" name="yfile" required style="padding: 4px; background: #fff; border: 1px solid #ced4da; border-radius: 4px;">
            <button type="submit" style="background: #28a745; color:#fff; border:0; padding:8px 20px; border-radius:4px; font-weight: bold; cursor:pointer;">Загрузить</button>
        </form>
    </div>

    <h5 style="margin-bottom: 10px;"><b>Содержимое корневого каталога (disk:/):</b></h5>
    <table style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #dee2e6;">
        <thead>
            <tr style="background: #f1f3f5; border-bottom: 2px solid #dee2e6; text-align: left;">
                <th style="padding: 12px;">Файл (редактировать имя)</th>
                <th style="padding: 12px;">Тип</th>
                <th style="padding: 12px;">Размер (Кб)</th>
                <th style="padding: 12px; text-align: right;">Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #6c757d; font-style: italic;">Нет файлов или не удалось загрузить список.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $fileItem): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 12px;">
                            <form method="POST" style="display: flex; gap: 5px; margin: 0;">
                                <?= bitrix_sessid_post() ?>
                                <input type="hidden" name="act" value="u">
                                <input type="hidden" name="item_path" value="<?= htmlspecialcharsbx($fileItem['path']) ?>">
                                <input type="text" name="new_name" value="<?= htmlspecialcharsbx($fileItem['name']) ?>" style="width: 220px; padding: 4px;">
                                <button type="submit" style="background: #007bff; color:#fff; border:0; padding:4px 12px; border-radius:3px; cursor:pointer;">Переименовать</button>
                            </form>
                        </td>
                        <td style="padding: 12px;"><?= htmlspecialcharsbx($fileItem['type']) ?></td>
                        <td style="padding: 12px;"><?= round($fileItem['size'] / 1024, 1) ?></td>
                        <td style="padding: 12px; text-align: right;">
                            <form method="POST" onsubmit="return confirm('Удалить файл безвозвратно?');" style="display:inline;">
                                <?= bitrix_sessid_post() ?>
                                <input type="hidden" name="act" value="d">
                                <input type="hidden" name="item_path" value="<?= htmlspecialcharsbx($fileItem['path']) ?>">
                                <button type="submit" style="background: #dc3545; color:#fff; border:0; padding:4px 12px; border-radius:3px; cursor:pointer;">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
