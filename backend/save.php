<?php
/*=================================================================
 * RecordRTC 音頻檔案上傳處理器
 * 
 * 作者: Muaz Khan - www.MuazKhan.com 
 * 授權: MIT License - https://www.webrtc-experiment.com/licence/
 * 文檔: https://github.com/muaz-khan/RecordRTC
 * 
 * 功能: 接收前端上傳的音頻檔案並保存到伺服器
 *================================================================*/

// 允許跨域請求（CORS）
header("Access-Control-Allow-Origin: *");

// 開啟錯誤報告以便調試
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 設定自定義錯誤處理器
set_error_handler("someFunction");

/**
 * 自定義錯誤處理函數
 * 當發生錯誤時顯示友好的錯誤訊息
 * 
 * @param int $errno 錯誤級別
 * @param string $errstr 錯誤訊息
 */
function someFunction($errno, $errstr) {
    echo '<h2>Upload failed.</h2><br>';
    echo '<p>'.$errstr.'</p>';
}

/**
 * 主要的檔案上傳處理函數
 * 驗證檔案、檢查權限、並將檔案保存到指定目錄
 */
function selfInvoker()
{
    /*---------------------------------------------------------------
     * 檔案名稱驗證
     * 檢查是否提供了有效的檔案名稱
     *--------------------------------------------------------------*/
    
    // 檢查是否提供了音頻或視頻檔案名稱
    if (!isset($_POST['audio-filename']) && !isset($_POST['video-filename'])) {
        echo 'Empty file name.';
        return;
    }

    // 確保檔案名稱不為空
    if (empty($_POST['audio-filename']) && empty($_POST['video-filename'])) {
        echo 'Empty file name.';
        return;
    }

    /*---------------------------------------------------------------
     * 安全性檢查（目前已禁用）
     * 可用於限制只允許特定前綴的檔案上傳
     *--------------------------------------------------------------*/
    
    // 檢查音頻檔案名稱前綴（目前禁用：false &&）
    if (false && isset($_POST['audio-filename']) && strrpos($_POST['audio-filename'], "RecordRTC-") !== 0) {
        echo 'File name must start with "RecordRTC-"';
        return;
    }

    // 檢查視頻檔案名稱前綴（目前禁用：false &&）
    if (false && isset($_POST['video-filename']) && strrpos($_POST['video-filename'], "RecordRTC-") !== 0) {
        echo 'File name must start with "RecordRTC-"';
        return;
    }
    
    /*---------------------------------------------------------------
     * 檔案資訊提取
     * 根據檔案類型提取相應的檔案名稱和臨時檔案路徑
     *--------------------------------------------------------------*/
    
    $fileName = '';   // 檔案名稱
    $tempName = '';   // 臨時檔案路徑
    $file_idx = '';   // 檔案索引鍵
    
    // 判斷是音頻還是視頻檔案
    if (!empty($_FILES['audio-blob'])) {
        $file_idx = 'audio-blob';                    // 音頻檔案
        $fileName = $_POST['audio-filename'];        // 音頻檔案名稱
        $tempName = $_FILES[$file_idx]['tmp_name'];  // 臨時檔案路徑
    } else {
        $file_idx = 'video-blob';                    // 視頻檔案
        $fileName = $_POST['video-filename'];        // 視頻檔案名稱
        $tempName = $_FILES[$file_idx]['tmp_name'];  // 臨時檔案路徑
    }
    
    /*---------------------------------------------------------------
     * 檔案有效性檢查
     * 確保檔案名稱和臨時檔案路徑都有效
     *--------------------------------------------------------------*/
    
    if (empty($fileName) || empty($tempName)) {
        if(empty($tempName)) {
            echo 'Invalid temp_name: '.$tempName;
            return;
        }

        echo 'Invalid file name: '.$fileName;
        return;
    }

    /*---------------------------------------------------------------
     * 檔案大小限制檢查（目前已註解）
     * 可用於限制上傳檔案的大小
     *--------------------------------------------------------------*/
    
    /*
    $upload_max_filesize = return_bytes(ini_get('upload_max_filesize'));

    if ($_FILES[$file_idx]['size'] > $upload_max_filesize) {
       echo 'upload_max_filesize exceeded.';
       return;
    }

    $post_max_size = return_bytes(ini_get('post_max_size'));

    if ($_FILES[$file_idx]['size'] > $post_max_size) {
       echo 'post_max_size exceeded.';
       return;
    }
    */

    /*---------------------------------------------------------------
     * 設定檔案保存路徑
     * 檔案將保存到 public/uploads/ 目錄
     *--------------------------------------------------------------*/
    
    $filePath = '../public/uploads/' . $fileName; // 檔案保存路徑
    
    /*---------------------------------------------------------------
     * 檔案副檔名安全檢查
     * 只允許指定的音頻/視頻格式上傳
     *--------------------------------------------------------------*/
    
    // 允許的檔案副檔名清單
    $allowed = array(
        'webm',  // WebM 視頻格式
        'wav',   // WAV 音頻格式
        'mp4',   // MP4 視頻格式
        'mkv',   // MKV 視頻格式
        'mp3',   // MP3 音頻格式
        'ogg'    // OGG 音頻格式
    );
    
    $extension = pathinfo($filePath, PATHINFO_EXTENSION); // 提取副檔名
    
    // 檢查副檔名是否在允許清單中
    if (!$extension || empty($extension) || !in_array($extension, $allowed)) {
        echo 'Invalid file extension: '.$extension;
        return;
    }
    
    /*---------------------------------------------------------------
     * 檔案移動和錯誤處理
     * 將臨時檔案移動到最終目錄，並處理可能的錯誤
     *--------------------------------------------------------------*/
    
    // 嘗試將檔案從臨時目錄移動到目標目錄
    if (!move_uploaded_file($tempName, $filePath)) {
        // 檔案移動失敗時的詳細錯誤處理
        if(!empty($_FILES["file"]["error"])) {
            // PHP 檔案上傳錯誤代碼對應表
            $listOfErrors = array(
                '1' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                '2' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                '3' => 'The uploaded file was only partially uploaded.',
                '4' => 'No file was uploaded.',
                '6' => 'Missing a temporary folder. Introduced in PHP 5.0.3.',
                '7' => 'Failed to write file to disk. Introduced in PHP 5.1.0.',
                '8' => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'
            );
            $error = $_FILES["file"]["error"];

            // 顯示對應的錯誤訊息
            if(!empty($listOfErrors[$error])) {
                echo $listOfErrors[$error];
            }
            else {
                echo 'Not uploaded because of error #'.$_FILES["file"]["error"];
            }
        }
        else {
            echo 'Problem saving file: '.$tempName;
        }
        return;
    }
    
    /*---------------------------------------------------------------
     * 上傳成功
     * 返回成功訊息給前端
     *--------------------------------------------------------------*/
    
    echo 'success'; // 回傳成功狀態
}

/*=================================================================
 * 已註解的輔助函數
 * 用於處理 PHP 配置中的檔案大小設定
 *================================================================*/

/*
// 將 PHP ini 設定值轉換為位元組數
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // 'G' 修飾符從 PHP 5.1.0 開始可用
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}
*/

/*=================================================================
 * 執行主要處理函數
 * 啟動檔案上傳處理流程
 *================================================================*/

selfInvoker(); // 調用主要處理函數
?>
