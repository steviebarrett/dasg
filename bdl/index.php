<?php

require_once '../includes/include.php';

//require Composer
//require_once '../vendor/autoload.php';
//$phpWord = new \PhpOffice\PhpWord\PhpWord();

$dbh = DB::getDatabaseHandle();

//get all texts
$sth = $dbh->prepare("SELECT * FROM bdl_text ORDER BY CAST(id AS unsigned) ASC");
$sth->execute();
$texts = $sth->fetchAll();
$textHtml = '<option>-- select a text --</option>';

foreach ($texts as $text) {
    $textHtml .= '<option value="' . $text["id"] . '">' . $text["id"] . '</option>';
}

//get all classical firstlines
$sth = $dbh->prepare("SELECT id, firstline_c, firstline_untagged FROM bdl_text WHERE firstline_c IS NOT NULL AND firstline_c != '' ORDER BY firstline_untagged  ASC");
$sth->execute();
$firstlines = $sth->fetchAll();
$firstLineHtml = '<option>-- select a first line --</option>';

foreach ($firstlines as $firstline) {
    $firstLineHtml .= '<option value="' . $firstline["id"] . '">' . $firstline["firstline_c"] . '</option>';
}


echo <<<HTML
<html>

    <head>
        <title>BDL</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <!--link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css"-->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


   
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

        <!--script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script-->        
        
        <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
        <script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>
        
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


        <style>
            
            .toggle {
                font-size: small;
            }
            
            .hide {
                display: none;
            }
            
            .lineref {
                display: block;
                margin-top: 1rem;
            }
            
            .linecontent, #result, #textResult {
                list-style-type: none;
                margin: 0;
                padding: 0;
                padding-top: 1rem;
                padding-bottom: 1rem;
             
            }
            
            .linecontent ul > li {
                margin:0;
                padding:0;
            }
            
            .lineoptions {
                list-style-type: none;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: space-around;
            }
            
            .resultLine {
                margin-left: 2em;
            }
            
            #mirador {
                height: 1000px;
            }
            
            /* Override the default bs-modal backdrop behavior */
            .modal-backdrop {
                /* Set background color to transparent */
                background-color: transparent !important;
            }
            
        </style>
    </head>
    <body>

        <div class="container-fluid">
        
            <div class="row mt-3">       <!-- the line search -->
                <div class="col-3 mb-3 border">
                    <input type="text" class="form-control" name="q" id="q" placeholder="search"/>
                </div>
                <div class="col-1">
                    <button class="btn-primary btn" id="search">search</button>
                </div>
                <div class="col-8">
                    <ul class="lineoptions">      
                        <li class="form-check">
                            <label class="form-check-label" for="ignoreTags">
                                ignore tags
                            </label>
                            <input class="form-check-input" type="checkbox" checked id="ignoreTags">
                        </li>   
                        <li class="form-check">
                            <label class="form-check-label" for="as">
                                accent sensitive
                            </label>
                            <input class="form-check-input" type="checkbox" checked id="as">
                        </li>   
                        <li class="form-check">
                            <label class="form-check-label" for="sw">
                                single word
                            </label>
                            <input class="form-check-input" type="checkbox" checked id="sw">
                        </li>   
                        <li class="form-check">
                            <label class="form-check-label" for="Dsearch">
                                D
                            </label>
                            <input class="form-check-input searchcheck" data-f="diplomatic" type="checkbox" checked id="Dsearch">
                        </li>            
                        <li class="form-check">
                            <label class="form-check-label" for="Vsearch">
                                V
                            </label>
                            <input class="form-check-input searchcheck" data-f="vernacular" type="checkbox" checked id="Vsearch">
                        </li>
                        <li class="form-check">
                            <label class="form-check-label" for="Csearch">
                                C
                            </label>
                            <input class="form-check-input searchcheck" data-f="classical" type="checkbox" checked id="Csearch">
                        </li>
                        <li class="form-check">
                            <label class="form-check-label" for="Tsearch">
                                T
                            </label>
                            <input class="form-check-input searchcheck" data-f="translation" type="checkbox" checked id="Tsearch">
                        </li>
                        <li class="form-check">
                            <label class="form-check-label" for="Nsearch">
                                N
                            </label>
                            <input class="form-check-input searchcheck" data-f="notes"  type="checkbox" checked id="Nsearch">
                        </li>
                        <li class="form-check">
                            <label class="form-check-label" for="TCsearch">
                                TC
                            </label>
                            <input class="form-check-input searchcheck" data-f="team_comments"  type="checkbox" checked id="TCsearch">
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <button class="btn btn-success hide" id="toggleResults">show/hide results</button>
                    <h4 id="resultCountHeader" class="mt-3 hide">There are <span id="resultCount"></span> results</h4>
                    <ul id="result">
                    </ul>
                </div>
            </div>
            
            <div class="row">       
            
                <div class="col-4 mb-5 p-4 border">     <!-- text and mirador container -->
                    
                    <div id="miradorBody" class="hide">
                        
                      <div id="mirador" style="position:relative;width:100%;">
                      </div>                        
                        
                    </div>
                    
                    <div id="text">
                
                        <div class="row form-group mb-3">
                            <label for="text" class="col-4 col-form-label">Text:</label>
                            <div id="textid-container" class="col-8">
                                <select id="textid" class="form-control">
                                    {$textHtml}
                                </select>
                            </div>
                        </div>
                        
                        <div class="row form-group mb-3">
                            <label for="firstLine" class="col-4 col-form-label">First Line [C]:</label>
                            <div class="col-8">
                                <select id="firstLine" class="form-control">
                                    {$firstLineHtml}
                                </select>
                            </div>
                        </div>
                        
                        <div class="row form-group mb-3">       <!-- the text metadata search -->
                            <div class="col-10 mb-3">
                                <input type="text" class="form-control" name="qt" id="qt" placeholder="search text metadata"/>
                            </div>
                            <div class="col-2">
                                <button class="btn-primary btn" id="searchText">search</button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col mb-3">
                                <button class="btn btn-success hide" id="toggleTextResults">show/hide results</button>
                                <h4 id="textResultCountHeader" class="mt-3 hide">There are <span id="textResultCount"></span> results</h4>
                                <ul id="textResult">
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row form-group mb-3">
                            <div class="col-3">
                               <a href="index.php" class="btn btn-primary">reset</a> 
                            </div>
                        </div>
    
                        <div id="metaContainer" class="row hide">   <!-- text metadata container -->
                            
                            <p></p>Metadata: <a href="#" data-target="meta" class="toggle">show/hide</a></p>
                        
                            <div id="meta" class="hide">
                        
                                <div class="row form-group textmeta mb-3">
                                    <label for="author" class="col-2 col-form-label"">Author:</label>
                                    <div class="col">
                                        <input type="text" id="author" class="form-control text_editor">
                                    </div>
                                </div>
                                
                                <div class="row form-group textmeta mb-3">
                                    <label for="pagerange" class="col-2 col-form-label"">Page Range:</label>
                                    <div class="col">
                                        <input type="text" id="pagerange" class="form-control text_editor">
                                    </div>
                                </div>
                                
                                <div class="row form-group textmeta mb-3">
                                    <label for="edition">Edition:</label>
                                       <div>
                                            <div id="edition" class="text_editor"></div> 
                                       </div>
                                </div>
                                
                                <div class="row form-group textmeta mb-3">
                                       <label for="fld">First Line D:</label>
                                       <div>
                                            <div id="fld" class="text_editor"></div> 
                                       </div>
                                </div>
                                <div class="row form-group textmeta mb-3">
                                       <label for="flv"">First Line V:</label>
                                       <div>
                                            <div id="flv" class="text_editor"></div> 
                                       </div>
                                </div>
                                <div class="row form-group textmeta mb-3">
                                       <label for="flc">First Line C:</label>
                                       <div>
                                            <div id="flc" class="text_editor"></div> 
                                       </div>
                                </div>
                                <div class="row form-group textmeta mb-3">
                                       <label for="text_translation">Translation:</label>
                                       <div>
                                            <div id="text_translation" class="text_editor"></div> 
                                       </div>
                                </div>
                                <div class="row form-group textmeta mb-3">
                                       <label for="text_notes">Notes:</label>
                                       <div>
                                            <div id="text_notes" class="text_editor"></div> 
                                       </div>
                                </div>
                                                       
                                <div class="row textmeta">
                                    <div class="col-4">
                                        <button id="saveText" data-id="" class="btn btn-lg btn-success">save</button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div id="savedTextAlert" class="alert alert-danger alert-dismissible fade text-center" role="alert">
                                        <strong>saved</strong>
                                    </div>
                                </div>
                                
                            </div>
                             
                        </div>
                    
                    </div>
                
                </div>
                
                <div class="col-4 mb-5 p-4 border">     <!-- line edit middle section -->
                    
                    <div class="row">
                    
                        <div id="pageContainer" class="col-12 mb-3">     <!-- page container -->
                        
                            <div class="row form-group">
                                <label for="pageid" class="col-2 col-form-label">Page:</label>
                                <div class="col-5">
                                    <select id="pageid" class="form-control">
                                    </select>
                                </div>
                                <div class="col-1">
                                     <!-- Button trigger modal -->
                                    <button type="button" id="manuscript-icon" class="btn btn-primary hide" data-bs-toggle="modal" data-bs-target="#miradorModal">
                                        <i class="fa-solid fa-scroll"></i>
                                    </button>
                                </div>
                                <!--div class="col-2">
                                    <input type="text" id="isos" class="form-control text_editor">
                                </div-->
                                <div class="col-1">
                                    <button type="button" id="closeMirador" class="mb-3 btn btn-secondary hide">close</button>
                                </div>
                            </div>
                   
                        </div>
       
                    </div>
                    
                    <div class="row">
                                   
                        <div id="lineContainer" class="col-6 mb-3">     <!-- line container -->
                        
                            <div class="row form-group">
                                <label for="lineid" class="col-2 col-form-label">Line:</label>
                                <div class="col">
                                    <select id="lineid" class="form-control">
                                    </select>
                                </div>
                            </div>
                   
                        </div>
                    
                    </div>
                    
                    <div class="row">   <!-- the line data form -->
                                   
                        <div id="lineForm" data-id="" class="col">
           
                            <div class="row form-group textmeta mb-3">
                               <label for="diplomatic">D (Diplomatic Transcription):</label>
                               <div>
                                    <div id="diplomatic" class="editor"></div> 
                               </div>
                            </div>
                            <div class="row form-group textmeta mb-3">
                                   <label for="vernacular"">V (Vernacular Transliteration): <a href="#" data-target="vernacular" class="toggleToolbar">toolbar +/-</a></label>
                                   <div id="V">
                                        <div id="vernacular" class="editor"></div> 
                                   </div>
                            </div>
                            <div class="row form-group textmeta mb-3">
                                   <label for="classical">C (Classical Transliteration):  <a href="#" data-target="classical" class="toggleToolbar">toolbar +/-</a></label>
                                   <div id="C">
                                        <div id="classical" class="editor"></div> 
                                   </div>
                            </div>
                            <div class="row form-group textmeta mb-3">
                                   <label for="translation">T (Translation):  <a href="#" data-target="translation" class="toggleToolbar">toolbar +/-</a></label>
                                   <div id="T">
                                        <div id="translation" class="editor"></div> 
                                   </div>
                            </div>
                            <div class="row form-group textmeta mb-3">
                                   <label for="notes">N (Notes and Comments):</label>
                                   <div>
                                        <div id="notes" class="editor"></div> 
                                   </div>
                            </div>
                            <div class="row form-group textmeta mb-3 bg-danger">
                                   <label for="team_comments" class="text-white">TC (Team Comments):</label>
                                   <div>
                                        <div id="team_comments" class="editor"></div> 
                                   </div>
                            </div>
                            
                            <div class="row">
                                <div id="savedLineAlert" class="alert alert-danger alert-dismissible fade text-center" role="alert">
                                    <strong>saved</strong>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-4">
                                    <button id="saveLine" data-id="" class="btn btn-lg btn-success">save</button>
                                </div>
                            </div> 
                                 
                        </div>           
                    
                    </div>
                
                </div> 
                
                <div id="pageContentContainer" class="col-4 mb-3 hide">     <!-- page content container -->
                           
                    <h4 class="pageHeading"></h4>
                    
                    <div class="mb-3">
                        <ul class="lineoptions">
                        
                            <li class="form-check">
                                <label class="form-check-label" for="Dshow">
                                    D
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="Dshow" data-field="dip">
                            </li>
                            <li class="form-check">
                                <label class="form-check-label" for="Vshow">
                                    V
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="Vshow" data-field="ver">
                            </li>
                            <li class="form-check">
                                <label class="form-check-label" for="Cshow">
                                    C
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="Cshow" data-field="cla">
                            </li>
                            <li class="form-check">
                                <label class="form-check-label" for="Tshow">
                                    T
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="Tshow" data-field="tra">
                            </li>
                            <li class="form-check">
                                <label class="form-check-label" for="Nshow">
                                    N
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="Nshow" data-field="not">
                            </li>
                            <li class="form-check">
                                <label class="form-check-label" for="TCshow">
                                    TC
                                </label>
                                <input class="form-check-input fieldcheck" type="checkbox" checked id="TCshow" data-field="tea">
                            </li>
                            <!--li class="form-check">
                                <label class="form-check-label" for="allchecks">
                                    toggle all
                                </label>
                                <input class="form-check-input" type="checkbox" id="allchecks">
                            </li-->
                        </ul>
                    </div>
                    
                    <div class="row textmeta">
                        <div class="col-4">
                            <label for="pageview" class="form-check-label">Page View</label>
                            <input type="radio" name="viewtype" class="viewtype form-check-input" id="pageview" value="page" checked>
                        </div>
                        <div class="col-4">
                            <label for="lineview" class="form-check-label">Line View</label>
                            <input type="radio" name="viewtype" class="viewtype form-check-input" value="line" id="lineview">
                        </div>
                    </div>
                            
                    <div id="pageContent"></div>

                </div>
            
            </div>
            
  
            <!-- Mirador Modal -->
            <!--div class="modal fade" id="miradorModal" tabindex="-1" aria-labelledby="miradorModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  <div class="modal-body">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div-->
        
        </div>
