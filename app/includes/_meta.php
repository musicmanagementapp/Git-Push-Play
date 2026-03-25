    <?php
    // Defaults
    $defaultTitle = "GitPushPlay";
    $defaultDesc  = "Meta Description HERE";

    $title = isset($title) && trim($title) !== "" ? $title : $defaultTitle;
    $description = isset($description) && trim($description) !== "" ? $description : $defaultDesc;

    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    ?>



    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.ico">