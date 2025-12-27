/*! emoji-picker-admin.js - lightweight ACF emoji picker front-end for admin */
(function($){
    'use strict';

    /**
     * Sistema de inicialización para ACF Emoji Picker
     * - Previene la duplicación de event handlers usando data attribute 'acf-emoji-initialized'
     * - Compatible con campos estáticos, repeaters, flexible content y campos clonados
     * - Carga los emojis bajo demanda (lazy loading) solo al abrir el picker
     */

    function fetchEmojiData(url){
        return fetch(url).then(function(res){
            if(!res.ok) throw new Error('Network response was not ok');
            return res.json();
        });
    }

    function normalizeEmoji(item){
        // The emoji.json package uses "char" property for the emoji character
        if(item.char) return item.char;
        // fallback for other formats
        if(item.emoji) return item.emoji;
        return '';
    }

    function buildGrid($wrap, emojis){
        var $grid = $wrap.find('.acfp-grid').empty();
        emojis.forEach(function(e){
            var $btn = $('<button type="button" class="acfp-emoji-btn" title="'+(e.name||'')+'">'+e.char+'</button>');
            $grid.append($btn);
        });
    }

    function addEvents($wrap){
        // Verificar si ya está inicializado para evitar duplicar eventos
        if($wrap.data('acf-emoji-initialized')){
            return;
        }
        
        // Marcar como inicializado
        $wrap.data('acf-emoji-initialized', true);
        
        $wrap.on('click', '.acf-emoji-open', function(e){
            e.preventDefault();
            var $button = $(this);
            var $grid = $button.siblings('.acf-emoji-grid');
            
            // Si el grid no está cargado, cargarlo primero
            if(!$grid.data('loaded')){
                $grid.show();
                $grid.find('.acfp-grid').html('<div class="acfp-loading">'+ (acfEmojiPicker.strings.loading) +'</div>');
                initField($wrap);
            } else {
                // Si ya está cargado, simplemente hacer toggle
                $grid.toggle();
            }
        });

        $wrap.on('click', '.acfp-emoji-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            // Obtener el emoji del atributo data-emoji (en caso de que Twemoji lo haya convertido a imagen)
            var ch = $btn.attr('data-emoji') || $btn.text();
            var $grid = $btn.closest('.acf-emoji-grid');
            var returnType = $grid.data('return') || 'emoji';
            var $input = $btn.closest('.acf-emoji-picker-wrap').find('.acf-emoji-input');
            if(returnType === 'emoji'){
                $input.val(ch).trigger('change');
            } else if(returnType === 'codepoint'){
                // compute codepoint(s)
                var cps = [];
                for (var i = 0; i < ch.length; i++) {
                    cps.push('U+' + ch.codePointAt(i).toString(16).toUpperCase());
                }
                $input.val(cps.join(' ')).trigger('change');
            } else {
                $input.val(ch).trigger('change');
            }
            // close
            $grid.hide();
        });

        $wrap.on('input', '.acfp-search', function(){
            var q = $(this).val().toLowerCase();
            var $grid = $(this).closest('.acf-emoji-grid').find('.acfp-grid');
            $grid.find('.acfp-emoji-btn').each(function(){
                var $btn = $(this);
                var emoji = $btn.attr('data-emoji') || $btn.text();
                var t = $btn.prop('title').toLowerCase() + ' ' + emoji;
                $btn.toggle(t.indexOf(q) !== -1);
            });
        });
    }

    // init per field
    function initField($el){
        var $wrap = $el;
        var $gridContainer = $wrap.find('.acf-emoji-grid'); // El contenedor completo
        var $gridEl = $wrap.find('.acfp-grid'); // El elemento donde van los botones
        
        if($gridContainer.data('loaded')) return;
        
        fetchEmojiData(acfEmojiPicker.emojiDataUrl).then(function(list){
            // list is array of emoji objects, map to chars and names
            var emojis = [];
            list.forEach(function(it){
                var ch = it.char || it.emoji || it.text || null;
                if(ch){
                    emojis.push({char: ch, name: it.name || it.slug || ''});
                }
            });
            // remove duplicates and limit to common subset if extremely large
            var seen = {};
            var uniques = [];
            emojis.forEach(function(e){
                if(!seen[e.char]){
                    seen[e.char] = true;
                    uniques.push(e);
                }
            });
            // build grid - limpiar primero por si hay mensaje de loading
            $gridEl.empty();
            uniques.forEach(function(e){
                var $btn = $('<button type="button" class="acfp-emoji-btn" title="'+ (e.name || '') +'" data-emoji="'+ e.char +'">'+ e.char +'</button>');
                $gridEl.append($btn);
            });
            // mark loaded en el contenedor
            $gridContainer.data('loaded', true);
            // use twemoji to render images optionally
            if(window.twemoji){
                // twemoji.parse on the grid to turn characters into svg/img for consistent look
                twemoji.parse($gridEl[0], {folder: 'svg', ext: '.svg'});
            }
        }).catch(function(err){
            // fallback small list if fetch fails
            $gridEl.empty();
            var fallback = ['😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊','😋','😎','😍','😘','😗','😙','😚','🙂','🤗','🤔','🤨','😐','😑','😶','🙄','😏','😣'];
            fallback.forEach(function(ch){
                $gridEl.append('<button type="button" class="acfp-emoji-btn" data-emoji="'+ch+'">'+ch+'</button>');
            });
            $gridContainer.data('loaded', true);
            // use twemoji to render images optionally
            if(window.twemoji){
                twemoji.parse($gridEl[0], {folder: 'svg', ext: '.svg'});
            }
        });
    }

    // Función helper para inicializar campos
    function initializeFields($context){
        $context.find('.acf-emoji-picker-wrap').each(function(){
            addEvents($(this));
        });
    }

    $(document).ready(function(){
        // Init existing fields on page load
        initializeFields($(document));

        // Hook principal: cuando ACF configura campos (incluyendo repeaters)
        $(document).on('acf/setup_fields', function(e, postbox){
            initializeFields($(postbox));
        });

        // Hook específico: cuando se añade una nueva fila en un repeater
        if(typeof acf !== 'undefined'){
            acf.addAction('append', function($el){
                initializeFields($el);
            });
        }

        // Hook alternativo para versiones antiguas de ACF
        $(document).on('acf/append_field', function(e, $el){
            initializeFields($el);
        });
    });

})(jQuery);
