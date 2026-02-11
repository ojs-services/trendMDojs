{**
 * plugins/generic/trendMDojs/templates/settingsForm.tpl
 *
 * Copyright (c) 2026 OJS Services
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the TrendMD Widget Plugin.
 *}

<script>
	$(function() {ldelim}
		$('#trendMDOjsSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');

		// Confirm save without identifier
		$('#trendMDOjsSettingsForm').on('submit', function(e) {ldelim}
			var idVal = $.trim($('input[name="trendMDIdentifier"]').val() || $('#trendMDIdentifier').val());
			if (!idVal) {ldelim}
				if (!confirm('{translate key="plugins.generic.trendMDojs.settings.confirmNoIdentifier"}')) {ldelim}
					e.preventDefault();
					e.stopImmediatePropagation();
					return false;
				{rdelim}
			{rdelim}
		{rdelim});

		// Validate identifier
		$('#trendMDOjsValidateBtn').on('click', function(e) {ldelim}
			e.preventDefault();
			var $btn = $(this);
			var $result = $('#trendMDOjsValidateResult');
			var idVal = $.trim($('input[name="trendMDIdentifier"]').val() || $('#trendMDIdentifier').val());

			$btn.prop('disabled', true);
			$result.html('<span style="color:#666;">{translate key="plugins.generic.trendMDojs.validate.checking"}</span>');

			$.ajax({ldelim}
				url: '{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="validateIdentifier" escape=false}',
				type: 'POST',
				data: {ldelim}
					trendMDIdentifier: idVal,
					csrfToken: $('input[name="csrfToken"]').val()
				{rdelim},
				success: function(response) {ldelim}
					try {ldelim}
						var data = typeof response === 'object' ? response : JSON.parse(response);
						var content = typeof data.content === 'string' ? JSON.parse(data.content) : data.content;
						var color = content.valid ? '#e65100' : '#c62828';
						var icon = content.valid ? '&#9432;' : '&#10007;';
						var html = '';
						for (var i = 0; i < content.messages.length; i++) {ldelim}
							html += '<div style="color:' + color + '; padding:2px 0;">' + icon + ' ' + content.messages[i] + '</div>';
						{rdelim}
						$result.html(html);
					{rdelim} catch(err) {ldelim}
						$result.html('<span style="color:#c62828;">&#10007; {translate key="plugins.generic.trendMDojs.validate.error"}</span>');
					{rdelim}
				{rdelim},
				error: function() {ldelim}
					$result.html('<span style="color:#c62828;">&#10007; {translate key="plugins.generic.trendMDojs.validate.error"}</span>');
				{rdelim},
				complete: function() {ldelim}
					$btn.prop('disabled', false);
				{rdelim}
			{rdelim});
		{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="trendMDOjsSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="trendMDOjsNotification"}

	{* Setup Guide *}
	<div style="background:#f0f7ff; border:1px solid #bbdefb; border-radius:4px; padding:14px 18px; margin-bottom:18px;">
		<h4 style="margin:0 0 10px 0; color:#1565c0;">{translate key="plugins.generic.trendMDojs.guide.title"}</h4>
		<p style="margin:0 0 10px 0; line-height:1.6;">{translate key="plugins.generic.trendMDojs.guide.intro"}</p>

		<div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:3px; padding:10px 14px; margin-bottom:10px;">
			<strong style="color:#2e7d32;">&#10003; {translate key="plugins.generic.trendMDojs.guide.autoTitle"}</strong>
			<ul style="margin:6px 0 0 0; padding-left:18px; line-height:1.6;">
				<li>{translate key="plugins.generic.trendMDojs.guide.autoScript"}</li>
				<li>{translate key="plugins.generic.trendMDojs.guide.autoDiv"}</li>
			</ul>
			<div style="background:#fff; border:1px solid #c8e6c9; border-radius:3px; padding:8px 12px; margin-top:10px;">
				<strong style="font-size:12px; color:#2e7d32;">{translate key="plugins.generic.trendMDojs.guide.generatedCode"}</strong>
				<pre style="margin:4px 0 0 0; font-size:11px; line-height:1.5; white-space:pre-wrap; color:#333;">&lt;script defer src="{$trendMDJsUrl}" data-trendmdconfig='{ldelim}"website_id":"your-uuid","element":"#trendmd-suggestions"{rdelim}'&gt;&lt;/script&gt;
&lt;div id="trendmd-suggestions"&gt;&lt;/div&gt;</pre>
			</div>
		</div>

		<div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:3px; padding:10px 14px;">
			<strong style="color:#e65100;">&#9998; {translate key="plugins.generic.trendMDojs.guide.youDoTitle"}</strong>
			<p style="margin:6px 0 0 0; line-height:1.6;">{translate key="plugins.generic.trendMDojs.guide.youDoText"}</p>
			<div style="background:#fff; border:1px solid #ddd; border-radius:3px; padding:8px 12px; margin-top:8px; font-family:monospace; font-size:13px; color:#555;">
				{translate key="plugins.generic.trendMDojs.guide.exampleFormat"}
			</div>
		</div>
	</div>

	{* Identifier *}
	{fbvFormArea id="trendMDOjsIdentifierArea" title="plugins.generic.trendMDojs.settings.identifierSection"}

		{fbvFormSection title="plugins.generic.trendMDojs.settings.identifierLabel" for="trendMDIdentifier"}
			{fbvElement type="text" id="trendMDIdentifier" value=$trendMDIdentifier size=$fbvStyles.size.LARGE}
			<p><span class="instruct">{translate key="plugins.generic.trendMDojs.settings.identifierHelp"}</span></p>
		{/fbvFormSection}

		<div style="margin:-10px 0 14px 0;">
			<button type="button" id="trendMDOjsValidateBtn" class="pkp_button" style="margin-right:10px;">
				{translate key="plugins.generic.trendMDojs.validate.btnLabel"}
			</button>
			<div id="trendMDOjsValidateResult" style="display:inline-block; vertical-align:middle;"></div>
		</div>

	{/fbvFormArea}

	{* Display Options *}
	{fbvFormArea id="trendMDOjsDisplayArea" title="plugins.generic.trendMDojs.settings.displaySection"}

		{fbvFormSection title="plugins.generic.trendMDojs.settings.positionLabel"}
			<ul style="list-style:none; padding:0; margin:0.5em 0;">
				<li style="margin-bottom:0.75em;">
					<label style="cursor:pointer;">
						<input type="radio" name="widgetPosition" value="article_main"
							{if $widgetPosition === 'article_main' || !$widgetPosition}checked="checked"{/if} />
						<strong>{translate key="plugins.generic.trendMDojs.position.articleMain"}</strong>
						&mdash; {translate key="plugins.generic.trendMDojs.position.articleMainHint"}
					</label>
				</li>
				<li style="margin-bottom:0.75em;">
					<label style="cursor:pointer;">
						<input type="radio" name="widgetPosition" value="article_detail"
							{if $widgetPosition === 'article_detail'}checked="checked"{/if} />
						<strong>{translate key="plugins.generic.trendMDojs.position.articleDetail"}</strong>
						&mdash; {translate key="plugins.generic.trendMDojs.position.articleDetailHint"}
					</label>
				</li>
				<li>
					<label style="cursor:pointer;">
						<input type="radio" name="widgetPosition" value="article_footer"
							{if $widgetPosition === 'article_footer'}checked="checked"{/if} />
						<strong>{translate key="plugins.generic.trendMDojs.position.articleFooter"}</strong>
						&mdash; {translate key="plugins.generic.trendMDojs.position.articleFooterHint"}
					</label>
				</li>
			</ul>
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.trendMDojs.settings.cssClassLabel" for="customCssClass"}
			{fbvElement type="text" id="customCssClass" value=$customCssClass size=$fbvStyles.size.MEDIUM}
			<p><span class="instruct">{translate key="plugins.generic.trendMDojs.settings.cssClassHelp"}</span></p>
		{/fbvFormSection}

	{/fbvFormArea}

	{fbvFormButtons}
</form>
