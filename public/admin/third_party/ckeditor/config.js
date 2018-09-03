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
            ['Image','Flash','Html5audio','PasteCode','Table','SpecialChar'],
            ['Format','Styles']
        ];

    config.width = '100%';
    config.height = '500';
    config.emailProtection = 'encode';
    config.extraPlugins = 'btgrid,pastecode,html5audio';
    config.startupOutlineBlocks = true;
    config.allowedContent = {
        $1: {
            // Use the ability to specify elements as an object.
            elements: CKEDITOR.dtd,
            attributes: true,
            styles: false,
            classes: true
        }
    };
    config.disallowedContent = 'img{width,height}; font';

    config.basicEntities = false;
    config.entities = false;
    config.entities_greek = false;
    config.entities_latin = false;
    config.htmlEncodeOutput = false;
    config.entities_processNumerical = false;

    config.protectedSource.push(/<i[^>]*><\/i>/g);
    config.protectedSource.push(/<span[^>]*><\/span>/g);

    config.stylesSet = [
        { name: 'Сделать кнопку', element: 'a', attributes: {'class': 'btn'} },
        { name: 'Масштабируемое', element: 'img', attributes: { 'class': 'img-fluid' } },
        { name: 'Слева', element: 'img', attributes: { 'class': 'float-left mb10 mt10 mr20' } },
        { name: 'Справа', element: 'img', attributes: { 'class': 'float-right mb10 mt10 ml20' } },
        { name: 'Курсив', element: 'em' }
    ];
};
