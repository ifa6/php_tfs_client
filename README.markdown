PHP TFS Client
==========

Usage
------------

```php
$client = new Tfs\Client($appkey);
$response = $client->save($fileContent);
if ( $response->isOk() ) {
    $tfsFile = $response->getResult()->TFS_FILE_NAME;
    echo "成功写入 Tfs 文件：" . $tfsFile;
    $response = $client->fetch($tfsFile);
    if ( $response->isOk() ) {
        echo "Tfs 文件内容：" . $response->getContent();
    }
} else {
    error_log(sprintf("写入错误 [%s]: %s\n", $response->getCode(), $response->getMessage()));
}
```

