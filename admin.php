<?php
require(__DIR__ . '/assets/functions.php');
session_start();
if (isset($_GET['password'])) {
    if (!empty($securityEntrance) && isset($securityEntrance) && $_GET['SE'] !== $securityEntrance) {
        echo '安全入口错误';
        exit;
    }
    if (checkPassword($adminPassword)) {
        addConfig('adminPassword', password_hash($_GET['password'], PASSWORD_DEFAULT), __DIR__ . '/assets/config.php', $configPath);
    }
    $_SESSION['password'] = $_GET['password'];
    $queryString = $_SERVER['QUERY_STRING'];
    parse_str($queryString, $params);
    if (isset($params['password'])) {
        unset($params['password']);
    }
    $newQueryString = '?' . http_build_query($params);
    header('location: ' . $webPath . '/admin.php' . $newQueryString);
} elseif (isset($_GET['id'])) {
    if ($_GET['id'] == "logOut") {
        session_destroy();
        header('location: ' . $webPath . '/admin.php');
    }
} else {
    $_GET['id'] = "home";
}


// POST请求处理
if ($_SERVER['REQUEST_METHOD'] == "POST" && password_verify($_SESSION['password'], $adminPassword)) {
    if ($_POST['action'] == "editSet") {
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && $key !== "adminPassword") {
                addConfig($key, $value, $configPath);
            }
            if ($key == "adminPassword" && !empty($value) && isset($value)) {
                addConfig('adminPassword', password_hash($value, PASSWORD_DEFAULT), $configPath);
                header("location: $webPath/admin.php?id=set");
            }
        }
        header("location: $webPath/admin.php?id=set");
    } elseif ($_POST['action'] == "addProject") {
        // 从POST请求中获取数据
        $id = $_POST['id'];
        $name = $_POST['name'];
        $describe = $_POST['describe'];
        $detail = $_POST['detail'];

        // 防止重复添加
        if (isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }

        // 处理图标
        if ($_POST['icon'] == "default") {
            $iconUrl = '.host/assets/img/appIcon/default.ico';
        } else {
            if (strstr($_POST['icon'], ",")) {
                $img = explode(',', $_POST['icon']);
                $img = $img[1];
            }
            $icon = base64_decode($img);
            $iconFilename = str_replace(" ", "+", $id) . ".ico";
            $iconUrl = '.host/assets/img/appIcon/' . $iconFilename;
            file_put_contents(__DIR__ . '/assets/img/appIcon/' . $iconFilename, $icon);
        }

        // 处理截图
        $screenshots = json_decode($_POST['screenshots'], true);
        $screenshotsArray = [];
        mkdir(__DIR__ . '/assets/img/screenshots/' . $id);
        foreach ($screenshots as $screenshot) {
            $screenshotFilename = str_replace(" ", "+", basename($screenshot['name']));
            $screenshotUrl = '.host/assets/img/screenshots/' . $id . '/' . $screenshotFilename;
            if (strstr($screenshot['data'], ",")) {
                $img = explode(',', $screenshot['data']);
                $img = $img[1];
            }
            file_put_contents(__DIR__ . '/assets/img/screenshots/' . $id . '/' . $screenshotFilename, base64_decode($img));
            $screenshotsArray[$screenshotFilename] = $screenshotUrl;
        }

        // 创建项目数据
        $targetProjectData = [
            'type' => 'common',
            'showName' => $name,
            'version' => '未发布',
            'describe' => $describe,
            'detail' => $detail,
            'logoUrl' => $iconUrl,
            'support' => true,
            'focusUpdate' => true,
            'update' => [],
            'screenshots' => $screenshotsArray,
            'download' => ""
        ];

        // 更新项目数组
        $projectsArray[$id] = $targetProjectData;

        // 保存项目数据
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        header("Location: " . $webPath . "/admin.php");
        exit;
    } elseif ($_POST['action'] == "deleteProject") {
        $id = $_POST['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        unset($projectsArray[$id]);
        deldir(__DIR__ . '/assets/img/screenshots/' . $id);
        if ($id != 'default') {
            unlink(__DIR__ . '/assets/img/appIcon/' . $id . '.ico');
        }
        mkdir(__DIR__ . '/assets/img/screenshots/' . $id);
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php");
        exit;
    } elseif ($_POST['action'] == "editProjectSet") {
        $id = $_GET['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $projectInfo = (array)$projectsArray[$id];
        $projectInfo['support'] = $_POST['support'] == 'on' ? true : false;
        $projectInfo['focusUpdate'] = $_POST['focusUpdate'] == 'on' ? true : false;
        $projectInfo['type'] = $_POST['webProject'] == 'on' ? 'web' : 'common';
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "editProjectDescribe") {
        $id = $_GET['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $projectInfo = (array)$projectsArray[$id];
        $projectInfo['detail'] = $_POST['detail'] ?? "暂无描述";
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "addScreenShots") {
        $id = $_GET['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $screenshotFilename = str_replace(" ", "+", basename($_POST['name']));
        $screenshotUrl = '.host/assets/img/screenshots/' . $id . '/' . $screenshotFilename;
        if (strstr($_POST['data'], ",")) {
            $img = explode(',', $_POST['data']);
            $img = $img[1];
        }
        mkdir(__DIR__ . '/assets/img/screenshots/' . $id);
        file_put_contents(__DIR__ . '/assets/img/screenshots/' . $id . '/' . $screenshotFilename, base64_decode($img));
        $projectInfo = (array)$projectsArray[$id];
        $screenshotInfo = (array)$projectInfo['screenshots'];
        $screenshotInfo[$screenshotFilename] = $screenshotUrl;
        $projectInfo['screenshots'] = $screenshotInfo;
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "deleteScreenShots") {
        $name = $_POST['name'];
        $id = $_GET['id'];
        // if (!file_exists(__DIR__.'/assets/img/screenshots/'.$id.'/'.str_replace(" ","+",urldecode($name)))) {
        //     header("Location: " . $webPath . "/admin.php");
        //     exit;
        // }
        unlink(__DIR__ . '/assets/img/screenshots/' . $id . '/' . str_replace(" ", "+", urldecode($name)));
        $projectInfo = (array)$projectsArray[$id];
        $screenshotInfo = (array)$projectInfo['screenshots'];
        unset($screenshotInfo[str_replace(" ", "+", urldecode($name))]);
        $projectInfo['screenshots'] = $screenshotInfo;
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "editProjectVer") {
        $id = $_GET['id'];
        $ver = $_POST['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $verFile = $_POST['verFile'];
        if (!empty($verFile)) {
            if (strstr($verFile, ",")) {
                $file = explode(',', $verFile);
                $file = $file[1];
                mkdir(__DIR__ . '/assets/file/' . $id);
                mkdir(__DIR__ . '/assets/file/' . $id . '/' . $ver);
                file_put_contents(__DIR__ . '/assets/file/' . $id . '/' . $ver . '/' . $_POST['verFileName'], base64_decode($file));
                $downloadUrl = ".host/assets/file/" . $id . "/" . $ver . "/" . $_POST['verFileName'];
            }
        } else {
            $downloadUrl = $_POST['url'];
        }
        $projectInfo = (array)$projectsArray[$id];
        $updateInfo = (array)$projectInfo['update'];
        $updateInfo[$ver] = array(
            'changeLog' => $_POST['detail'],
            'downloadUrl' => $downloadUrl
        );
        $projectInfo['update'] = $updateInfo;
        if ($_POST['latest'] == "on") {
            $projectInfo['version'] = $ver;
        }
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "deleteProjectVer") {
        $id = $_GET['id'];
        $ver = $_POST['ver'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        deldir(__DIR__ . '/assets/file/' . $id . '/' . $ver);
        $projectInfo = (array)$projectsArray[$id];
        $updateInfo = (array)$projectInfo['update'];
        unset($updateInfo[$ver]);
        $projectInfo['update'] = $updateInfo;
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "editCommonInfo2") {
        $id = $_GET['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $projectInfo = (array)$projectsArray[$id];
        $updateInfo = (array)$projectInfo['update'];
        $projectInfo['version'] = $_POST['version'];
        if (!empty($_POST['icon'])) {
            if (strstr($_POST['icon'], ",")) {
                $img = explode(',', $_POST['icon']);
                $img = $img[1];
            }
            $icon = base64_decode($img);
            $iconFilename = str_replace(" ", "+", $id) . ".ico";
            $iconUrl = '.host/assets/img/appIcon/' . $iconFilename;
            file_put_contents(__DIR__ . '/assets/img/appIcon/' . $iconFilename, $icon);
            $projectInfo['logoUrl'] = $iconUrl;
        }
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header("Location: " . $webPath . "/admin.php?id=" . $_GET['id']);
        exit;
    } elseif ($_POST['action'] == "editCommonInfo") {
        $id = $_GET['id'];
        if (!isset($projectsArray[$id])) {
            header("Location: " . $webPath . "/admin.php");
            exit;
        }
        $projectInfo = (array)$projectsArray[$id];
        $updateInfo = (array)$projectInfo['update'];
        if ($_POST['targetInfo'] == "projectName") {
            $projectInfo['showName'] = $_POST['info'];
        } elseif ($_POST['targetInfo'] == "projectDescribe") {
            $projectInfo['describe'] = $_POST['info'];
        }
        $projectsArray[$id] = $projectInfo;
        file_put_contents($projectPath, json_encode($projectsArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

if (!password_verify($_SESSION['password'], $adminPassword)) : ?>
    <?php
    if (!empty($securityEntrance) && isset($securityEntrance) && $_GET['SE'] !== $securityEntrance) {
        echo '安全入口错误';
        exit;
    }
    ?>

    <head>
        <meta charset="UTF-8">
        <title>身份验证 | <?php echo $webName ?? "瑞思项目管理" ?></title>
        <link href="<?php echo $webPath ?>/assets/style.css" rel="stylesheet">
        <script>
            var defaultNavItems = [];
            var defaultNavRightItems = [];
            var defaultFooterLinks = [];
            var defaultCopyright = '<?php echo $copyRight ?? "版权所有 © 2024 腾瑞思智" ?>';
        </script>
        <script src="https://assets.3r60.top/v3/package.js"></script>
    </head>
    <script>
        $(document).ready(() => {
            setTimeout(() => {
                createMessage(`
        <?php
        if ($_SESSION['password'] == null || $_SESSION['password'] == '' || !isset($_SESSION['password'])) {
            echo '请验证身份后继续';
        } else {
            echo '输入的密码不正确';
        }
        ?>
        `, "danger");
                createDialog('type', 'primary', '验证身份', '<?php echo (checkPassword($adminPassword)) ? '密码为空，请设置新密码' : '请输入的管理密码' ?>', (input) => {
                    window.location.href = "<?php echo $webPath ?>/admin.php?password=" + input + "&SE=<?php echo $_GET['SE'] ?>"
                }, () => {
                    window.location.reload()
                }, true, '输入管理密码');
            }, 100)
        })
    </script>
<?php
    exit;
endif; ?>
<!DOCTYPE html>
<html lang="zh-Hans">

<head>
    <meta charset="UTF-8">
    <title><?php echo $webName ?? "腾瑞思智项目管理" ?></title>
    <link href="<?php echo $webPath ?>/assets/style.css" rel="stylesheet">
    <script>
        var defaultNavItems = [];
        var defaultNavRightItems = [];
        var defaultFooterLinks = [];
        var defaultCopyright = '<?php echo $copyRight ?? "版权所有 © 2024 腾瑞思智" ?>';
    </script>
    <script src="https://assets.3r60.top/v3/package.js"></script>
</head>

<body>
    <topbar data-showexpendbutton="false"></topbar>
    <main>
        <?php if ($_GET['id'] == "home") : ?>
            <h1>管理中心</h1>
            在下方选择一个项目以开始
            <span class="flex" style="flex-flow:row wrap">
                <a href="<?php echo $webPath ?>/admin.php?id=set" class="mb-0 hostLink btn btn-white btn-shadow btn-md project-link">
                    <img class="project-image" src="<?php echo $webPath . "/assets/img/appIcon/set.ico" ?>" alt="">
                    <div class="project-details">
                        <h3 class="project-title">系统设置</h3>
                        <p class="project-description">更改网站基本信息</p>
                    </div>
                </a>
                <a href="<?php echo $webPath ?>/admin.php?id=logOut" class="mb-0 hostLink btn btn-white btn-shadow btn-md project-link">
                    <img class="project-image" src="<?php echo $webPath . "/assets/img/appIcon/logOut.ico" ?>" alt="">
                    <div class="project-details">
                        <h3 class="project-title">退出登录</h3>
                        <p class="project-description">销毁当前会话信息</p>
                    </div>
                </a>
                <a href="<?php echo $webPath ?>/admin.php?id=addProject" class="mb-0 hostLink btn btn-white btn-shadow btn-md project-link">
                    <img class="project-image" src="<?php echo $webPath . "/assets/img/appIcon/addIcon.ico" ?>" alt="">
                    <div class="project-details">
                        <h3 class="project-title">新建项目</h3>
                        <p class="project-description">创建一个新的项目</p>
                    </div>
                </a>
                <?php echo renderProjects($projects, true) ?>
            </span>
            <script>
                $('span>a').on('contextmenu', function(event) {
                    event.preventDefault();
                    var id = this.href.split('?id=')[1];
                    var name = $(this).find('h3.project-title').text();
                    if (id == 'set' || id == 'logOut' || id == "addProject") {
                        createMessage('不支持删除此项目', 'danger');
                        return;
                    }
                    createDialog('confirm', 'danger', '删除“' + name + '”', '确认删除此项目吗？项目图标、项目截图与项目文件将一并删除，删除后不可恢复', function() {
                        fetchAndReplaceContent(
                            window.location.href,
                            'main',
                            'main',
                            null, {
                                'action': 'deleteProject',
                                'id': id
                            }
                        );
                    });
                });
                var webTitle = "管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>";
                var defaultTitle = `管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>`;
            </script>
        <?php elseif ($_GET['id'] == "set"): ?>
            <span class="text-center">
                <br>
                <h2 class="text-left m-0A" style="max-width: 650px;"><i class="bi bi-gear"></i>&nbsp;站点设置</h2>
                <form method="POST" id="configForm" action="" class="text-left m-0A" style="max-width: 650px;">
                    <input type="hidden" name="action" value="editSet">
                    <label for="webName"><br>网站名称:</label><br>
                    <input type="text" id="webName" name="webName" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入网站名称" value="<?php echo $webName ?>">
                    <br>
                    <label for="copyRight">版权信息:</label><br>
                    <input type="text" id="copyRight" name="copyRight" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入版权信息" value="<?php echo $copyRight ?>" required>
                    <br>
                    <label for="adminPassword">验证密码:</label><br>
                    <input type="password" id="adminPassword" name="adminPassword" class="textEditor textEditor-success textEditor-maxWidth" placeholder="留空则不更改密码">
                    <br>
                    <label for="securityEntrance">安全入口:</label><br>
                    <input type="text" id="securityEntrance" name="securityEntrance" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入安全入口" value="<?php echo $securityEntrance ?>">
                    <button type="button" class="btn btn-shadow btn-circle" onclick="createDialog('alert','success','安全入口','若填写此项，进入admin.php时需附带?SE=安全密码的 GET参数')">?</button>
                    <br>
                    更改站点图标仅需替换favicon.ico文件即可<br>
                    伪静态已改为自识别
                    <br><br>
                    <button type="submit" class="btn btn-shadow btn-success btn-md">保存设置</button>
                </form>
                <script>
                    $('#configForm').on('submit', function(event) {
                        event.preventDefault();
                        const formData = formInputsToKeyPairs($(this));
                        console.log(formData);
                        fetchAndReplaceContent(
                            window.location.href,
                            'main',
                            'main',
                            null,
                            formData
                        );
                    });
                    var webTitle = "站点设置 - 管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>";
                    var defaultTitle = `<a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a>站点设置`;
                </script>
            </span>
        <?php elseif ($_GET['id'] == "addProject"): ?>
            <span class="text-center">
                <br>
                <h2 class="text-left m-0A" style="max-width: 650px;"><i class="bi bi-plus-circle"></i>&nbsp;新建项目</h2>
                <form method="POST" id="newProjectForm" action="" class="text-left m-0A" style="max-width: 650px;">
                    <input type="hidden" name="action" value="addProject">
                    <label for="id"><br>项目标识:</label><br>
                    <input type="text" id="id" name="id" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入项目标识码" required="">
                    <br>
                    <label for="name">显示名称:</label><br>
                    <input type="text" id="name" name="name" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入项目名称" required="">
                    <br>
                    <label for="describe">简要描述:</label><br>
                    <input type="text" id="describe" name="describe" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入项目名称" required="">
                    <br>
                    <label for="detail">详细描述:</label><br>
                    <textarea id="detail" name="detail" class="textEditor textEditor-success textEditor-maxWidth" placeholder="暂无描述"></textarea>
                    <br>
                    <label for="icon">项目图标:</label><br>
                    <input type="file" id="iconSelector" class="textEditor textEditor-success textEditor-maxWidth" accept="image/*">
                    <br>
                    <label for="screenshots">项目截图:</label><br>
                    <input type="file" id="screenshotsSelector" class="textEditor textEditor-success textEditor-maxWidth" multiple accept="image/*">
                    <div id="selectedFiles" class="text-left m-0A" style="max-width: 650px; margin-top: 10px;"></div>
                    <input type="hidden" id="screenshots" name="screenshots" value="[]">
                    <input type="hidden" id="icon" name="icon" value="default">
                    <br><br>
                    <button type="submit" class="btn btn-shadow btn-success btn-md">确认创建</button>
                </form>
                <script>
                    function handleFileSelect(event) {
                        var input = event.target;
                        var selectedFilesDiv = document.getElementById('selectedFiles');
                        var screenshotsInfoInput = document.getElementById('screenshots');
                        var screenshotsInfo = JSON.parse(screenshotsInfoInput.value);

                        if (input.files && input.files.length > 0) {
                            Array.from(input.files).forEach(function(file) {
                                if (isImageFile(file)) {
                                    var reader = new FileReader();

                                    reader.onload = function(e) {
                                        var fileInfo = {
                                            name: file.name,
                                            type: file.type,
                                            size: file.size,
                                            data: e.target.result
                                        };
                                        screenshotsInfo.push(fileInfo);
                                        screenshotsInfoInput.value = JSON.stringify(screenshotsInfo);

                                        var fileNameSpan = document.createElement('span');
                                        fileNameSpan.textContent = file.name + ' ';
                                        fileNameSpan.style.color = '#337ab7';
                                        selectedFilesDiv.appendChild(fileNameSpan);
                                        selectedFilesDiv.appendChild(document.createTextNode('\u00A0\u2022\u00A0'));
                                    };

                                    reader.readAsDataURL(file);
                                } else {
                                    alert('请选择图片文件。');
                                }
                            });
                        }
                    }

                    function handleIconSelect(event) {
                        var input = event.target;
                        var iconInput = document.getElementById('icon');

                        if (input.files && input.files.length > 0) {
                            if (isImageFile(input.files[0])) {
                                var reader = new FileReader();

                                reader.onload = function(e) {
                                    iconInput.value = e.target.result;
                                };

                                reader.readAsDataURL(input.files[0]);
                            } else {
                                alert('请选择图片文件。');
                            }
                        }
                    }

                    function isImageFile(file) {
                        return file.type.startsWith('image/');
                    }

                    document.getElementById('screenshotsSelector').addEventListener('change', handleFileSelect);
                    document.getElementById('iconSelector').addEventListener('change', handleIconSelect);

                    $('#newProjectForm').on('submit', function(event) {
                        event.preventDefault();
                        const formData = formInputsToKeyPairs($(this));
                        console.log(formData);
                        history.pushState('', '', '<?php echo $webPath ?>/admin.php');
                        fetchAndReplaceContent(
                            window.location.href,
                            'main',
                            'main',
                            null,
                            formData
                        );
                    });
                    var webTitle = "新建项目 - 管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>";
                    var defaultTitle = `<a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a>新建项目`;
                </script>
            </span>
        <?php elseif (isset($project)): ?>
            <div class="blur-img" style="top:15px;height:200px;background-image: url('<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico' ?>');"></div>
            <div class="flex p-10 basicInfo" style="padding-bottom: 0;">
                <img id="projectLogo" class="infoEdit" height="80px" src="<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico'; ?>" alt="项目 Logo">
                <span class="ml-25" style="flex:1">
                    <h1 id="projectName" class="infoEdit mb-10 mt-0"><?php echo $project->showName ?? "项目不存在"; ?></h1>
                    <p class="mt-0">
                        <span id="projectVer" class="tag tag-white mr-5 infoEdit"><?php echo $project->version ?? '?'; ?></span>
                        <span id="projectDescribe" class="mt-0 infoEdit"><?php echo $project->describe ?? '暂无描述'; ?></span>
                    </p>
                </span>
                <script>
                    $('.infoEdit').on('contextmenu', function(event) {
                        event.preventDefault();
                        var targetInfo = this.id;
                        switch (targetInfo) {
                            case 'projectName':
                                var displayName = "项目名称";
                                var formType = "text";
                                var defaultValue = `<?php echo $project->showName ?>`;
                                break;
                            case 'projectDescribe':
                                var displayName = "项目描述";
                                var formType = "text";
                                var defaultValue = `<?php echo $project->describe ?>`;
                                break;
                        }
                        if (targetInfo !== 'projectVer' && targetInfo !== 'projectLogo') {
                            createDialog('type', 'primary', '编辑项目基本信息', `编辑${displayName}`, (input) => {
                                fetchAndReplaceContent(
                                    window.location.href,
                                    'main',
                                    'main',
                                    null, {
                                        action: 'editCommonInfo',
                                        targetInfo: `${targetInfo}`,
                                        info: input
                                    }
                                )
                            }, null, true, `${defaultValue}`);
                        } else {
                            history.pushState('', '', '<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>&page=editInfo');
                            fetchAndReplaceContent(
                                '<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>&page=editInfo',
                                'main',
                                'main',
                                null,
                            )
                        }
                    })
                </script>
            </div>
            <span style="color:#898989;margin-left: 16px;"><i class="bi bi-info-circle"></i>&nbsp;右键上方图标/文字修改相应信息</span>
            <span class="details mt-20">
                <span style="flex:1;min-width: calc(100% - 340px);">
                    <?php if (empty($_GET['page'])) {
                        $_GET['page'] = "set";
                    } ?>
                    <?php if ($_GET['page'] == "set") : ?>
                        <card class="mt-5" style="display: block;width:100%;height: auto;box-sizing:border-box;">
                            <h3 class="mt-0">详细描述</h3>
                            <form method="POST" id="editProjectDescribe" action="">
                                <input type="hidden" name="action" id="action" value="editProjectDescribe" />
                                <textarea id="detail" name="detail" class="textEditor textEditor-success mt-10" style="height:100px" placeholder="更新日志"><?php echo str_replace("<br>", "\n", $project->detail) ?></textarea>
                                <button type="submit" class="btn btn-shadow btn-success btn-md mt-10">更新信息</button>
                            </form>
                            <script>
                                $('#editProjectDescribe').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    // history.pushState('', '', window.location.href);
                                    fetchAndReplaceContent(
                                        window.location.href,
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                });
                            </script>
                        </card>
                        <card class="mt-10" style="display: block;width:100%;box-sizing:border-box;">
                            <h3 class="mt-0">项目截图</h3>
                            <span style="width:100%;overflow-x:auto;display:flex">
                                <?php foreach ($project->screenshots as $key => $img) : ?>
                                    <img style="border-radius:3px" class="mr-10 screenshots" height="150px" src="<?php echo $img ?? ''; ?>" alt="<?php echo $key ?? "无描述" ?>">
                                <?php endforeach; ?>
                            </span>
                            <input type="file" class="textEditor textEditor-success mt-10" name="uploadscreenshots" id="uploadscreenshots" />
                            <script>
                                $('#uploadscreenshots').on('change', function(event) {
                                    var input = event.target;
                                    var iconInput = document.getElementById('icon');

                                    if (input.files && input.files.length > 0) {
                                        var reader = new FileReader();

                                        reader.onload = function(e) {
                                            fetchAndReplaceContent(
                                                window.location.href,
                                                'main',
                                                'main',
                                                null, {
                                                    'action': 'addScreenShots',
                                                    'name': input.files[0].name,
                                                    'data': e.target.result
                                                }
                                            );
                                        };

                                        reader.readAsDataURL(input.files[0]);
                                    }
                                });
                                $('.screenshots').on('contextmenu', function(event) {
                                    event.preventDefault();
                                    var lastOf = this.src.lastIndexOf('/')
                                    var name = this.src.substr(lastOf + 1)
                                    createDialog('confirm', 'danger', '删除“' + name + '”', '确认删除此屏幕截图吗？删除后不可恢复', function() {
                                        fetchAndReplaceContent(
                                            window.location.href,
                                            'main',
                                            'main',
                                            null, {
                                                'action': 'deleteScreenShots',
                                                'name': name
                                            }
                                        );
                                    });
                                });
                            </script>
                        </card>
                        <card class="mt-10" style="display: block;width:100%;height: auto;box-sizing:border-box;">
                            <h3 class="mt-0">项目设定</h3>
                            <form method="POST" id="editProjectSet" action="">
                                <input type="hidden" name="action" id="action" value="editProjectSet" />
                                <div class="checkbox-container mt-10 mb-10"><input type="checkbox" name="support" id="support" <?php echo ($project->support == true) ? 'checked' : '' ?>><label for="support">软件支持状态</label></div>
                                <div class="checkbox-container mb-10"><input type="checkbox" name="focusUpdate" id="focusUpdate" <?php echo ($project->focusUpdate == true) ? 'checked' : '' ?>><label for="focusUpdate">强制更新启用[额外]</label></div>
                                <div class="checkbox-container mb-10"><input type="checkbox" name="webProject" id="webProject" <?php echo ($project->type == 'web') ? 'checked' : '' ?>><label for="webProject">网页项目模式[修改下载文本]</label></div>
                                <button type="submit" class="btn btn-shadow btn-success btn-md mt-10">保存设置</button>
                            </form>
                            <script>
                                $('#editProjectSet').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    // history.pushState('', '',> window.location.href);
                                    fetchAndReplaceContent(
                                        window.location.href,
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                });
                            </script>
                        </card>
                    <?php elseif ($_GET['page'] == "editVer"): ?>
                        <card class="mt-5" style="display: block;width:100%;height: auto;box-sizing:border-box;">
                            <h3 class="mt-0"><a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a><?php echo ($_GET['ver'] == "new") ? "创建新版本" : '编辑版本 ' . $_GET['ver'] ?></h3>
                            <form method="POST" id="editProjectVer" action="">
                                <input type="hidden" name="action" id="action" value="editProjectVer" />
                                <input type="<?php echo !empty($_GET['ver']) && $_GET['ver'] !== "new" ? 'hidden' : 'text' ?>" class="textEditor textEditor-success mt-10" name="id" value="<?php echo $_GET['ver'] !== "new" ? $_GET['ver'] : '' ?>" placeholder="版本标识" required="" />
                                <textarea id="detail" name="detail" class="textEditor textEditor-success mt-<?php echo !empty($_GET['ver']) && $_GET['ver'] !== "new" ? '10' : '5' ?>" style="height:200px" placeholder="更新日志"><?php echo $project->update->{$_GET['ver']}->changeLog ?></textarea>
                                版本文件：<br>
                                <input type="file" class="textEditor textEditor-success mt-5" id="verFileSelector" />
                                <input type="hidden" name="verFile" id="verFile" value="" />
                                <input type="hidden" name="verFileName" id="verFileName" value="" />
                                <input type="text" class="textEditor textEditor-success" name="url" id="" value="<?php echo $project->update->{$_GET['ver']}->downloadUrl ?>" placeholder="未选择文件使用此Url" />
                                <span style="color:#898989;"><i class="bi bi-info-circle"></i>&nbsp;优先使用上传的文件，若未上传则使用手动输入的链接</span>
                                <div class="checkbox-container mt-10 mb-10"><input type="checkbox" name="latest" id="latest" <?php echo ($_GET['ver'] == $project->version) ? 'checked' : '' ?>><label for="latest">设为当前版本</label></div>
                                <button type="submit" class="btn btn-shadow btn-success btn-md mt-10">保存更改</button>
                            </form>
                            <script>
                                $('#verFileSelector').on('change', function(event) {
                                    var input = event.target;
                                    if (input.files && input.files.length > 0) {
                                        var reader = new FileReader();
                                        reader.onload = function(e) {
                                            document.getElementById('verFile').value = e.target.result;
                                            document.getElementById('verFileName').value = input.files[0].name;
                                        };
                                        reader.readAsDataURL(input.files[0]);
                                    }
                                });
                                $('#editProjectVer').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    history.pushState('', '', '<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>');
                                    fetchAndReplaceContent(
                                        '<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>',
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                });
                            </script>
                        </card>
                    <?php elseif ($_GET['page'] == "editInfo"): ?>
                        <card class="mt-5" style="display: block;width:100%;height: auto;box-sizing:border-box;">
                            <h3 class="mt-0"><a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a>编辑基本信息</h3>
                            <form method="POST" id="editCommonInfo" action="">
                                <input type="hidden" name="action" id="action" value="editCommonInfo2" />
                                项目版本：<br>
                                <select class="textEditor textEditor-success" name="version" id="version">
                                    <?php foreach ((array)$project->update as $version => $update) : ?>
                                        <option value="<?php echo $version ?>" <?php echo ($version == $project->version) ? 'selected' : '' ?>><?php echo $version ?></option>
                                    <? endforeach ?>
                                </select>
                                项目图标：<br>
                                <input type="file" class="textEditor textEditor-success mt-5" id="iconSelector" />
                                <input type="hidden" name="icon" id="iconInfo" value="" />
                                <span style="color:#898989;"><i class="bi bi-info-circle"></i>&nbsp;不选择图标即不更改</span><br>
                                <button type="submit" class="btn btn-shadow btn-success btn-md mt-10">保存更改</button>
                            </form>
                            <script>
                                $('#iconSelector').on('change', function(event) {
                                    var input = event.target;
                                    if (input.files && input.files.length > 0) {
                                        var reader = new FileReader();
                                        reader.onload = function(e) {
                                            document.getElementById('iconInfo').value = e.target.result;
                                        };
                                        reader.readAsDataURL(input.files[0]);
                                    }
                                });
                                $('#editCommonInfo').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    history.pushState('', '', '<?php echo $webPath ?>/admin.php?id=<?php echo $_GET['id'] ?>');
                                    fetchAndReplaceContent(
                                        window.location.href,
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                });

                                function isImageFile(file) {
                                    var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/ico'];
                                    return allowedTypes.includes(file.type);
                                }
                            </script>
                        </card>
                    <?php endif; ?>
                </span>
                <span class="ml-15" style="min-width: 300px;">
                    <h3 class="mt-10 mb-0 ml-5">更新记录</h3>
                    <a href="<?php echo $webPath . '/admin.php?id=' . $_GET['id'] . '&page=editVer&ver=new' ?>" class="hostLink btn btn-lg btn-white items-center" style="display: flex;width: 90%; margin: 10px;padding:0;">
                        <div style="padding-left: 10px;padding-right: 10px;text-align: left;">
                            <h3 style="font-size: 1.1em;margin-bottom: 0;">发布新版本</h3>
                            <p style="color: #666;margin-top: 5px;font-size: 0.8em;">创建一个新的版本记录</p>
                        </div>
                    </a>
                    <?php if (isset($project->version)) : ?>
                        <?php
                        $updateInfo = (array)$project->update;
                        krsort($updateInfo);
                        $first = true;
                        $count = 0;
                        foreach ($updateInfo as $key => $update) :
                            $showFullLog = false;
                            $showShortLog = false;

                            if ($first) {
                                $showFullLog = true;
                                $first = false;
                            } elseif ($count < 5) {
                                $showShortLog = true;
                            }

                            $log = $showFullLog ? $update->changeLog : ($showShortLog ? substr($update->changeLog, 0, 100) . '...' : '');
                            $count++;
                        ?>
                            <a href="<?php echo $webPath . '/admin.php?id=' . $_GET['id'] . '&page=editVer&ver=' . $key ?>" class="hostLink btn btn-lg btn-white items-center versions" style="display: flex;width: 90%; margin: 10px;padding:0;">
                                <div style="padding-left: 10px;padding-right: 10px;text-align: left;">
                                    <h3 style="font-size: 1.1em;margin-bottom: 0;">Ver <?php echo $key; ?><?php echo $key == $project->version ? '（Latest）' : '' ?></h3>
                                    <p style="color: #666;margin-top: 5px;font-size: 0.8em;"><?php echo $log; ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </span>
            </span>
            <script>
                $('.versions').on('contextmenu', function(event) {
                    event.preventDefault();
                    var ver = this.href.split('&ver=')[1];
                    createDialog('confirm', 'danger', '删除“' + ver + '”', '确认删除此版本吗？相应的版本文件将会被删除', function() {
                        fetchAndReplaceContent(
                            window.location.href,
                            'main',
                            'main',
                            null, {
                                'action': 'deleteProjectVer',
                                'ver': ver
                            }
                        );
                    });
                });
                var webTitle = "<?php echo $project->showName ?> - 管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>";
                var defaultTitle = `<a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a><?php echo $project->showName ?>`;
            </script>
        <?php else: ?>
            <h1>页面不存在</h1>
            请检查地址后重试
            <script>
                var webTitle = "页面不存在 - 管理中心 | <?php echo $webName ?? "瑞思项目管理" ?>";
                var defaultTitle = `<a class="p-0 hostLink" href="<?php echo $webPath ?>/admin.php"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a>页面不存在`;
            </script>
        <?php endif; ?>
        <script>
            var searchValue = '<?php echo $_GET['s'] ?>'
            reloadScript("<?php echo $webPath ?>/assets/js/basic.js");
            reloadScript("<?php echo $webPath ?>/assets/js/admin.js");
            $(document).ready(function() {
                setTimeout(function() {
                    $('#pageTitle').html(defaultTitle);
                }, 500);
                setTimeout('<?php
                            $vers = file_get_contents("https://app.3r60.top/assets/projects.json");
                            $projects = json_decode($vers);
                            $ver = $projects->Ris_ProjectManager->version;
                            $update = str_replace(PHP_EOL, '<br>', $projects->Ris_ProjectManager->update->{$ver}->changeLog);
                            if ($ver !== $localVersion && $_GET['mode'] !== 'editPost') {
                                echo 'createDialog("confirm","success","版本更新","' . $localVersion . '=>' . $ver . '<br>更新日志:' . $update . '<br>点击确定前往更新",()=>{location.href="' . $webPath . '/install.php"})';
                            }
                            ?>', 500)
            });

            function formInputsToKeyPairs(formElement) {
                const formData = new FormData(formElement[0]);
                const dataObject = {};

                for (let [key, value] of formData.entries()) {
                    dataObject[key] = value;
                }

                return dataObject;
            }
        </script>
    </main>
    <footer></footer>
</body>

</html>