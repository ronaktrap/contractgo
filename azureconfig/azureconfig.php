<?php
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
//Connection String
$connectionString = "DefaultEndpointsProtocol=https;AccountName=contractgo01;AccountKey=JzOUKAI+rJZA/AhQP7u57ZxW3QQ1F/J0XYrJt2TT7k2N4IxKakJH/pCTURoqMGGqmaVbOG6LblsCf6m8qhKB6w==;";
// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);