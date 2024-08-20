<?php
$extractPath = './'; // 解压目标目录  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {  

if(file_exists("./assets/config.php")) {
    include './assets/config.php';
    if (!password_verify($_POST['adminPassword'],$adminPassword)){
        echo '安装失败：管理员密码错误！请重新进入网页后重试';  
        exit;
    }
}
    // 下载
    if(!file_exists("./assets/config.php")) {
        $downloadUrl = 'https://app.3r60.top/project-manager/RisProjectManager_Full.zip'; // 完整版下载地址  
        $zipFilePath = './RisProjectManager_Full.zip'; // 临时存储ZIP文件的路径  
    } else {
        $downloadUrl = 'https://app.3r60.top/project-manager/RisProjectManager_Update.zip'; // 更新版下载地址 
        $zipFilePath = './RisProjectManager_Update.zip'; // 临时存储ZIP文件的路径  
    }
    $file = fopen($zipFilePath, "wb");  
    if ($file) {  
        $ch = curl_init($downloadUrl);  
        curl_setopt($ch, CURLOPT_FILE, $file);  
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        curl_exec($ch);  
        curl_close($ch);  
        fclose($file);  
    }  
  
    // 解压
    $zip = new ZipArchive;  
    $res = $zip->open($zipFilePath);  
    if ($res === TRUE) {  
        $zip->extractTo($extractPath);  
        $zip->close();  
        unlink($zipFilePath); // 删除临时ZIP文件  
    }  

if($downloadUrl == 'https://app.3r60.top/project-manager/RisProjectManager_Full.zip') {
    // 配置
		$Php = '<?php
$adminPassword = "'.password_hash($_POST['adminPassword']).'";
$webName = "'.$_POST['siteName'].'";
';
file_put_contents("./assets/config.php",$Php);
}
    echo '若此处无其他提示则安装完成！点击确认自动跳转（若存在报错代码请发送至群询问）';  
    exit;  
}  

?>
<!DOCTYPE html>  
<html>  
<head>  
    <title>Ris_ProjectManager</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <script src="https://assets.3r60.top/Jquery/jquery-3.5.1.js"></script>  
    <script src="https://assets.3r60.top/v2/package.js?mode=Login"></script>
</head>  
<body>  
    <!-- 主要部分 -->  
    <LoginMain id="LoginMain">
        <div id="LoginDiv">
            <div style="display:flex">
                <img src="https://app.3r60.top/favicon.ico" alt="Account" height="30px" style="margin-bottom:15px">
                <span style="font-size:25px">&nbsp;Ris_ProjectManager</span>
            </div>
            <h2 style="margin-top:0px">欢迎使用瑞思项目管理</h2>
            <form id="installForm">  
            <?php if(!file_exists("./assets/config.php")) :?>
                <input type="text" name="siteName" class="Input" placeholder="设定站点名称" required="">  
                <input type="text" name="adminPassword" class="Input" placeholder="设定管理员密码" required="">  
            <?php else: ?>
                <input type="hidden" name="siteName" class="Input" placeholder="设定站点名称" required=""> 
                更新/重装模式<br> <br>
                <input type="text" name="adminPassword" class="Input" placeholder="验证管理员密码" required=""> 
            <?php endif; ?>
            <br> <br>
                <button type="button" class="Button" id="installButton">下载并安装</button>  
                <div id="progress"></div>  
            </form>  
        </div>
    </LoginMain>
  
    <script>  
        $(document).ready(function() {  
            $('#installButton').click(function() {  
                $('#installButton').text('安装中...').prop('disabled', true);
                var siteName = $('input[name="siteName"]').val();  
                var adminPassword = $('input[name="adminPassword"]').val();  
  
                // 发送AJAX请求  
                $.ajax({  
                    url: 'install.php',  
                    type: 'POST',  
                    data: {  
                        action: 'install',  
                        siteName: siteName,  
                        adminPassword: adminPassword  
                    },  
                    xhrFields: {  
                        onprogress: function(e) {  
                            if (e.lengthComputable) {  
                                var percentComplete = (e.loaded / e.total) * 100;  
                                console.log(percentComplete + '%');  
                                $('#progress').text(percentComplete + '%');  
                            }  
                        }  
                    },  
                    success: function(response) {  
                            alert(response);  
                            location.href = "./index.php";
                    },  
                    error: function() {  
                        alert('请检查您的网络连接或稍后重试。');  
                        $('#installButton').text('下载并安装').prop('disabled', false);
                    }  
                });  
            });  
        });  
    </script>
</body>
</html>
