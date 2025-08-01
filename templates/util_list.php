<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Liste des utils</h1>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>Nom</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if($utils == null || count($utils) == 0) {
                    ?>
                    <tr>aucun util trouve</tr>
                    <?php
                } else {
                    foreach($utils as $util) {
                        ?>
                        <tr>
                            <td><?= $util->getId() ?></td>
                            <td><?= $util->getName() ?></td>
                        </tr>
                        <?php
                    }
                }
            ?>
        </tbody>
    </table>
</body>
</html>