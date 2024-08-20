<?php
require('./assets/functions.php');
?>
<!DOCTYPE html>
<html lang="zh-Hans">

<head>
    <meta charset="UTF-8">
    <title><?php echo $webName ?? "瑞思项目管理" ?></title>
    <link href="./assets/style.css" rel="stylesheet">
    <script src="https://assets.3r60.top/v3/package.js"></script>
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
</head>

<body>
    <topbar data-showexpendbutton="false"></topbar>
    <main>
        <h2 id="noticeText">全部项目</h2>
        <span class="flex" style="flex-flow:row wrap">
            <?php echo renderProjects($projects) ?>
        </span>
        <script>
            var webTitle = "<?php echo $webName ?? "瑞思项目管理" ?>";
            var defaultTitle = "<?php echo $webName ?? "瑞思项目管理" ?>";
            var defaultNavItems = []
            var defaultNavRightItems = []
            var defaultFooterLinks = [];
            var defaultCopyright = '<?php echo $copyRight ?? "版权所有 © 2024 腾瑞思智" ?>';
            var searchValue = '<?php echo $_GET['s'] ?>'
            reloadScript("<?php echo $webPath ?>/assets/js/basic.js");
            reloadScript("<?php echo $webPath ?>/assets/js/index.js");
            $(document).ready(function () {
                setTimeout(function () {
                    $('#pageTitle').html(`<a class="p-0 hostLink" href="./"><?php echo $webName ?? "瑞思项目管理" ?></a>`);
                }, 500);
            });
        </script>
    </main>
    <footer></footer>
</body>

</html>