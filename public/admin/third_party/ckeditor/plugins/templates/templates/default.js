/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

// Register a templates definition set named "default".
CKEDITOR.addTemplates( 'default', {
	// The name of sub folder which hold the shortcut preview images of the
	// templates.
	imagesPath: CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + 'templates/images/' ),

	// The templates definitions.
	templates: [ {
		title: 'Две кнопки рядом',
		image: 'template1.gif',
		description: 'Две кнопки рядом для галереи на главной странице',
		html: '<a class="shortcode_button btn_large btn_type1" href="javascript:void(0);">Узнать больше</a> <a class="shortcode_button btn_large btn_type2" href="javascript:void(0);">Связаться со мной!</a>'
	} ]
} );
