<?php

if(isset($_POST['sub']))
{
    define('KB', 1000);
    define('MB', 1000000);

    $file_name = $_FILES['pdf_file']['name'];
    $tmp_name = $_FILES['pdf_file']['tmp_name'];
    $size = $_FILES['pdf_file']['size'];
    $error = $_FILES['pdf_file']['error'];

    $allowed_arr = ['pdf'];
    $unique_identifier = generate_unique_name();
    $dir = "/var/www/html/PdfTools/temp_space/";
    $destination = $dir.$unique_identifier;
    $extension = end(explode(".",$file_name));

    if($error)
    {
        $response['status'] = "Fail";
        $response['message'] = "Something went wrong: 'ERROR CODE $error'";
    }
    else if(!in_array($extension,$allowed_arr))
    {
        $response['status'] = "Fail";
        $response['message'] = "Only .pdf files are allowed";
    }
    else
    {
        if($size > 1000000)
        {
            $size_suffix = "MB";
            $initial_size = round(($size/MB),1). "$size_suffix";
        }
        else
        {
            $size_suffix = "KB";
            $initial_size = round(($size/KB),1). "$size_suffix";
        }
        move_uploaded_file($tmp_name, $destination.".pdf");
        shell_exec("pdf2ps $destination.pdf $destination.ps");
        shell_exec("ps2pdf $destination.ps $destination"."_compressed.pdf");
        $new_file_size = filesize($destination."_compressed.pdf");
        if($new_file_size == $size)
        {
            $response[''] = 'Fail';
            $response['message'] = "Sorry we could not shrink your file. It appears the PDF is already compressed";
        }
        else
        {
            $final_size_in_bytes = filesize($destination."_compressed.pdf");
            if($size_suffix == "MB")
            {
                $final_size = round(($final_size_in_bytes/MB),1). "$size_suffix";
            }
            else if($size_suffix == "KB")
            {
                $final_size = round(($final_size_in_bytes/KB),1). "$size_suffix";
            }
            copy($destination."_compressed.pdf","/var/www/html/PdfTools/final/$unique_identifier.pdf");
            $response['status'] = "Success";
            $response['message'] = "File Compressed Successfully";
            $response['data'] = array(
                "original_name" => $file_name,
                "identifier" => $unique_identifier,
                "original_size" => $initial_size,
                "new_size" => $final_size
            );
        }
    }
    cleanup($unique_identifier);
    if($response['status'] == "Success")
    {
        $encoded_response = json_encode($response);
        session_start();
        $_SESSION['file_data'] = $encoded_response;
        header("location:download.php");
    }
    else
    {
        echo json_encode($response);
        die;
    }
}
else
{
    header("location:shrink.php");
}

function generate_unique_name()
{
    $capital_characters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    $small_characters = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
    $characters = array_merge($capital_characters, $small_characters);
    $digits = ['0','1','2','3','4','5','6','7','8','9'];
    $characters = array_merge($digits,$characters);
    $random_length = random_int(5,15);
    $new_name = '';
    create_new_name:
    for($i = 0; $i <= $random_length; $i++)
    {
        $new_name .= $characters[array_rand($characters)];
    }
    if(file_exists("final".$new_name."_compressed.pdf"))
    {
        goto create_new_name;
    }
    return $new_name;
}

function cleanup($name)
{
    $directory = "temp_space/";
    $file = $directory.$name;
    
    if(file_exists($file.".pdf"))
    {
        unlink($file.".pdf");
    }

    if(file_exists($file."_compressed.pdf"))
    {
        unlink($file."_compressed.pdf");
    }

    if(file_exists($file.".ps"))
    {
        unlink($file.".ps");
    }
}

?>