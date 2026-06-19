<main>
    <h2>Conversions</h2>
    <p>This text comes from conversions.php and derivatives</p>

    <div>
        <?php

        try {
          include "child.php";
        } catch (ParseError $error) {
            echo "Parse error: " . $error->getMessage();
        } catch (Throwable $error) {
            echo "Other error handling: " . $error->getMessage();
        }

        ?>
    </div>
    <br>
    <?php
        $var = 1.23456e-3;
        $var1 = 1.23456e+3;
        echo "result is - \"$var\" \"$var1\"<br>";

        echo nl2br((string) shell_exec("dir"));

        $str = '45.2wef';
        $number = $str - 12;
        echo $number . "<br>"; // 33.2

        $va2 = 0;
        if ($var) {
            echo 'interpreted as true';
        }
        else {
            echo 'interpreted as false';
        }

        echo "<h3>Explicit type conversion</h3>";
        $textValue = "PHP";
        $textAsArray = (array) $textValue;
        echo "First array item: " . $textAsArray[0] . "<br>";

        $languageArray = [
            "name" => "PHP",
            "type" => "language",
        ];
        $languageObject = (object) $languageArray;

        echo "<p>Array converted to object:</p>";
        echo "Name: " . $languageObject->name;
        echo "Type: " . $languageObject->type;
    ?>
</main>
