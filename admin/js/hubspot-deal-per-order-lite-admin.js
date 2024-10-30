(function( $ ) {
	'use strict';

	var ajaxUrl                           = hubwooi18n.ajaxUrl;
	var hubwooWentWrong                   = hubwooi18n.hubwooWentWrong;
	var hubwooDealsPipelineSetupCompleted = hubwooi18n.hubwooDealsPipelineSetupCompleted;
	var hubwooSecurity                    = hubwooi18n.hubwooSecurity;
	var hubwooCreatingPipeline            = hubwooi18n.hubwooCreatingPipeline;
	var hubwooMailFailure                 = hubwooi18n.hubwooMailFailure;

	jQuery( document ).ready(
		function() {

			jQuery( '.hubwoo_deal_lite_tracking' ).on(
				'click',
				function(){
					jQuery( '.hub_deal_lite_pop_up_wrap' ).show();
				}
			);

			jQuery( '.hubwoo_deal_lite_accept' ).on(
				'click',
				function(){

					jQuery( '#hubwoo-deal-lite-loader' ).show();

					jQuery.post(
						ajaxUrl,
						{ 'action' : 'hubwoo_deal_lite_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity },
						function( response ) {

							var oauth_response = jQuery.parseJSON( response );
							var oauth_status   = oauth_response.status;
							var oauthMessage   = oauth_response.message;

							if ( oauth_status ) {

								jQuery.post(
									ajaxUrl,
									{ 'action' : 'hubwoo_deal_lite_accept', 'hubwooSecurity' : hubwooSecurity },
									function( response ) {

										if ( response != null ) {

											var data_status = response;

											if ( ! data_status ) {

												alert( hubwooMailFailure );
											}

											location.reload();
										} else {
											// close the popup and show the error.
											alert( hubwooWentWrong );

											return false;
										}

										jQuery( '#hubwoo-deal-lite-loader' ).hide();
									}
								);
							} else {
								// close the popup and show the error.
								alert( hubwooWentWrong );
								jQuery( '#hubwoo-deal-lite-loader' ).hide();
								return false;
							}
						}
					);
				}
			);

			jQuery( '.hubwoo_deal_lite_later' ).on(
				'click',
				function(){

					jQuery( '#hubwoo-deal-lite-loader' ).show();

					jQuery.post(
						ajaxUrl,
						{'action' : 'hubwoo_deal_lite_later', 'hubwooSecurity' : hubwooSecurity },
						function( response ) {
							jQuery( '#hubwoo-deal-lite-loader' ).hide();
							jQuery( '.hub_deal_lite_pop_up_wrap' ).hide();
						}
					);
				}
			);

			jQuery( '#hubwoo-deal-lite-pipeline-setup' ).on(
				'click',
				function() {

					jQuery( '#hubwoo-deal-lite-loader' ).show();

					jQuery.post(
						ajaxUrl,
						{'action' : 'hubwoo_deal_lite_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity },
						function( response ) {

							var oauth_response = jQuery.parseJSON( response );
							var oauth_status   = oauth_response.status;
							var oauthMessage   = oauth_response.message;

							if ( oauth_status ) {

								jQuery( '#hubwoo-deal-lite-loader' ).hide();
								jQuery( '#hubwoo-deal-pipeline-setup-process' ).show();
								jQuery.post(
									ajaxUrl,
									{'action' : 'hubwoo_deal_lite_get_pipeline', 'hubwooSecurity' : hubwooSecurity },
									function( response ) {

										if ( response != null ) {

											var pipelines       = jQuery.parseJSON( response );
											var pipelines_count = pipelines.length;

											var pipeline_progress = parseFloat( 100 / pipelines_count );

											jQuery.each(
												pipelines,
												function( index, pipeline_details ) {

													var pipelineData = { 'action':'hubwoo_deal_lite_create_pipeline', 'pipelineDetails': pipeline_details, 'hubwooSecurity' : hubwooSecurity };

													jQuery.ajax( { url: ajaxUrl, type: 'POST', data : pipelineData, async: false } ).done(
														function( pipelineResponse ) {

															var response      = jQuery.parseJSON( pipelineResponse );
															var errors        = response.errors;
															var hubwooMessage = "";

															if ( ! errors ) {

																  var responseCode = response.status_code;

																if ( responseCode == 200 ) {

																	hubwooMessage += "<div class='notice updated'><p> " + hubwooCreatingPipeline + "</p></div>";
																	alert( hubwooDealsPipelineSetupCompleted );
																	location.reload();
																} else {

																	//var hubwooResponse = response.response;

																	//if ( hubwooResponse != null && hubwooResponse != "" ) {

																		//hubwooResponse = jQuery.parseJSON( hubwooResponse );
																		hubwooMessage += "<div class='notice error'><p> " + response.response + "</p></div>";
																		alert( hubwooWentWrong );
																		location.reload();
																	//}
																}
															}

															jQuery( '.progress-bar' ).css( 'width',pipeline_progress + '%' );
															jQuery( ".hubwoo-deal-lite-message-area" ).append( hubwooMessage );
														}
													);
												}
											);
										}
									}
								);
							}
						}
					);
				}
			);
		}
	);
})( jQuery );
