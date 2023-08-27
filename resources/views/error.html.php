<?php

declare(strict_types=1);

use Sunrise\Http\Router\Exception\HttpExceptionInterface;

/** @var HttpExceptionInterface $this */

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sprintf('%d | %s', $this->getStatusCode(), $this->getReasonPhrase()) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            background-color: #FAFAFA;
            color: #212121;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #212121;
                color: #FAFAFA;
            }
        }
        .content-container {
            margin: 20vh 10vw 0 10vw;
        }
        .violations-container:has(p),
        .navigation-container {
            margin-top: 2.5em;
        }
        .violation-property-path-label {
            display: inline-block;
            margin-right: 0.25em;
            padding: 0.25em 0.5em;
            background-color: #C62828;
            border-radius: 0.25em;
            color: #FAFAFA;
            font-size: .8em;
            font-weight: 300;
            line-height: 1;
        }
        .navigation-link {
            display: inline-block;
            margin-right: 0.5em;
            padding: 0.75em 1.5em;
            background-color: #1565C0;
            border: 1px solid #1565C0;
            box-shadow: #2196F3 0 1px 0 0 inset;
            border-radius: 0.25em;
            color: #FAFAFA;
            font-size: .9em;
            font-weight: 600;
            text-decoration: none;
            line-height: 1;
        }
        .navigation-link:hover {
            background-color: #1976D2;
        }
        .navigation-link:active {
            background-color: #1E88E5;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h1><?= $this->getReasonPhrase() ?></h1>
        <p><?= htmlentities($this->getMessage()) ?></p>
        <div class="violations-container">
            <?php foreach ($this->getViolations() as $violation): ?>
                <p><span class="violation-property-path-label"><?= $violation->source ?></span> <?= htmlentities($violation->message) ?></p>
            <?php endforeach; ?>
        </div>
        <div class="navigation-container">
            <p>
                <a class="navigation-link" href="/">Home</a>
                <a class="navigation-link" href="javascript:window.history.go(-1)">Back</a>
            </p>
        </div>
    </div>
</body>
</html>
