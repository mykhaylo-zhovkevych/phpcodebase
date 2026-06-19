<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Include And Require</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 40px;
        }

        a {
            border: 1px solid #333;
            color: #333;
            display: inline-block;
            margin: 4px;
            padding: 8px 12px;
            text-decoration: none;
        }

        .box {
            border: 1px solid #ccc;
            margin-top: 20px;
            padding: 16px;
        }

        .step {
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .info {
            color: blue;
        }
    </style>
</head>
<body>
    <h1>PHP Include And Require</h1>

    <p>
        Both commands add another PHP file into this page.
        The difference is what happens when the file is missing.
    </p>

    <p>
        <!--? starts the query string, test is the query parameter, include is the query parameter value-->
        <a href="index.php?test=include">Test include</a>
        <a href="index.php?test=require">Test require_once</a>
        <a href="index.php?test=once">Test include_once</a>
    </p>

    <div class="box">
        <?php
        $test = $_GET["test"];

        if ($test === "include") {
            echo "<h2>Test: include</h2>";
            echo "<p class=\"step\">Step 1: PHP starts.</p>";

            include "missingChildd.php";

            echo "<p class=\"step success\">Step 2: PHP still continues, even if the file is missing.</p>";
            echo "<p class=\"info\">include gives a warning, but the page keeps running.</p>";
        }

        if ($test === "require") {
            echo "<h2>Test: require</h2>";
            echo "<p class=\"step\">Step 1: PHP starts.</p>";

            require "missingChildd.php";

            echo "<p class=\"step\">Step 2: I will not see this text.</p>";
        }

        if ($test === "once") {
            echo "<h2>Test: include_once</h2>";
            echo "<p class=\"step\">Step 1: PHP starts.</p>";

            include_once "child.php";
            include_once "child.php";

            echo "<p class=\"step success\">Step 2: child.php was shown only one time.</p>";
            echo "<p class=\"info\">include_once does not include the same file twice.</p>";
        }
        ?>
    </div>
</body>
</html>
