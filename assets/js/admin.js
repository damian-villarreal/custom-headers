(function ($) {
	'use strict';

	var strings = window.customHeaders && window.customHeaders.strings ? window.customHeaders.strings : {};
	var ruleIndex = 0;
	var headerIndex = 0;

	function refreshRules() {
		var $rules = $('#ch-rules .ch-rule');
		$('.ch-rules-empty').prop('hidden', $rules.length > 0);
		$rules.each(function (i) {
			$(this).find('.ch-row-number').text(i + 1);
		});
	}

	function replaceTokens(html, ruleToken, headerToken) {
		return html
			.replace(/__RULE_INDEX__/g, ruleToken)
			.replace(/__HEADER_INDEX__/g, headerToken);
	}

	function addRule() {
		var ruleToken = 'r' + (ruleIndex++);
		var html = replaceTokens($('#tmpl-ch-rule').html(), ruleToken, 'h0');
		var $rule = $($.parseHTML($.trim(html)));
		$('#ch-rules').append($rule);
		refreshRules();
		$rule.find('.ch-context').trigger('focus');
	}

	function addHeaderRow($rule) {
		var ruleToken = $rule.data('rule-index');
		var headerToken = 'h' + (headerIndex++);
		var html = replaceTokens($('#tmpl-ch-header-row').html(), ruleToken, headerToken);
		$rule.find('.ch-header-rows').append($($.parseHTML($.trim(html))));
		$rule.find('.ch-header-rows .ch-header-name').last().trigger('focus');
	}

	function validateHeaderRow($row) {
		var name = $.trim($row.find('.ch-header-name').val() || '');
		var value = $.trim($row.find('.ch-header-value').val() || '');
		var invalid = (name && !value) || (!name && value);
		$row.toggleClass('ch-row-invalid', invalid);
		return !invalid;
	}

	function validateRule($rule) {
		var hasCompleteHeader = false;
		var headerInvalid = false;

		$rule.find('.ch-header-row').each(function () {
			var $row = $(this);
			var name = $.trim($row.find('.ch-header-name').val() || '');
			var value = $.trim($row.find('.ch-header-value').val() || '');
			if (name && value) {
				hasCompleteHeader = true;
			}
			if (!validateHeaderRow($row)) {
				headerInvalid = true;
			}
		});

		var uri = $.trim($rule.find('.ch-uri-pattern').val() || '');
		var invalid = /\s/.test(uri) || !hasCompleteHeader || headerInvalid;
		$rule.toggleClass('ch-rule-invalid', invalid);
		return !invalid;
	}

	function validateForm() {
		var valid = true;
		var $firstInvalid = null;

		$('#ch-rules .ch-rule').each(function () {
			if (!validateRule($(this))) {
				valid = false;
				if (!$firstInvalid) {
					$firstInvalid = $(this).find('.ch-header-name, .ch-uri-pattern').filter(function () {
						return $.trim($(this).val()) === '' || $(this).hasClass('ch-row-invalid');
					}).first();
				}
			}
		});

		if (!valid) {
			if ($firstInvalid && $firstInvalid.length) {
				$firstInvalid.trigger('focus');
			}
			window.alert(strings.validationRules || 'Each rule must have at least one header with both a name and a value.');
			return false;
		}

		return true;
	}

	function initRules() {
		$('#ch-rules').sortable({
			handle: '.ch-drag-handle',
			axis: 'y',
			placeholder: 'ch-rule-placeholder',
			update: refreshRules
		});

		$('#ch-add-rule').on('click', addRule);

		$(document).on('click', '.ch-remove-rule', function () {
			$(this).closest('.ch-rule').remove();
			refreshRules();
		});

		$(document).on('click', '.ch-add-header-row', function () {
			addHeaderRow($(this).closest('.ch-rule'));
		});

		$(document).on('click', '.ch-remove-header-row', function () {
			var $rule = $(this).closest('.ch-rule');
			$(this).closest('.ch-header-row').remove();
			validateRule($rule);
		});

		$(document).on('input change', '.ch-header-row input', function () {
			var $row = $(this).closest('.ch-header-row');
			if ($row.hasClass('ch-row-invalid')) {
				validateHeaderRow($row);
			}
			validateRule($(this).closest('.ch-rule'));
		});

		$(document).on('input change', '.ch-uri-pattern', function () {
			var $rule = $(this).closest('.ch-rule');
			if (/\s/.test($(this).val() || '')) {
				$rule.addClass('ch-rule-invalid');
			} else {
				validateRule($rule);
			}
		});

		refreshRules();
	}

	$(function () {
		initRules();
		$('form').on('submit', function () {
			return validateForm();
		});
	});
})(jQuery);
