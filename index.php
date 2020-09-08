<?php

include './conn/config.php';
require_once 'vendor/autoload.php';
require("./phpmailer/PHPMailerAutoload.php");
require_once "./random_string.php";

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

//Initialize Connection
include './azureconfig/azureconfig.php';

$arr = array();
$id = 0;
$uploadOk = 0;

if (!empty(filter_input(INPUT_POST, "Name")) && !empty(filter_input(INPUT_POST, "Email")) && !empty(filter_input(INPUT_POST, "Subject")) && !empty($_FILES["Filename"])) {

    $name = filter_input(INPUT_POST, "Name");
    $email = filter_input(INPUT_POST, "Email");
    $subject = filter_input(INPUT_POST, "Subject");

    $filename = $_FILES['Filename']['tmp_name'];
    $newFileToUpload = generateRandomString() . "-" . str_replace(" ","",$_FILES['Filename']['name']); //Remove white space from file name when upload file to Azure
    
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("docType", "textDocuments");
    $createContainerOptions->addMetaData("category", "guidance");

    $containerName = "blockblobappuwpzbl"; //Static container name
    //$containerName = "blockblobapp" . generateRandomString(); //Dynamic container name
    try {
        // Create container.
        //$blobClient->createContainer($containerName, $createContainerOptions); //Create container evert time and upload file
        
        $file = basename($newFileToUpload); //Get extension from new generated file name
        $ext = new SplFileInfo($file);
        $fileext = strtolower($ext->getExtension()); //Get extension of file

        //Open file and get contents of selected file for write content into the new Text file.
        $fp = fopen($filename, "r") or die("Unable to open file!");
        $content = file_get_contents($filename);
        fclose($fp);

        //Create blob and write content into the file
        $contentType = 'text/plain; charset=UTF-8';
        $options = new CreateBlockBlobOptions();
        $options->setContentType($contentType);
        $blobClient->createBlockBlob($containerName, $newFileToUpload, $content, $options); //Write content to text file
        
        //***** Start Send Mail With Attachment *****//
        $to = $email;
        $pdfsubject = $subject;
        $pdfbody = "Hello ".$name.",<br/>Please find the attachment.";

        $pdfmail = new PHPMailer();
        $pdfmail->isSMTP();
        $pdfmail->SMTPAuth = TRUE;
        $pdfmail->Host = "smtp.gmail.com";
        $pdfmail->SMTPSecure = "ssl"; //tls or ssl
        $pdfmail->Port = 465; //587 or 465
        $pdfmail->Username = "hmwork2000@gmail.com";
        $pdfmail->Password = '$work@2018';
        $pdfmail->setFrom('hmwork2000@gmail.com', 'ContractGo');
        $pdfmail->addAddress($to);
        $pdfmail->Subject = $pdfsubject;
        $pdfmail->WordWrap = 80;
        $pdfmail->isHTML(true);
        $pdfmail->msgHTML($pdfbody);

        //Attach pdf file to mail
        $path = "https://contractgo01.blob.core.windows.net/".$containerName."/".$newFileToUpload;
        $pdfmail->addAttachment($filename,$newFileToUpload);

        if (!$pdfmail->send()) {
            $arr["Status"] = "fail";
            $uploadOk = 0;
        } else {
            $arr["Status"] = "success";
            $uploadOk = 1;
        }
    } catch (ServiceException $e) {
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br />";
    } catch (InvalidArgumentTypeException $e) {
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br />";
    }
    
    //***** End Send Mail With Attachment *****//
    //Response
    if ($uploadOk == 1) {
        $info[] = $arr;
        echo json_encode($info);
    } else {
        $info[] = $arr;
        echo json_encode($info);
    }
} else {
    $arr["Status"] = "fail";
    $info[] = $arr;
    echo json_encode($info);
}