HTML;


echo <<<HTML
<script src="https://unpkg.com/mirador@latest/dist/mirador.min.js"></script>
<script>
    
$(function () {
    
 /*       $('#miradorModal').draggable({
            handle: ".modal-content" // Specify the handle for dragging
        });

        // Listen for the mirador modal's shown.bs.modal event
        //$('#miradorModal').on('shown.bs.modal', function () {
 */           
        //save an updated ISOS number
        $('#isos').on('blur', function () {
            let isos = $(this).val();    
            let pageid = $('#pageid').val();
            let data = {
                action: 'saveIsos',
                id: pageid,
                isos: isos          
            }
            $.post('ajax.php', data, function (response) {
                console.log(response);
            });
            $('#pageid option:selected').attr('data-isos-num', isos);
        });
        
        $('#manuscript-icon').on('click', function () {
          let isosNum = $('#pageid option:selected').attr('data-isos-num');
      
          $('#text').hide();
          $('#miradorBody').removeClass('hide');
          $('#closeMirador').removeClass('hide');
          
          // Simulate asynchronous content loading 
          setTimeout(function () {
              loadMirador(isosNum);
          }, 50); 
        });
        
        $('#closeMirador').on('click', function () {
            $('#miradorBody').addClass('hide');
            $('#closeMirador').addClass('hide');
            $('#text').show();
        });
        
        /* 
            Display fields controls
         */
         let displayDefaults = 
         {
            'dip' : true,
            'ver' : true,
            'cla' : true,
            'tra' : true,
            'not' : true,
            'tea' : true
         };
         //get the display fields from the cookie or use the defaults
         let displayFields = ($.cookie('displayFields')) ? JSON.parse($.cookie('displayFields')) : displayDefaults;
         $.cookie('displayFields', JSON.stringify(displayFields));
         $.each(displayFields, function (field, checked) {
            let elem = $('input[data-field="' + field + '"]');
            let c = checked ? 'checked': '';        //check the display boxes or not as appropriate
            elem.prop('checked', c);
         });

        $('.fieldcheck').on('click', function () {
            let field = $(this).data('field');
            $('.'+field).toggle();
            displayFields[field] = $(this).prop('checked');
            // store in the cookie
            $.cookie('displayFields', JSON.stringify(displayFields));
        });
      
        /*
            //
         */
        
        /* if viewtype cookie is set then select appropriate radio button */
        let type = null;
        if (type = $.cookie('viewtype')) {
            $('#'+type+'view').prop('checked', true);    
        }
        
        /* switch page and line views and assign to cookie */
        $('.viewtype').on('click', function () {
           let type = $('input[name="viewtype"]:checked').val();
           $.cookie('viewtype', type);
           if (type == 'line') {
               $('.linecontent').hide();
               let lineid = $('#lineid').val();
               $('#line_'+lineid).show();
           } else {
               $('.linecontent').show();
           }     
        });
        
        $('#search').on('click', function () {
            performSearch();
        });
        
        $('#q').keypress(function(e) {
            if (e.which == 13) { // 13 is the key code for "Enter" key
                performSearch();
            }
        });
         
        $('#searchText').on('click', function () {
            performTextSearch();
        });
        
        $('#qt').keypress(function(e) {
            if (e.which == 13) { // 13 is the key code for "Enter" key
                performTextSearch();
            }
        });
           
        $('#q, #qt').on('click', function () {
            $(this).val('');
        })
        
        $('#toggleResults').on('click', function () {
            $('#result').toggle();
        })
        
        $('#toggleTextResults').on('click', function () {
            $('#textResult').toggle();
        })
        
        //auto load all the pages
        var pageListHtml = '';
        $.getJSON('ajax.php?action=getPageList', function (data) {
           $.each(data, function (i, page) {
              pageListHtml += '<option value="'+page.id+'" data-isos-num="'+page.isos+'" data-page-num="'+page.number+'" data-text-id='+page.text_id+'>'+page.number+' . '+page.text_id;
              pageListHtml += '';
              pageListHtml += '';
              pageListHtml += '</option>'; 
           });
       })
       .done(function (){
           $('#pageid').append(pageListHtml);
       //    $('#pageContainer').show();
       });
        
       //create the editors for text metadata
       let text_fields = ['fld', 'flv', 'flc', 'edition', 'text_translation', 'text_notes'];
       $.each(text_fields, function (i, field) {
           createEditor(field); 
       });
              
       //create the editors for line metadata
       let line_fields = ['diplomatic', 'vernacular', 'classical', 'translation', 'notes', 'team_comments'];
       $.each(line_fields, function (j, field) {
           createEditor(field);   
       });    
        
       clear("text");
         
       /*
        *   Save Text
        */
       $('#saveText').on('click', function () {
          let data = {
              action:   "saveText",
              id:       $(this).attr('data-id'),
              fld:      fld.getData(),
              flv:      flv.getData(),
              flc:      flc.getData(),
              trans:    text_translation.getData(),
              author:   $('#author').val(),
              pagerange:$('#pagerange').val(),
              edition:  $('#edition').val(),
              notes:    text_notes.getData()
          } 
          $.post('ajax.php', data, function(response) {
             if (response.error) {
                 console.log(response.error);
             } else {
                 showAlert('savedTextAlert');
             }
          });
       });
       
       /*
        *   Save Line
        */
       $('#saveLine').on('click', function () {
          let id = $(this).attr('data-id');
          let data = {
              action:       "saveLine",
              id:           id,
              diplomatic:   diplomatic.getData(),
              vernacular:   vernacular.getData(),
              classical:    classical.getData(),
              translation:  translation.getData(),
              author:       $('#author').val(),
              pagerange:    $('#pagerange').val(),
              edition:      $('#edition').val(),
              notes:        notes.getData(),
              team_comments:team_comments.getData()
          } 
          $.post('ajax.php', data, function(response) {
             if (response.error) {
                 console.log(response.error);
             } else {
                 //refresh page and line content
                 loadPageContent($('#pageid').val(), id);
                 loadLineEditors(id);
                 showAlert('savedLineAlert');
             }
          });
       });

       /* 
       * Load Text
        */
       $('#textid, #firstLine').on('change', function () {
           let textId = $(this).val();   
           loadText(textId);
       });
       
       $(document).on('click', '.textResultLink', function () {
           let textId = $(this).data('text-id'); 
           loadText(textId);
           $('#meta').show();
       });
       
       /*
       *    Load Page
        */
       $('#pageid').on('change', function () {
           //get the line list
           let pageId = $(this).val();
           let isos = $('#pageid option:selected').attr('data-isos-num');
           $('#isos').val(isos);
           loadPageContent(pageId);
       }); 
       
       /*
        * Load Line
        */
       $('#lineid').on('change', function () {
           //get the line data
           let lineId = $(this).val();
           loadLineEditors(lineId);
       }); 
       
       $(document).on('click', '.lineref' , function () {
           let lineId = $(this).attr('data-line-id');
           loadLineEditors(lineId);
       });
  
     
    /*
        Simple toggle
     */
     $('.toggle').on('click', function () {
         let target = $(this).attr('data-target'); 
         $('#'+target).toggle();
     });
          
    /*
        Toggle display of editor toolbars
     */
     $('.toggleToolbar').on('click', function () {
         let target = $(this).attr('data-target');
         $("#"+target).closest("div").next().find(".ck-sticky-panel").toggle();
        
       // $('#'+target).toggle();
     });
    /* 
        Generate Word files
     */
     $('#textToWord').on('click', function () {
         let id = $('#saveText').attr('data-id');
        window.open('word.php?source=text&id='+id, '_blank'); 
     });
     
   });



    /*
            /// Functions ///
     */
    function performSearch() 
    {
        let q = $('#q').val();
        let qr;
       
        try {            
            qr = new RegExp(q, 'gui'); 
            } catch (e) {
                if (e instanceof SyntaxError) { //test for a badly formed regex
                alert('Invalid regular expression! Please try a different format.');
                return false;
            } else {
                console.error('An unexpected error occurred:', e);
                return false;
            }
        }

        let html = '';
        let fieldQuery = '';
        let numResults = null;
        let ignoreTags = $('#ignoreTags').prop('checked');
        let as = $('#as').prop('checked');     //accent sensitive
        let sw = $('#sw').prop('checked');    //single word
        $('.searchcheck:checked').each(function () {
            let field = $(this).data('f');
            fieldQuery += '&f[]=' + field; 
        });            
        
       $.getJSON('ajax.php?action=search&q='+q+'&as='+as+'&sw='+sw+'&ignoreTags='+ignoreTags+fieldQuery, function (data) {       
           numResults = data.length;
           $.each(data, function (key, result) { 
               let pageid = result['page_id'];
               let lineid = result['id'];
               let ref = '['+result['pageNum']+'.'+result['text_id']+'.'+result['number']+']';
//                  html += '<li><a href="#" onclick="loadPageContent('+pageid+', '+lineid+');loadLineEditors('+lineid+');">'+ref + '</a> : <div class="resultLine">';

               html += '<li><a href="#" class="lineref" data-line-id="'+lineid+'" onclick="loadPageContent('+pageid+', '+lineid+');">'+ref + ':</a> <div class="resultLine">';

               html += 'D: ' + result['diplomatic'].replace(qr, (match) => `<mark>\${match}</mark>`)+'<br>';
               html += 'V: ' + result['vernacular'].replace(qr, (match) => `<mark>\${match}</mark>`)+'<br>';               
               html += 'C: ' + result['classical'].replace(qr, (match) => `<mark>\${match}</mark>`)+'<br>';
               html += 'T: ' + result['translation'].replace(qr, (match) => `<mark>\${match}</mark>`)+'<br>';
               html += '</div></li>';
           });            
       }) 
       .done(function () {
          $('#result').html(html); 
          $('#result').show();
          $('#resultCount').html(numResults);
          $('#toggleResults').show();
          $('#resultCountHeader').show();
       });
    }
        
    function performTextSearch() 
    {
        let qt = $('#qt').val();
        let html = '';
        let numResults = 0;
        
        $.getJSON('ajax.php?action=searchText&qt='+qt, function (data) {
           numResults = data.length;
           $.each(data, function (key, result) {
               html += '<li><a href="#" class="textResultLink" data-text-id="'+result.id+'">'+result.id+'</a>: '+result.author+' '+result.firstline_c+'</li>';
           });
        })
        .done(function () {
           $('#textResult').html(html); 
           $('#textResult').show();
           $('#textResultCount').html(numResults);
           $('#toggleTextResults').show();
           $('#textResultCountHeader').show(); 
        });
    }
        
    // Show the alerts
    function showAlert(id) 
    {
        $('#'+id).addClass('show');
        
        // Automatically fade the alert after 3 seconds (adjust as needed)
        setTimeout(function() {
            $('#'+id).removeClass('show');
        }, 3000);
    }
      
    /*
       * Reset (parts of) the form
     */
    function clear(level)
    {
        switch (level) {     
            case "text":
                $('.textmeta').hide();
                $('#pageid').html('<option>-- select a page --</option>');
                $('#pageContentContainer').hide();
                $('#manuscript-icon').hide();
            case "page":
                $('#lineid').html('<option>-- select a line --</option>');
        //        $('.linecontent').show();
            case "line":
                $('#lineForm, #lineContainer').hide();    
            case "result":
                $('#result').hide();  //hide the search results
            default:
                
        }
       
    }
    
    function loadText(textId) 
    {
        clear("text")
        //   let textId = $(this).val();
       $('#textid option[value="'+textId+'"]').prop("selected", true);
       /** default to -select a first line- to handle no first line entry for this text */
       $('#firstLine option[value="none"]').prop("selected", true);
       /**   --  **/
       $('#firstLine option[value="'+textId+'"]').prop("selected", true);
       $('#saveText').attr('data-id', textId);
       //$('#textToWord').show();
       
       //get the text metadata
       $.getJSON('ajax.php?action=getText&tid='+textId, function (text) {
          $('#author').val(text.author);
          $('#pagerange').val(text.pagerange);
          $('#edition').val(text.edition);
          fld.setData(text.firstline_d ?? '');
          flv.setData(text.firstline_v ?? '');
          flc.setData(text.firstline_c ?? '');
          text_translation.setData(text.firstline_t ?? '');
          text_notes.setData(text.notes ?? '');
          
           
       })
       .done(function (){
           $('.textmeta').show();
           $('#metaContainer').show();
       });
       
       //get the page list
       let pageListHtml = '';
       let firstPageId = null;
       $.getJSON('ajax.php?action=getPageList&tid='+textId, function (data) {
           firstPageId = data[0].id;
           $.each(data, function (i, page) {
              pageListHtml += '<option data-isos-num="'+page.isos+'" data-page-num="'+page.number+'" value="'+page.id+'">'+page.number +  '</option>'; 
           });
       })
       .done(function (){
           $('#pageid').append(pageListHtml);
           $('#pageContainer').show();
           loadPageContent(firstPageId);
       });
    }
    
    function loadLineEditors(lineId)
    {
        $("#lineid option[value='"+lineId+"']").attr("selected", "selected");
        $('#saveLine').attr('data-id', lineId);
        
        $.getJSON('ajax.php?action=getLine&lid='+lineId, function (line) {
          
          $('#lineForm').attr('data-id', lineId);
          diplomatic.setData(line.diplomatic ?? '');
          vernacular.setData(line.vernacular ?? '');
          classical.setData(line.classical ?? '');
          translation.setData(line.translation ?? '');
          notes.setData(line.notes ?? '');
          team_comments.setData(line.team_comments ?? '');

        })
        .done(function (){
           $('#lineForm').show();
           if ($.cookie('viewtype') == 'line') {  
               $('.linecontent').hide();
               $('#line_'+lineId).show();
           }
       });
    }
    
    function loadPageContent(pageId, lineId = null) 
    {
       clear("page");   
        
       $('#manuscript-icon').show();
       $('#pageid').val(pageId);
       let lineListHtml = '';
       let pageContent = '';
       let firstLineId = null;
       let hide = $.cookie('viewtype') == 'line' ? 'hide' : '';
       $.getJSON('ajax.php?action=getLineList&pid='+pageId, function (data) {
           firstLineId = data[0].id;
           $.each(data, function (i, line) {
              let hideLine = (line.id == lineId) ? '' : hide;
              let content = {
                  "dip" : line.diplomatic,
                  "ver" : line.vernacular ?? '',
                  "cla" : line.classical ?? '',
                  "tra" : line.translation ?? '',
                  "not" : line.notes ?? ''
              }
              let selected = '';
              // set the line dropdown if there is a line ID provided          
              if (lineId !== null) {     
                  if (lineId == line.id) {
                    selected = "selected";
                    }                       
              }
              //
              lineListHtml += '<option '+selected+' value="'+line.id+'">'+line.number+'</option>';
              let ref = '['+line.pageNum+'.'+line.text_id+'.'+line.number+']';
              pageContent += '<ul class="linecontent ' + hideLine + '" id="line_'+line.id+'">';   
              pageContent += '<li><a href="#" class="lineref" data-line-id="'+line.id+'">'+ ref + '</a></li>';
              let displayFields = JSON.parse($.cookie('displayFields'));
              $.each(displayFields, function (key, value) {
                  if (key == "tea") {return false;} //skip Team Comments for now
                  let hide = value == false ? 'hide' : '';
                  let txt = content[key].replace(/<br>/g, '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                  pageContent += '<li class="'+hide+' field '+key+'">' + key.charAt(0).toUpperCase() + ': ' + txt + '</li>';
              })      
              if (line.team_comments) {
                  let hide = displayFields["tea"] == false ? 'hide' : '';
                  pageContent += '<li class="'+hide+' field tea" id="team_comment_'+line.id+'" style="color:red;">' + line.team_comments + '</li>';
              }
              pageContent += '</ul>';
           });
       })
       .done(function (){
           //hide the toolbars for certain editor toolbars by default
           let hide_toolbars = ['vernacular', 'classical', 'translation'];
           $.each(hide_toolbars, function(i, field) {
              $("#"+field).closest("div").next().find(".ck-sticky-panel").hide(); 
           });
           $('#lineid').append(lineListHtml);
           $('#lineContainer').show(); $('.textmeta').show();
           $('#pageContent').html(pageContent);
           $('#pageContentContainer').show(); 
           if (!lineId) {
                loadLineEditors(firstLineId);  //default to loading first line
                lineId = firstLineId;
           } 
       });
    }
    

    
   function SpecialCharactersSimple( editor ) {
    editor.plugins.get( 'SpecialCharacters' ).addItems( 'Simple', [
        
        { title: 'D dot', character: 'Ḋ' },
        { title: 'M dot', character: 'Ṁ' },
        
        { title: 'A acute', character: 'Á' },    
        { title: 'E acute', character: 'É' },
        { title: 'I acute', character: 'Í' },
        { title: 'O acute', character: 'Ó' },
        { title: 'U acute', character: 'Ú' },
        
        { title: 'a acute', character: 'á' },
        { title: 'e acute', character: 'é' },
        { title: 'i acute', character: 'í' },
        { title: 'o acute', character: 'ó' },
        { title: 'u acute', character: 'ú' },
        
        { title: 'A grave', character: 'À' },    
        { title: 'E grave', character: 'È' },
        { title: 'I grave', character: 'Ì' },
        { title: 'O grave', character: 'Ò' },
        { title: 'U grave', character: 'Ù' },
        
        { title: 'a grave', character: 'à' },
        { title: 'e grave', character: 'è' },
        { title: 'i grave', character: 'ì' },
        { title: 'o grave', character: 'ò' },
        { title: 'u grave', character: 'ù' },
        
        { title: 'A macron', character: 'Ā' },    
        { title: 'E macron', character: 'Ē' },
        { title: 'I macron', character: 'Ī' },
        { title: 'O macron', character: 'Ō' },
        { title: 'U macron', character: 'Ū' },
        
        { title: 'a macron', character: 'ā' },
        { title: 'e macron', character: 'ē' },
        { title: 'i macron', character: 'ī' },
        { title: 'o macron', character: 'ō' },
        { title: 'u macron', character: 'ū' },
        
        { title: 'b dot', character: 'ḃ' },
        { title: 'd dot', character: 'ḋ' },    
        { title: 'f dot', character: 'ḟ' }, 
        { title: 'h dot', character: 'ḣ' }, 
        { title: 'ȝ dot', character: 'ʒ̇' },
        
        { title: 'm dot', character: 'ṁ' },    
        { title: 'n dot', character: 'ṅ' }, 
        { title: 'p dot', character: 'ṗ' },
        { title: 'w dot', character: 'ẇ' },
        
        { title: 'a dot', character: 'ȧ' },    
        { title: 'e dot', character: 'ė' },
        { title: 'o dot', character: 'ȯ' },
        { title: 'y dot', character: 'ẏ' },
        
        { title: 'c dot', character: 'ċ' },
        { title: 'g dot', character: 'ġ' },
        { title: 'k dot', character: 'k̇' },
        { title: 't dot', character: 'ṫ' },
        
        { title: 'Ʒ large', character: 'Ʒ' },
        { title: 'ʒ small', character: 'ʒ' },
        
        { title: 'a tilde', character: '\u00E3' }
    ] );
}

    /*
        Creates and stores an instance of CKEditor 
     */
    function createEditor(id) {
        // Visit https://ckeditor.com/docs/ckeditor5/latest/features/index.html to browse all the features.
       CKEDITOR.ClassicEditor.create(document.getElementById(id), {
           extraPlugins: [SpecialCharactersSimple],
            // https://ckeditor.com/docs/ckeditor5/latest/features/toolbar/toolbar.html#extended-toolbar-configuration-format
            toolbar: {
                items: [
                    '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', 'removeFormat', '|',   
                    'fontColor','|',     
                    'undo', 'redo',
                    'specialCharacters',  'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            // Changing the language of the interface requires loading the language file using the <script> tag.
            // language: 'es',
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true
                }
            },
    
            // Be careful with the setting below. It instructs CKEditor to accept ALL HTML markup.
            // https://ckeditor.com/docs/ckeditor5/latest/features/general-html-support.html#enabling-all-html-features
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: false,
                        classes: true,
                        styles: false
                    }
                ]
            },
            // Be careful with enabling previews
            // https://ckeditor.com/docs/ckeditor5/latest/features/html-embed.html#content-previews
            htmlEmbed: {
                showPreviews: true
            },
            
            // The "superbuild" contains more premium features that require additional configuration, disable them below.
            // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
            removePlugins: [
                // These two are commercial, but you can try them out without registering to a trial.
                 'ExportPdf',
                 'ExportWord',
                'AIAssistant',
                'CKBox',
                'CKFinder',
                'EasyImage',
                 'Base64UploadAdapter',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                // Careful, with the Mathtype plugin CKEditor will not load when loading this sample
                // from a local file system (file://) - load this site via HTTP server if you enable MathType.
                 'MathType',
                // The following features are part of the Productivity Pack and require additional license.
                'SlashCommand',
                'Template',
                'DocumentOutline',
                'FormatPainter',
                'TableOfContents',
                'PasteFromOfficeEnhanced',
                'CaseChange'
            ]
        })
        .then( editor => {
            
            window[id] = editor;    //save the instance to the window for reuse
        });
    }
    
    function loadMirador() 
    { 
        let page = $('#pageid option:selected').attr("data-isos-num");
        let mirador = Mirador.viewer({
            "id": "mirador",
            "manifests": {
            "https://www.isos.dias.ie/static/manifests/NLS_Adv_MS_72_1_37.json?manifest=https://www.isos.dias.ie/static/manifests/NLS_Adv_MS_72_1_37.json": {
            "provider": "ISOS"
            }
        },
        "workspace": {
          "showZoomControls": false,
          "type": 'mosaic'
        },
        "workspaceControlPanel": {
            "enabled": false
        },
        "window": {
          "sideBarOpenByDefault": false,
          "allowClose": false      
        },
        "windows": [
            {
                "loadedManifest": "https://www.isos.dias.ie/static/manifests/NLS_Adv_MS_72_1_37.json?manifest=https://www.isos.dias.ie/static/manifests/NLS_Adv_MS_72_1_37.json",
                "canvasIndex": page,
                "thumbnailNavigationPosition": 'far-bottom'
            }
        ]
        });
    }
  
  
</script>
HTML;

echo "\n\n</body></html>";