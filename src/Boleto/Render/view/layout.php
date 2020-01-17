<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boletos</title>
    <style type="text/css">
        <?php echo $css ?>
    </style>
</head>
<body>

<div style="width: 666px">
    <?php echo $boleto?>
</div>

<?php if(isset($imprimir_carregamento) && $imprimir_carregamento === true):?>
    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>
<?php endif?>
</body>
</html>
