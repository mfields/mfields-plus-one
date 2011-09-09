jQuery( document ).ready( function( $ ) {
	var preview = $( '#mfields_plus_one_preview div' );
	var start_count = $( '#mfields_plus_one_count_true' ).attr( 'checked' );
	
	$( '.mfields_plus_one_size' ).click( function() {
		var val = $( this ).val();
		preview.removeClass( 'small medium standard tall' );
		preview.addClass( val );
	} );
	$( '.mfields_plus_one_count' ).click( function() {
		preview.removeClass( 'count' );
		if ( 'true' == $( this ).val() ) {
			preview.addClass( 'count' );
		}
		console.log( 'taco: ' + $( this ).val() );
	} );
} );