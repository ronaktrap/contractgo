<link rel="stylesheet" href="css/bootstrap.min.css">

<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

//Initialize Connection
include './azureconfig/azureconfig.php';

//**************** Important Notes ****************//
// Only upload a file that has no white space in its name.
//**************** Important Notes ****************//

if (isset($_POST["upload"])) { //Comment this condition if call thid page as API
    //Get and Set File name
    $filename = $_FILES['FileName']['tmp_name'];
    $newFileToUpload = generateRandomString() . "-" . str_replace(" ","",$_FILES['FileName']['name']); //Remove white space from file name when upload file to Azure

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
        $contentType = (!empty($fileext) && $fileext == "txt" ? 'text/plain; charset=UTF-8' : "application/pdf");
        $options = new CreateBlockBlobOptions();
        $options->setContentType($contentType);
        $blobClient->createBlockBlob($containerName, $newFileToUpload, $content, $options); //Write content to text file
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
} 
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-4 ml-3 mt-3" style="border: 1px solid #a6a6a6;">
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="FileName">Upload File: (<span style="color:red;">Upload only .txt or .pdf file.</span>)</label>
                    <input type="file" name="FileName" id="FileName" accept=".txt,.pdf" class="form-control" required="" style="height: calc(1.8em + .75rem + 2px);">
                </div>
                <button type="submit" name="upload" id="btnUpload" class="btn btn-primary btn-sm">Submit</button>
            </form>
        </div>
    </div>
    <hr/>
</div>

<!--List of uploaded files-->
<?php
    include './TextFileList.php';
?>

<script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $("body").on("click", "#btnUpload", function () {
        var allowedFiles = [".txt", ".pdf"];
        var fileUpload = $("#FileName");
        if(fileUpload.val() !== "")
        {
            var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + allowedFiles.join('|') + ")$");
            if (!regex.test(fileUpload.val().toLowerCase())) {
                alert("Please upload files having extensions: " + allowedFiles.join(', ') + " only.");
                $("#FileName").val("");
                return false;
            }
            return true;
        }
    });
</script>
