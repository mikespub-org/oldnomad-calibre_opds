/**
 * SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Update Calibre2OPDS settings
 */
(function (window, document, $) {
	'use strict';
	
	$(document).ready(function () {
		var libraryRoot = $('#calibre_opds input[name="calibre_opds_library"]');
		var savedMessage = $('#calibre_opds div[name="calibre_opds_saved"]');

		var saved = function () {
			if (savedMessage.is(':visible')) {
				savedMessage.hide();
			}
			savedMessage.fadeIn(function () {
				setTimeout(function () {
					savedMessage.fadeOut();
				}, 5000);
			});
		};

		var submit = function () {
			var libraryRootValue = libraryRoot.val();
			var data = {
				libraryRoot: libraryRootValue
			};
			var url = OC.generateUrl('/apps/calibre_opds/settings');
			$.ajax({
				type: 'PUT',
				contentType: 'application/json; charset=utf-8',
				url: url,
				data: JSON.stringify(data),
				dataType: 'json'
			}).then(function (data) {
				saved();
				libraryRoot.val(libraryRootValue);
			});
		};

		$('#calibre_opds input[type="text"]').blur(submit);
	});
} (window, document, jQuery));
