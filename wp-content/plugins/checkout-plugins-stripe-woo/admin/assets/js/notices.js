( function( $ ) {
	$( document ).ready( function() {
		$( '.cpsw-dismissible-notice .notice-dismiss, .cpsw-notice .cpsw-notice-close-btn, .cpsw-notice-skip-btn' ).on( 'click', function( event ) {
			const $notice = $( this ).closest( '.cpsw-notice' );
			const noticeId = $notice.attr( 'id' );
			const repeatNoticeAfter = $( this ).attr( 'data-repeat-notice-after' ) || '';
			event.preventDefault();

			$.ajax( {
				url: cpsw_notice_ajax_object.ajax_url,
				type: 'POST',
				data: {
					action: 'dismiss_cpsw_notice',
					_security: cpsw_notice_ajax_object.notice_nonce,
					notice_id: noticeId,
					duration: repeatNoticeAfter,
				},
				success() {
					$notice.fadeOut( 'slow', function() {
						$notice.remove();
					} );
				},
				error( jqXHR, textStatus, errorThrown ) {
					// eslint-disable-next-line no-console
					console.error( 'Error dismissing notice:', textStatus, errorThrown );
				},
			} );

			// Redirect if link is available
			const link = $( this ).attr( 'href' ) || '';
			const target = $( this ).attr( 'target' ) || '';
			if ( '' !== link ) {
				window.open( link, target );
			}
		} );
	} );
}( jQuery ) );
