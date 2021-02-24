<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="main.css" rel="stylesheet" type="text/css"/>
    <title>Document</title>
</head>
<body>
<?php
echo '<table>';
echo '<thead class="table-head">';
echo '<tr>';
// header from keys of the first data row
foreach ($plugins[0] as $k => $v) {
    echo '<td>';
    echo $k;
    echo '</td>';
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($plugins as $data) {
    echo '<tr>';
    foreach ($data as $k => $datum) {
        echo '<td>';
        echo $datum;
        echo '</td>';
    }
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

?>
</body>
</html>