<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Variable Types</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 40px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f2f2f2;
        }

    </style>
</head>
<body>
    <h1>PHP Variable Types</h1>

    <?php
        function includeIfExists($file)
        {
            if (file_exists($file)) {
                include $file;
            } else {
                echo "<p>The file $file does not exist.</p>";
            }
        }

        // Dynamic types language style
        $float_name = 11.1;
        $array_name = ["HTML", "CSS", "PHP"];
        $null_is_null = null;
        $object = new stdClass();
        $object->name = "Student";
        $resource = fopen(__FILE__, "r");
    ?>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Output</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Float</td>
                <td><?php echo $float_name; ?></td>
            </tr>
            <tr>
                <td>Array</td>
                <td><?php echo $array_name[0] . ", " . $array_name[1] . ", " . $array_name[2]; ?></td>
            </tr>
            <tr>
                <td>Null</td>
                <td><?php var_dump($null_is_null); ?></td>
            </tr>
            <tr>
                <td>Object</td>
                <td><?php echo $object->name; ?></td>
            </tr>
            <tr>
                <td>Resource</td>
                <td><?php echo get_resource_type($resource); ?></td>
            </tr>
        </tbody>
    </table>
                <?php
                fclose($resource);
//                echo "check";
                ?>

    <?php
    includeIfExists("conversions.php");
    ?>

</body>
</html>
