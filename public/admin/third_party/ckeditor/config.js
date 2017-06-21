/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.skin = 'moono';
    config.filebrowserBrowseUrl = 		'/public/admin/third_party/elfinder/elfinder.html';
    config.filebrowserImageBrowseUrl = 	'/public/admin/third_party/elfinder/elfinder.html';
    config.filebrowserFlashBrowseUrl = 	'/public/admin/third_party/elfinder/elfinder.html';
    config.filebrowserWindowWidth = '1000';
    config.filebrowserWindowHeight = '500';
    config.defaultLanguage = 'ru';
    config.embed_provider = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}';
    config.contentsCss = '/public/site/css/editorarea.css';
    config.toolbar = 'WCMS';
    config.toolbar_WCMS =
        [
            ['Maximize','Source'],
            ['Cut','Copy','Paste','PasteText','PasteFromWord','Templates','btgrid'],
            ['Undo','Redo','-','Find','Replace','-','RemoveFormat'],
            ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Link','Unlink','Anchor'],
            ['Image','Flash','PasteCode','Table','SpecialChar'],
            ['Format','Styles']
        ];

    config.width = '100%';
    config.height = '500';
    config.emailProtection = 'encode';
    config.extraPlugins = 'btgrid,pastecode';
    config.startupOutlineBlocks = true;
    config.allowedContent = 'span div p h1 h2 h3 h4 h5 h6 ul ol li strong em blockquote mark del s ins small abbr address footer dl dt code kbd pre var samp i [class,id,title,role]{float}(*); a [href,class,title,name,download,target,id,rel,role]{float}(*); hr table tr th; td [colspan,rowspan,class]{float}(*);img[!src,alt,width,height,class]{float}(*); iframe [*]{*}(*); button [class,type,id,data-toggle]{*}(*);';

    config.basicEntities = false;
    config.entities = false;
    config.entities_greek = false;
    config.entities_latin = false;
    config.htmlEncodeOutput = false;
    config.entities_processNumerical = false;

    config.protectedSource.push(/<i[^>]*><\/i>/g);
    config.protectedSource.push(/<span[^>]*><\/span>/g);

    config.stylesSet = [
        { name: 'Сделать кнопку', element: 'a', attributes: {'class': 'button'} },
        { name: 'Курсив', element: 'em' }
    ];
};
