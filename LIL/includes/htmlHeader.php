<?php
declare(strict_types=1);

require_once "include.php";

// Expose CSRF token to JS
$csrf   = $_SESSION['csrf_token'];
$csrfJS = "<script>window.CSRF_TOKEN = " . json_encode($csrf, JSON_UNESCAPED_SLASHES) . ";</script>";

// Minor cache signal (fine)
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

echo <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Language in Lyrics</title>

  <link rel="apple-touch-icon" sizes="76x76" href="/includes/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/includes/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/includes/img/favicon-16x16.png">
  <link rel="manifest" href="/includes/img/site.webmanifest">
  <link rel="mask-icon" href="/includes/img/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" type="text/css" href="css/style.css">
  <link href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" integrity="sha512-ARJR74swou2y0Q2V9k0GbzQ/5vJ2RBSoCWokg4zkfM29Fb3vZEQyv0iWBMW/yvKgyHSR/7D64pFMmU8nYmbRkg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
  <script src="https://cdn.ckeditor.com/4.14.1/basic/ckeditor.js"></script>
  <script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js" integrity="sha512-yDlE7vpGDP7o2eftkCiPZ+yuUyEcaBwoJoIhdXv71KZWugFqEphIS3PU60lEkFaz8RxaVsMpSvQxMBaKVwA5xg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  {$csrfJS}
</head>
<body>
  <nav class="navbar navbar-expand-md">
    <div class="container">
      <a class="navbar-brand" href="index.php"><span>Cainnt anns na Ceathramhan</span><br>Language in Lyrics</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ms-md-auto my-2 my-md-0">
          <li class="nav-item">
            <a class="nav-link" title="Browse" href="index.php?m=records&a=list"><span class='gaelic'>Sealladh</span><br>View the Index</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" title="Search" href="index.php?m=records&a=search"><span class='gaelic'>Rannsaich</span><br>Search the Index</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" title="User Guide" href="index.php?m=faq"><span class='gaelic'>Cairt-iùil</span><br>User Guide</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid page-wrapper">
    <div class="row">
      <div id="mainBody" class="col-12 p-0">
HTML;