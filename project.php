<?php
if ($_GET['id'] == "psTest") {
    echo "successful";
    exit;
}
@require('./assets/functions.php');
?>
<!DOCTYPE html>
<html lang="zh-Hans">

<head>
    <meta charset="UTF-8">
    <title><?php echo $webName ?? "瑞思项目管理" ?></title>
    <link href="./assets/style.css" rel="stylesheet">
    <script src="https://assets.3r60.top/v3/package.js"></script>
    <link rel="icon" href="<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico'; ?>" type="image/x-icon">
</head>

<body>
    <topbar data-showexpendbutton="false"></topbar>
    <main>
        <style type="text/css" media="all">
        </style>
        <div id="overlay">
            <img id="largeImage" src="<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico'; ?>" alt="Large Image">
        </div>
        <div class="blur-img" style="background-image: url('<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico' ?>');"></div>
        <div class="flex p-30 basicInfo">
            <img height="140px" src="<?php echo $project->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico'; ?>" alt="项目 Logo">
            <span class="ml-25" style="flex:1">
                <h1 class="mb-10 mt-0"><?php echo $project->showName ?? "项目不存在"; ?></h1>
                <p class="mt-0"><span class="tag tag-white mr-5"><?php echo $project->version ?? '?'; ?></span><?php echo $project->describe ?? '暂无描述'; ?></p>
                <a href="<?php echo (!isset($project->update->{$project->version}->downloadUrl) || empty($project->update->{$project->version}->downloadUrl)) ? 'JavaScript:createDialog(\'alert\',\'danger\',\'该软件暂无下载链接\',\'请咨询开发者以寻求帮助\')' : $project->update->{$project->version}->downloadUrl ?>" class="btn btn-success btn-shadow btn-lg mt-10"><?php echo ($project->type == "web" ? '进入项目' : '下载') ?></a>
                <?php if (isset($project->openUrl) && !empty($project->openUrl)) : ?>
                    <a href="<?php echo $project->openUrl ?>" style="padding: 5px 12px 5px;" class="btn btn-white btn-shadow btn-lg" style=""><i class="bi bi-github"></i></a>
                <?php endif; ?>
            </span>
        </div>
        <span class="details">
            <span style="flex:1;min-width: calc(100% - 340px);">
                <?php if (isset($project->screenshots) && !empty($project->screenshots)) : ?>
                    <card class="mt-5" style="display: block;width:100%;height: 210px;box-sizing:border-box;">
                        <h3 class="mt-0">项目截图</h3>
                        <span style="width:100%;overflow-x:auto;display:flex">

                            <?php foreach ($project->screenshots as $key => $img) : ?>
                                <img style="border-radius:3px" class="mr-10" height="150px" src="<?php echo $img ?? ''; ?>" alt="<?php echo $key ?? "无描述" ?>">
                            <?php endforeach; ?>
                        </span>
                    </card>
                <?php endif; ?>
                <card class="mt-10" style="display: block;width:100%;box-sizing:border-box;">
                    <h3 class="mt-0">项目描述</h3>
                    <tr>
                        <code markdown>
                            <?php echo $project->detail ?? "暂无描述" ?>
                        </code>
                </card>
                <card class="mt-10" style="display: block;width:100%;box-sizing:border-box;">
                    <h3 class="mt-0">更新日志</h3>
                    <tr>
                        <?php if (isset($project->version)) : ?>
                            <?php
                            $updateInfo = (array)$project->update;
                            krsort($updateInfo);
                            foreach ($updateInfo as $key => $update) :
                            ?>
                                <h4>Ver <?php echo $key; ?><?php echo $key == $project->version ? '（当前版本）' : '' ?></h4>
                                <span><?php echo $update->changeLog; ?></span>
                            <?php endforeach; ?>
                        <?php else : ?>
                            暂无更新记录
                        <?php endif; ?>
                </card>
            </span>
            <span class="ml-15" style="min-width: 300px;">
                <h3 class="mt-10 mb-0 ml-5">其他项目</h3>
                <?php
                $keys = array_keys($projectsArray);
                shuffle($keys);
                $selectedKeys = array_slice($keys, 0, 5);

                // 生成HTML
                echo '<span class="flex" style="flex-flow: row wrap;">';
                foreach ($selectedKeys as $key) {
                    $projectInfo = $projectsArray[$key]; // 使用方括号访问数组元素
                    echo '
        <a class="hostLink" href="' . ($ps ? './' : './project.php?id=') . $key . '" class="btn btn-md items-center" style="display: flex;width: 90%; margin: 10px;padding:0;border-radius:0px">
            <img style="width: auto;height:50px;padding:10px" src="' . htmlspecialchars($projectInfo->logoUrl ?? $webPath . '/assets/img/appIcon/default.ico') . '" alt="Project Icon">
            <div style="padding-left: 10px;padding-right: 10px;text-align: left;">
                <h3 style="font-size: 1.1em;margin-bottom: 0;">' . htmlspecialchars($projectInfo->showName) . '</h3>
                <p style="color: #666;margin-top: 5px;font-size: 0.8em;">' . htmlspecialchars($projectInfo->describe) . '</p>
            </div>
        </a>
    ';
                }
                echo '</span>';
                ?>
            </span>
        </span>
        <br><br>
        <script>
            var webTitle = "<?php echo $project->showName ?? "项目不存在" ?> | <?php echo $webName ?? "瑞思项目管理" ?>";
            var defaultTitle = `<a class="p-0 hostLink" href="./"><i class="bi bi-chevron-left mr-10" style="font-size: 20px;"></i></a><?php echo $project->showName ?? "项目不存在"; ?>`;
            var defaultNavItems = []
            var defaultNavRightItems = []
            var defaultFooterLinks = [];
            var defaultCopyright = '<?php echo $copyRight ?? "版权所有 © 2024 腾瑞思智" ?>';
            var searchValue = '<?php echo $_GET['s'] ?>'
            reloadScript("<?php echo $webPath ?>/assets/js/basic.js");
            reloadScript("<?php echo $webPath ?>/assets/js/project.js");
            reloadScript("https://assets.3r60.top/MDTool/marked.min.js");
            $(document).ready(function() {
                setTimeout(function() {
                    $('#pageTitle').html(defaultTitle);
                }, 500);
            });
        </script>
    </main>
    <footer></footer>

</body>

</html>