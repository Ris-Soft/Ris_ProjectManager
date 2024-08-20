<?php
// 定义变量
$configPath = __DIR__ . '/config.php';
$projectPath = __DIR__ . '/projects.json';
$localVersion = "1.0.4";

// 引入配置
@include($configPath);

// 获取网站目录
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$dir = dirname($script_name);
$webPath = "$protocol://$host$dir";
$webPath = rtrim($webPath, '/');

// 判断伪静态是否启用
$ps = @file_get_contents($webPath.'/psTest') == "successful";

// 获取项目信息
$projects = json_decode(str_replace('.host',$webPath,file_get_contents($projectPath)));
$projectsArray = (array)$projects;
$projectId = $_GET['id'] ?? null;
$project = $projectId !== null ? $projects->{$projectId} : null;

function checkPassword($adminPassword){
    if (strpos($adminPassword, '$2y$') !== 0 && strpos($adminPassword, '$argon2i$') !== 0 && strpos($adminPassword, '$argon2id$') !== 0 && strpos($adminPassword, '$7$') !== 0) {
        return true;
    }
    return false;
}


function addConfig($variableName, $value, $fileName)
{
    if (!file_exists($fileName) || empty(file_get_contents($fileName))) {
        file_put_contents($fileName, '<?php');
    }
    $$variableName = $value;
    $content = str_replace('?>', '', file_get_contents($fileName));
    if (preg_match('/(\n\s*)?\$' . preg_quote($variableName, '/') . '\s*=\s*(.*);\n?/', $content, $matches)) {
        $newLine = "$matches[1]\$$variableName = '$value';\n";
        $newContent = str_replace($matches[0], $newLine, $content);
    } else {
        file_put_contents($fileName, "\n\$$variableName = '$value';", FILE_APPEND);
    }
    if (isset($newContent)) {
        file_put_contents($fileName, $newContent);
    }
}

function deleteConfig($variableName, $fileName)
{
    $$variableName = null;
    $content = file_get_contents($fileName);
    $pattern = '/(\n\s*)?\$' . preg_quote($variableName, '/') . '\s*=\s*.*;\n?/';
    $newContent = preg_replace($pattern, '', $content);
    if ($newContent !== $content) {
        file_put_contents($fileName, $newContent);
        return true;
    } else {
        return false;
    }
}

function renderProjects($projects,$admin = false)
{
    global $ps;
    $html = '';

    foreach ($projects as $key => $project) {
        $html .= '<a href="' . (
            $admin ? './admin.php?id=' . htmlspecialchars($key)
            : ($ps ? './' . htmlspecialchars($key)
                   : './project.php?id=' . htmlspecialchars($key))
        ) . '" class="hostLink btn btn-white btn-shadow btn-md project-link mb-0">';
        $html .= '<img class="project-image" src="' . htmlspecialchars($project->logoUrl ?? $defaultIconUrl) . '" alt="">';
        $html .= '<div class="project-details">';
        $html .= '<h3 class="project-title">' . htmlspecialchars($project->showName) . '</h3>';
        $html .= '<p class="project-description"><span class="tag tag-white" style="margin: 0px 5px 0px 0px;">'. htmlspecialchars($project->version ?? '?') .'</span>' . htmlspecialchars($project->describe) . '</p>';
        $html .= '</div>';
        $html .= '</a>';
    }

    return $html;
}

function deldir($dir){ // 来自CSDN
    // 判断给定文件名是否是一个目录
    if(is_dir($dir)){
        //获取目录数组，如果用fopen 然后在fread什么的麻烦
        $files =scandir($dir);
        //获取到单个的文件 但是这里的话路径不一定是正确的
        foreach ($files as $file){
            //把. 和.. 去掉
            if($file == '.' || $file == '..'){
                continue;
            }else{
            	//此时就得获取到正确的路径下的文件或者目录
                $path = $dir.'/'.$file;
                //此时判断一下文件的类型是文件还是目录，是文件直接干掉
                if(is_dir($path)){
                    //如果是目录的话，直接递归
                    deldir($path);
                }else{
                    //如果是文件的话直接删除
                    unlink($path);
                }
            }
        }
        //如果都清除干净了，直接删除空目录
        rmdir($dir);
    }
}
