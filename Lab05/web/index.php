<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title> PHP project</title>
    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 40px;
            text-align: center;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 300px;
        }
        h1 { color: #0073e6; }
        p { font-size: 18px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>PHP сервер работает</h1>
        <p>Время сервера: <b><?php echo date("Y-m-d H:i:s"); ?></b></p>
    </div>
</body>
</html>