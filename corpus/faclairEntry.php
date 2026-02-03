<?php

require_once '../includes/include.php';

$pageTitle = "Faclair Entry Generator";

require_once '../includes/htmlHeader.php';

echo <<<HTML
     
        <dl>
            <dt><label for="headword">headword</label></dt>
            <dd><input type="text" name="headword" id="headword"></dd>
    
            <dt><label for="form">type</label></dt>
            <dd>form:<input type="radio" id="form" name="type" value="form"><br>
            sense:<input type="radio" id="sense" name="type" value="sense"></dd>
        
            <dt><label for="year">year</label></dt>
            <dd><input onfocus="this.value=''" type="text" name="year" id="year"></dd>
            
            <dt><label for="text">text ID</label></dt>
            <dd><input onfocus="this.value=''" type="text" name="text" id="text"></dd>
            
            <dt><label for="text">Mac-Talla Issue</label></dt>
            <dd><input onfocus="this.value=''" type="text" name="issue" id="issue"></dd>
            
            <dt><label for="page">page</label></dt>
            <dd><input onfocus="this.value=''" type="text" name="page" id="page"></dd>
            
            <dt><label for="citation">citation</label></dt>
            <dd><input size="80em" type="text" name="citation" id="citation"></dd>
            
            <dt><label for="mark">mark</label></dt>
            <dd><input onfocus="this.value=''" type="text" name="mark" id="mark"></dd>
        </dl>
        
        <button id="add">add citation</button>
    
    <button id="press">view output</button>

    <div id="test"></div>
    
    <script>
        $(function () {
            let html = '<h1>output</h1>';
            $('#add').on('click', function () {
               let type = $('#form').is(':checked') ? 'form' : 'sense';
               let data = {
                   action: 'addCitation',
                   type: type,
                   year: $('#year').val(),
                   issue: $('#issue').val(),
                   text: $('#text').val(),
                   page: $('#page').val(),
                   citation: $('#citation').val(),
                   mark: $('#mark').val()
               } 
               $.post('http://dasg.localhost/corpus/faclairAjax.php', data)
                .done(function (output) {
                   html += output;
               }, 'html');
            });
            
            $('#press').on('click', function () {
                $('#test').html(html);
            }); 
        });
    
    </script>
    
HTML;

require_once '../includes/htmlFooter.php';