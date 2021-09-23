<?php

session_start();
if(isset($_SESSION['file_data']))
{
    $encoded_data = $_SESSION['file_data'];
    $file_data = json_decode($encoded_data,true);
    $file_name_arr = explode(".",$file_data['data']['original_name']);
    $count = count($file_name_arr);
    unset($file_name_arr[$count-1]);
    $file_name_without_extension = implode(".",$file_name_arr);
    ?>

    <html>
        <head>
            <title>Download File</title>
        </head>

        <body style="text-align:center; padding:30px">
            Original File Size: <?php echo $file_data['data']['original_size']; ?><br><br>
            New File Size: <?php echo $file_data['data']['new_size']; ?><br><br><br>
            <a href="/PdfTools/final/<?php echo $file_data['data']['identifier'].".pdf" ?>" download = "<?php echo $file_name_without_extension."_compressed.pdf"; ?>" >Download Your File</a>
        </body>

    </html>

    <?php
}
else
{
    echo "Sorry, something went wrong";
}

?>